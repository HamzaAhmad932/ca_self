<?php

namespace App;


use App\Repositories\Settings\PaymentSettings;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class UserBookingSource extends Model implements Auditable
{
    use AuditableTrait;

    /**
     * @property BookingSourceForm|null booking_source_form
     * @property PropertyInfo|null property_info
     * @property User|null User
     * @property UserAccount|null User_account
     * @property Mixed|null payment_schedule_setting
     * @property Mixed|null cancellation_setting
     * @property Mixed|null security_damage_deposit_setting
     * @property Mixed|null credit_card_validation_setting
     * @property ErrorLog|null error_logs
    */

    const  DEFAULT_PAYMENT_SETTING = '{"afterBookingDays":0,"amountType":2,"amountTypeValue":1,"beforeCheckInDays":0,
    "dayType":1,"remainingBeforeCheckInDays":86400,"status":false, "onlyVC":false}'; //only_vc for Vc Booking Process (On / OFF)
    const DEFAULT_CREDIT_CARD_SETTING = '{"amountType":1,"amountTypeValue":"","authorizeAfterDays":0,
    "autoReauthorize":false,"status":false}';
    const DEFAULT_SECURITY_DEPOSIT_SETTING = '{"amountType":1,"amountTypeValue":"","authorizeAfterDays":0,
    "autoReauthorize":false,"status":false}';
    const  DEFAULT_CANCELLATION_SETTING = '{"afterBooking":0,"afterBookingStatus":false,"beforeCheckIn":0,
    "beforeCheckInStatus":false,"rules":[{"canFee":"","is_cancelled":"","is_cancelled_value":""}],"status":false}';

    protected $fillable = [
        'user_id', 'user_account_id', 'property_info_id', 'booking_source_form_id', 'status', 'created_at', 'updated_at'
    ];

    public function booking_source_form()
    {
        return $this->belongsTo('App\BookingSourceForm');
    }

    public function property_info()
    {
        return $this->belongsTo('App\PropertyInfo');
    }
	public function User()
    {
        return $this->belongsTo('App\User');
    }

	public function User_account()
    {
        return $this->belongsTo('App\UserAccount');
    }



    public function user_settings_bridge()
    {
        return $this->hasMany('App\UserSettingsBridge' );
    }


    /**
     * Get all of the PMS's Error.
     */
    public function error_logs()
    {
        return $this->morphMany('App\ErrorLog', 'errorable');
    }

    /**
     * Get the user's Booking Source Payment Settings.
     */
    //TODO Change With hasOneThrough and also from code 'payment_schedule_setting[0]' when FrameWork version updated >= 5.8
    public function payment_schedule_setting()
    {
        return $this->hasManyThrough(
            PaymentScheduleSettings::class,
            UserSettingsBridge::class,
            'user_booking_source_id',  // Foreign key on UserSettingsBridge table...
            'id',                  // Foreign key on PaymentScheduleSettings table...
            'id',                   // Local key on UserBookingSource table...
            'model_id'       // Local key on UserSettingsBridge table...
        )->where('model_name', PaymentScheduleSettings::class);
    }

    /**
     * Get the user's Booking Source SD Settings.
     */
    public function security_damage_deposit_setting()
    {
        return $this->hasManyThrough(
            SecurityDamageDepositSetting::class,
            UserSettingsBridge::class,
            'user_booking_source_id',  // Foreign key on UserSettingsBridge table...
            'id',                  // Foreign key on SecurityDamageDepositSetting table...
            'id',                   // Local key on UserBookingSource table...
            'model_id'       // Local key on UserSettingsBridge table...
        )->where('model_name', SecurityDamageDepositSetting::class);
    }


    /**
     * Get the user's Booking Source credit_card Auth  Settings.
     */
    public function credit_card_validation_setting()
    {
        return $this->hasManyThrough(
            CreditCardValidationSetting::class,
            UserSettingsBridge::class,
            'user_booking_source_id',  // Foreign key on UserSettingsBridge table...
            'id',                  // Foreign key on CreditCardValidationSetting table...
            'id',                   // Local key on UserBookingSource table...
            'model_id'       // Local key on UserSettingsBridge table...
        )->where('model_name', CreditCardValidationSetting::class);
    }

    /**
     * Get the user's Booking Source cancellation  Settings.
     */
    public function cancellation_setting()
    {
        return $this->hasManyThrough(
            CancellationSetting::class,
            UserSettingsBridge::class,
            'user_booking_source_id',  // Foreign key on UserSettingsBridge table...
            'id',                  // Foreign key on CancellationSetting table...
            'id',                   // Local key on UserBookingSource table...
            'model_id'       // Local key on UserSettingsBridge table...
        )->where('model_name', CancellationSetting::class);
    }
}
