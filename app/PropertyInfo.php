<?php

namespace App;

use App\BookingInfo;
use App\Repositories\Bookings\Bookings;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @property CreditCardAuthorization|null paused_authorizations
 * @property TransactionInit|null paused_transaction_inits
 * @property mixed pms_property_id
 * @property mixed property_key
 * @property mixed currency_code
 * @property mixed user_account_id
 * @property mixed id
 * @property mixed user_id
 * @property $name
 * @property $logo
 * @property $use_bs_settings
 * @property $use_pg_settings
 * @property $pg_setting_property_id
 * @property $bs_setting_property_id
 */
class PropertyInfo extends Model implements Auditable
{
    use AuditableTrait;

    protected $fillable = ['name', 'logo', 'user_id', 'pms_id', 'user_account_id', 'pms_property_id', 'property_key',
        'currency_code', 'time_zone', 'longitude', 'latitude', 'address', 'city', 'country','property_email', 'user_payment_gateway_id', 'use_bs_settings',
        'use_pg_settings', 'status', 'notify_url', 'last_sync', 'available_on_pms', 'sync_booking_from'];

    /* MODEL KEYS */
    const UP_SELLS="up-sell";
    const GUIDE_BOOK="guide-book";
    const TAC="terms-and-conditions";
    /* MODELS HAVING RELATION WITH PROPERTY_INFO */
    const BRIDGED_MAIN_MODELS  = [
        self::UP_SELLS=>Upsell::class,
        self::GUIDE_BOOK=>GuideBook::class,
        self::TAC=>TermsAndCondition::class
    ];
    /* MODELS BRIDGE MODELS */
    const BRIDGED_MODELS  = [
        self::UP_SELLS=>UpsellPropertiesBridge::class,
        self::GUIDE_BOOK=>GuideBookPropertiesBridge::class,
        self::TAC=>TermsAndConditionRentalBridge::class
    ];
    /* MODELS BRIDGE MODELS META */
    const BRIDGED_MODEL_META  = [
        self::UP_SELLS =>
            ['column' => 'upsell_id', 'relation' =>'upsellPropertiesBridge'],
        self::GUIDE_BOOK =>
            ['column' =>  'guide_book_id', 'relation' =>'guideBookPropertiesBridge'],
        self::TAC =>
            ['column' =>  'terms_and_condition_id', 'relation' =>'termsAndConditionPropertiesBridge'],
    ];

    public function user() {
       return $this->belongsTo('App\User');
    }

    public function room_info() {
        return $this->hasMany('App\RoomInfo');
    }


    public function user_payment_gateway()
    {
        return $this->hasOne('App\UserPaymentGateway');
    }

    /**
     * This Relation is not available for eager Loading Relations ie => with('property_info') only use this relation on lazy loading
     * Otherwise it will return null or unexpected results
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function booking_info(){
        return $this->hasMany(BookingInfo::class, 'property_id', 'pms_property_id')
                ->where('user_account_id', $this->getAttribute('user_account_id'));
    }

    public function user_account() {
       return $this->belongsTo('App\UserAccount');
    }

    public function user_pms() {
        return $this->belongsTo('App\UserPms');
    }

    public function payment_gateway() {
        return $this->hasOne(UserPaymentGateway::class);
    }

    public function paused_authorizations() {
        return $this->hasManyThrough(
            CreditCardAuthorization::class,
            BookingInfo::class,
            'property_id',
            'booking_info_id',
            'pms_property_id',
            'id')
            ->with('booking_info')
            ->where('status', CreditCardAuthorization::STATUS_PAUSED);
    }

    public function pending_and_reattempt_authorizations() {
        return $this->hasManyThrough(
            CreditCardAuthorization::class,
            BookingInfo::class,
            'property_id',
            'booking_info_id',
            'pms_property_id',
            'id')
            ->with('booking_info')
            ->whereIn('status', [CreditCardAuthorization::STATUS_PENDING, CreditCardAuthorization::STATUS_REATTEMPT]);
    }

    public function paused_transaction_inits() {
        return $this->hasManyThrough(
            TransactionInit::class,
            BookingInfo::class,
            'property_id',
            'booking_info_id',
            'pms_property_id',
            'id')
            ->with('booking_info')
            ->where('payment_status', TransactionInit::PAYMENT_STATUS_PAUSED);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function pending_and_reattempt_transaction_inits() {
        return $this->hasManyThrough(
            TransactionInit::class,
            BookingInfo::class,
            'property_id',
            'booking_info_id',
            'pms_property_id',
            'id')
            ->with('booking_info')
            ->whereIn('payment_status', [TransactionInit::PAYMENT_STATUS_PENDING, TransactionInit::PAYMENT_STATUS_REATTEMPT]);
    }

    public function isActive() {
        return $this->status == 1 && $this->available_on_pms == 1;
    }
    public function getNameAttribute($value) {
        return handleSpecialCharacters($value);
    }

    public function setGuestLastNameAttribute($value) {
        $this->attributes['name'] = handleSpecialCharacters($value);
    }


    public function propertyInfoAudits()
    {
        return $this->hasMany(Audit::class, 'auditable_id', 'id')->where('auditable_type', 'App\PropertyInfo');
    }

    public function upsellPropertiesBridge(){
        return $this->hasMany(UpsellPropertiesBridge::class);
    }

    /** RELATIONS WITH GUIDE BOOKS */

    /** Relation With Bridge Table
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function guideBookPropertiesBridge(){
        return $this->hasMany(GuideBookPropertiesBridge::class);
    }

    /** Relation With Guide Books Main Table Through Pivot Table
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function guideBooks(){
        return $this->hasManyThrough(
            GuideBook::class,
            GuideBookPropertiesBridge::class,
            'property_info_id',
            'id',
            'id',
            'guide_book_id'
        );
    }

    /** RELATIONS WITH TERMS&CONDITIONS */

    /** Relation With Bridge Table
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function termsAndConditionPropertiesBridge()
    {
        return $this->hasMany(TermsAndConditionRentalBridge::class);
    }

    /** Relation With Terms And Conditions Main Table Through Pivot Table
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function termsAndConditions(){
        return $this->hasManyThrough(
            TermsAndCondition::class,
            TermsAndConditionRentalBridge::class,
            'property_info_id',
            'id',
            'id',
            'terms_and_condition_id'
        );
    }
    public function pmsForm() {
        return $this->belongsTo(PmsForm::class, 'pms_id', 'id');
    }

    /**
     * @return int|mixed
     */
    public function getBsSettingPropertyIdAttribute() {
        return $this->use_bs_settings == 1 ? $this->id : 0;
    }

    /**
     * @return int|mixed
     */
    public function getPgSettingPropertyIdAttribute() {
        return $this->use_pg_settings == 1 ? $this->id : 0;
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

