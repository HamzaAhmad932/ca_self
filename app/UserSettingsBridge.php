<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;


class UserSettingsBridge extends Model implements Auditable
{
   use AuditableTrait;
   /**
    * @property CreditCardValidationSetting|null credit_card_validation_setting
    * @property CancellationSetting|null cancellation_setting
    * @property SecurityDamageDepositSetting|null security_damage_deposit_setting
    * @property PaymentScheduleSettings|null payment_schedule_settings
    * @property UserPaymentGateway|null user_payment_gateway
    */
   protected $fillable = [
      'user_account_id', 'booking_source_form_id', 'property_info_id', 'model_name', 'model_id','user_booking_source_id'
   ];

   public function credit_card_validation_setting()
   {
      return $this->hasOne('App\CreditCardValidationSetting','id','model_id');
   }

   public function cancellation_setting()
   {
   return $this->hasOne('App\CancellationSetting','id','model_id');
   }

   public function security_damage_deposit_setting()
   {
      return $this->hasOne('App\SecurityDamageDepositSetting','id','model_id'); 
   }

   public function payment_schedule_settings()
   {   
      return $this->hasOne('App\PaymentScheduleSettings','id','model_id'); 
   }
  public function user_payment_gateway()
   { 
      return $this->hasOne('App\UserPaymentGateway','id','model_id');
   }
  
}
