<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use OwenIt\Auditing\Contracts\Auditable;
use App\Repositories\Settings\PaymentTypeMeta;
use OwenIt\Auditing\Auditable as AuditableTrait;


/**
 * @method static create(array $array)
 * @property mixed bookingSourceForm
 * @property $pms_booking_id
 * @property $user_account_id
 * @property $guest_name
 * @property $guest_last_name
 * @property $id
 * @property $check_in_date
 * @property $check_out_date
 * @property $property_id
 * @property $guest_address
 * @property $guest_country
 * @property $guest_post_code
 * @property $guest_phone
 * @property $guestMobile
 * @property mixed user_account
 * @property PropertyInfo property_info
 * @property string|null is_vc
 * @property mixed transaction_init
 * @property int pms_id
 * @property int master_id
 * @property int user_id
 * @property string guestCity
 * @property string full_response
 * @property int is_process_able
 * @property mixed credit_card_authorization
 * @property mixed guest_deleted_images
 */
class BookingInfo extends Model implements Auditable
{
    use AuditableTrait;
    const CREDIT_CARD_NOT_VALID_MESSAGE = 'Payment method not found. Please attach a Credit Card.';

    public $maintenance = false;

    const PAYMENT_GATEWAY_INACTIVE  = 2; // is_processable => Payment Gateway not Active',
    const PROCESSABLE  = 1; // is_processable => true',


    protected $fillable = ['pms_booking_id', 'bs_booking_id', 'user_id', 'user_account_id','property_id', 'pms_id', 'channel_code',
        'room_id', 'guest_email', 'guest_title', 'guest_phone', 'guest_name', 'guest_last_name', 'guest_zip_code', 'guest_post_code', 'guest_country',
        'guest_address', 'guest_currency_code', 'booking_time', 'pms_booking_modified_time', 'check_in_date', 'check_out_date',
        'pms_booking_status', 'total_amount', 'booking_older_than_24_hours', 'record_source', 'is_vc','is_manual', 'full_response',
        'is_process_able', 'cancellation_settings', 'cancellationTime', 'property_time_zone', 'manual_canceled', 'cancel_email_sent',
        'card_invalid_report_time', 'guestMobile', 'guestFax', 'guestCity', 'notes', 'flagColor', 'flagText',
        'bookingStatusCode', 'price', 'bookingReferer', 'guestComments', 'guestArrivalTime', 'invoiceNumber',
        'invoiceDate', 'apiMessage', 'message', 'bookingIp', 'host_comments', 'unit_id', 'master_id', 'property_info_id', 'num_adults', 'channel_reference'
        ];

    protected $appends = ['full_name'];

    public function user() {
        return $this->belongsTo('App\User');
    }

    public function user_account(){
        return $this->belongsTo('App\UserAccount');
    }

    public function booking_cancellations_info() {
        return $this->hasMany('App\Booking_cancellations_info');
    }

    public function Pms_booking_status_head() {
        return $this->belongsTo('App\PmsBookingStatusHead','code','pms_booking_status');
    }

    public function transaction_init() {
        return $this->hasMany(TransactionInit::class);
    }

    public function transaction_init_charged() {
        return $this->hasMany(TransactionInit::class)->where('type', 'c');
    }

    public function credit_card_authorization() {
        return $this->hasMany(CreditCardAuthorization::class);
    }

    public function credit_card_authorization_sd_cc() {
        return $this->hasMany(CreditCardAuthorization::class)->whereIn('type', [19, 20, 21, 22]);
    }

    public function ccAuthorizationDetails(){
        return $this->hasManyThrough(AuthorizationDetails::class,CreditCardAuthorization::class,'booking_info_id','cc_auth_id','id','id');
//            ->whereRaw('credit_card_authorizations.id = '.$this->credit_card_authorization()->latest()->first()->id);
    }

