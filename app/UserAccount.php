<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property $status
 * @property $integration_completed_on
 * @property mixed properties_info
 * @property mixed activeProperties
 * @property mixed bookings_info
 * @property mixed properties_info_unavailable
 * @property $id
 * @property UserPms pms
 * @property $name
 * @property mixed bookings_info
 * @property mixed all_properties
 */

/**
 * Class UserAccount
 * @package App
 */

class UserAccount extends Model implements Auditable
{
    use AuditableTrait;
    use LogsActivity;

    const TOTAL_BILLING_REMIND_EMAIL_ATTEMPTS = 4;
    const NEXT_REMINDER_EMAIL_DAYS = 4;

    /**
     * @property UserPms|null pms
     * @property UserNotificationSetting collection|null user_notification_settings
     * @property PropertyInfo collection|null properties_info
     * @property TransactionDetail collection|null transactions_detail
     * @property TransactionInit collection|null transactions_init
     * @property UserBookingSource collection|null user_bookings_source
     * @property BookingInfo collection|null bookings_info
     * @property ErrorLog collection|null error_logs
     * @property BookingSourceForm collection|null booking_source_form
     * @property GuestCommunication collection|null messages
     * @property UserPaymentGateway collection|null user_payment_gateways
    */


    /**
     * @var array
     */

    protected $fillable = ['name', 'company_logo', 'status', 'integration_completed_on', 'last_booking_sync',
        'time_zone', 'current_pms', 'stripe_customer_id', 'billing_card_required_reminder_due_date', 'billing_reminder_attempts',
        'suspend_account_on', 'sd_activated_on', 'plan_attached_status', 'plan_attached_status_last_sync', 'user_account_id_at_pms', 'sync_booking_from',
        'last_properties_synced', 'count_unauthorized_property_sync', 'last_properties_sync_exception'
    ];

    public function users()
    {
        return $this->hasMany('App\User');
    }

    public function user()
    {
        return $this->hasOne('App\User')->where('parent_user_id', '=', 0);
    }


    public function team_members()
    {
        return $this->hasMany('App\User')->where('parent_user_id', '!=', 0);
    }

    /**
     * This Function is not useAle now this table is replaced with user_preferences_notification_settings()
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function user_notification_settings()
    {
        return $this->hasMany('App\UserNotificationSetting');
    }

    public function user_preferences_notification_settings()
    {
        return $this->hasMany('App\UserPreferencesNotificationSettings');
    }

    public function properties_info()
    {
        return $this->hasMany('App\PropertyInfo')->where('available_on_pms', 1);
    }

    public function activeProperties()
    {
        return $this->hasMany('App\PropertyInfo')->where('available_on_pms', 1)
            ->where('status', 1);
    }

    public function all_properties()
    {
        return $this->hasMany('App\PropertyInfo');
    }

    public function properties_info_unavailable()
    {
        return $this->hasMany('App\PropertyInfo')->where('available_on_pms', 0);
    }

    public function transactions_detail()
    {
        return $this->hasMany('App\TransactionDetail');
    }
    public function transactions_init()
    {
    
        return $this->hasMany('App\TransactionInit');
    }
    public function transactions_init_success()
    {

        return $this->hasMany('App\TransactionInit')->whereIn('type', ['C', 'M', 'CS'])->where('payment_status', '1');
    }

    public function user_bookings_source()
    {
    
        return $this->hasMany('App\UserBookingSource');
    }

    public function bookings_info()
    {
        return $this->hasMany('App\BookingInfo');
    }

    public function error_logs()
    {

        return $this->hasMany('App\ErrorLog');

    }


    public function booking_source_form()
    {
        return $this->hasManyThrough('App\BookingSourceForm', 'App\UserBookingSource');

    }

    public function booking_source_forms() {

        return $this->belongsToMany('App\BookingSourceForm', 'user_booking_sources');
    }

    public function messages()
    {
        return $this->hasMany('App\GuestCommunication');

    }

    public function user_payment_gateways()
    {
        return $this->hasMany('App\UserPaymentGateway');

    }

    public function pms() {
        return $this->hasOne('App\UserPms');
    }
    public function ccauth()
    {
        return $this->hasMany(CreditCardAuthorization::class);
    }

    public function userAccountPMSVerified() {
        return $this->hasMany(UserPms::class, 'user_account_id', 'id')
            ->where('is_verified', 1);
    }
    public function companyAdmin(){
        return $this->users[0];
    }

    //to calculate the successfully charged amount for all the booking of particular user
    public function successful_transactions()
    {
    
        return $this->transactions_init()->whereIn('type', ['C', 'M', 'CS'])->where('payment_status', '1');
    }

    //to calculate the failed charged amount for all the booking of particular user
    public function failed_transactions()
    {
    
        return $this->transactions_init()->whereIn('type', ['C', 'M', 'CS'])->where('payment_status', '0');
    }

    //to calculate the scheduled amount for all the booking of particular user
    public function scheduled_transactions()
    {
    
        return $this->transactions_init()->whereIn('type', ['C', 'M', 'CS'])->where('payment_status', '2');
    }

    public function system_jobs()
    {
        return $this->hasMany('App\SystemJob');
    }

    /**
     * @return bool
     */
    public function isActive() {
        return $this->status == 1;
    }

    public function isIntegrationCompleted() {
        return $this->integration_completed_on != null;
    }

    public function creditCardInfos() {
        return $this->hasMany('App\CreditCardInfo');
    }


    public function getNameAttribute($value) {
        return strip_tags(handleSpecialCharacters($value));
    }

    public function setNameAttribute($value) {
        $this->attributes['name'] = strip_tags(handleSpecialCharacters($value));
    }

    public function fetchBookingSettings()
    {
        return $this->hasMany('App\FetchBookingSetting');
    }

    public function userGeneralPreferences()
    {
        return $this->hasMany('App\UserGeneralPreference');
    }

    public function termsAndConditions(){
        return $this->hasMany(TermsAndCondition::class);
    }

    public function upsells(){
        return $this->hasMany(Upsell::class);
    }

    public function upsell_types(){
        return $this->hasMany(UpsellType::class);
    }


    public function upsellOrders(){
        return $this->hasMany(UpsellOrder::class);
    }


    public function guideBooks(){
        return $this->hasMany(GuideBook::class);
    }

    public function guideBook_Types(){
        return $this->hasMany(GuideBookType::class);
    }

    /**
     * @param $value
     * @return string|string[]|null
     */
    public function getAddressAttribute($value)
    {
        return preg_replace('~[\r\n]+~', '', $value);
    }
}
