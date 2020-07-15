<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class CancellationSetting extends Model implements Auditable
{
    use AuditableTrait;

    const TRANSACTION_TYPE_VOID = 'void';
    const TRANSACTION_TYPE_REFUND = 'refund';
    const TRANSACTION_TYPE_CHARGE = 'charge';
    const NON_REFUNDABLE_BOOKING = 'nonRefundable';


    protected $fillable = [
        'settings'
    ];
    
 public function user_settings_bridge()
   {
        return $this->belongsTo('App\UserSettingsBridge');
    }


}