    /**
     * This Relation is not available for eager Loading Relations ie => with('property_info') only use this relation on lazy loading
     * otherwise it will return null or unexpected results
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function property_info() {
        return $this->belongsTo('App\PropertyInfo', 'property_id', 'pms_property_id')
            ->where('user_account_id','=', $this->getAttribute('user_account_id'));
    }

    public function property_infos(){
        return $this->belongsTo(PropertyInfo::class, 'property_info_id', 'id');
    }

    public function room_info() {
        return $this->belongsTo(RoomInfo::class, 'room_id', 'pms_room_id')
            ->where('property_info_id', $this->property_info->id);
    }

    public function cc_Infos() {
        return $this->hasMany('App\CreditCardInfo');
    }

    public function messages() {
        return $this->hasMany('App\GuestCommunication','booking_info_id');
    }

    public function booking_auth_security_damage() {

        $paymentType = new PaymentTypeMeta();
        $cc_infos = $this->cc_Infos()->where('is_vc', 0)->with('ccauth')->get();
        $cc_auths = collect();
        foreach($cc_infos as $ccinfo){
            if(!$ccinfo->ccauth->isEmpty()){

                $auths = $ccinfo->ccauth()->whereIn('type', [
                $paymentType->getSecurityDepositAutoAuthorize(),
                $paymentType->getSecurityDepositManualAuthorize()])->get();
                foreach($auths as $auth){
                    $cc_auths->push($auth);
                }
            }
        }
        return $cc_auths;
    }
    public function booking_auths() {

        $paymentType = new PaymentTypeMeta();
        $cc_infos = $this->cc_Infos()->where('is_vc', 0)->with('ccauth')->get();
        $cc_auths = collect();
        foreach($cc_infos as $ccinfo){
            if(!$ccinfo->ccauth->isEmpty()){

                $auths = $ccinfo->ccauth()->get();
                foreach($auths as $auth){
                    $cc_auths->push($auth);
                }
            }
        }
        return $cc_auths;
    }

    public function guest_images() {
        return $this->hasMany(GuestImage::class, 'booking_id')->orderBy('id','desc');
    }

    /**
     * Won't Work With Eager Loading
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function bookingSourceForm() {
        return $this->belongsTo(BookingSourceForm::class, 'channel_code', 'channel_code')
            ->where('channel_code', $this->getAttribute('channel_code'))
            ->where('pms_form_id', $this->getAttribute('pms_id'));
    }

    /**
     * Won't Work With Eager Loading Same as Defined above,
     * just overcome compatibility issues (pre used function name)
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function booking_source() {
        return $this->belongsTo(BookingSourceForm::class, 'channel_code', 'channel_code')
            ->where('channel_code', $this->getAttribute('channel_code'))
            ->where('pms_form_id', $this->getAttribute('pms_id'));
    }

    // public function getCheckInDateAttribute($check_in_date){

    //     // return Carbon::parse($check_in_date)->format('d M Y h:i a');
    //     return Carbon::parse($check_in_date)->format('d M Y');
    // }

    // public function getCheckOutDateAttribute($check_out_date){

    //     // return Carbon::parse($check_out_date)->format('d M Y h:i a');
    //     return Carbon::parse($check_out_date)->format('d M Y');
    // }

    // public function getBookingTimeAttribute($booking_time){
    //     $date_value = Carbon::parse($booking_time);
    //     $time_zone_value = $this->getAttribute('property_time_zone');
    //     $date_value->setTimezone($time_zone_value);
    //     return $date_value->format('d M Y h:i a');
    // }


    public function pms_form() {
        return $this->belongsTo(PmsForm::class, 'pms_id', 'id');
    }

    /**
     * BookingInfo's Security Auths
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function credit_card_authorizations() {
        $paymentType = new PaymentTypeMeta();
        $securityAuthTypes = [$paymentType->getSecurityDepositAutoAuthorize(), $paymentType->getSecurityDepositManualAuthorize()];
        return  $this->hasMany(CreditCardAuthorization::class, 'booking_info_id', 'id')->whereIn('type', $securityAuthTypes);
    }

    public function success_charge_transaction_init() {
        return $this->transaction_init()->where('type' , 'C')->where('payment_status', 1);
    }

    public function schedule_charge_transaction_init() {
        return $this->transaction_init()->where('type' , 'C')->where('payment_status', 2);
    }

    public function failed_charge_transaction_init() {
        return $this->transaction_init()->where('type' , 'C')->whereIn('payment_status', [0, 4]);
    }

    public function guest_data(){
        return $this->belongsTo(GuestData::class, 'id', 'booking_id');
    }

    public function scopeNameFilter($query, $first_name, $last_name){
        return $query->where('guest_name', $first_name)->orWhere('guest_last_name', $last_name);
    }

    public function sent_emails(){
        return $this->hasMany('App\SentEmail');
    }

    public function getGuestNameAttribute($value) {
        return handleSpecialCharacters($value);
    }

    public function setGuestNameAttribute($value) {
        $this->attributes['guest_name'] = handleSpecialCharacters($value);
    }

    public function getGuestLastNameAttribute($value) {
        return handleSpecialCharacters($value);
    }

    public function setGuestLastNameAttribute($value) {
        $this->attributes['guest_last_name'] = handleSpecialCharacters($value);
    }

    public function setChannelCodeAttribute($value) {
        $this->attributes['channel_code'] =
            empty(BookingSourceForm::where([['channel_code', $value], ['pms_form_id', $this->attributes['pms_id']]])->count())
                ? config('db_const.booking_source_forms.channelCode.others')
                : $value;
    }

    public function isCanceledInDB() {
        return $this->attributes['cancellationTime'] != null || $this->attributes['manual_canceled'] == 1 || $this->attributes['pms_booking_status'] == 0;
    }

    public function upsellOrders(){
        return $this->hasMany(UpsellOrder::class);
    }

    /**
     * Zero Value indicated that its Master Booking
     * @return bool
     */
    public function isMaster() {
        return $this->attributes['master_id'] == 0;
    }

    /**
     * Negative one indicates that its Normal Booking
     * @return bool
     */
    public function isNormal() {
        return $this->attributes['master_id'] == -1;
    }

    /**
     * Value Greater than Zero indicates that it's GroupBooking
     * @return bool
     */
    public function isGroupBooking() {
        return $this->attributes['master_id'] > 0;
    }

    public function room_upsells() {
        return $this->room_info->upsells();

    }

    public function upsellCarts(){
        return $this->hasMany(UpsellCart::class);
    }

    public function getFullNameAttribute() {
        return ucfirst(handleSpecialCharacters($this->guest_name)) . ' ' .
            ucfirst(handleSpecialCharacters($this->guest_last_name));
    }

    public function bookingDetail() {
        return $this->hasOne(BookingInfoDetail::class);
    }

    public function guest_deleted_images() {
        return $this->hasMany(GuestImageDetail::class)->orderBy('id','desc');
    }
}