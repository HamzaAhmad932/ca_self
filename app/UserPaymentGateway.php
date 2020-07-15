<?php

namespace App;

use App\RefundDetail;
use App\AuthorizationDetails;
use App\System\PaymentGateway\Models\GateWay;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @property PropertyInfo|null property_info
 * @property PaymentGatewayForm|null payment_gateway_form
 * @property UserAccount|null user_account_id
 * @property TransactionDetail|null transactions_detail
 * @property ErrorLog|null error_logs
 * @property UserSettingsBridge|null user_settings_bridge
 * @property string gateway
 * @property int id
 */

class UserPaymentGateway extends Model implements Auditable
{
    use AuditableTrait;

    protected $fillable = [
        'payment_gateway_form_id',
        'property_info_id',
        'user_id', 'user_account_id', 'gateway', 'payment_hold_day','is_verified'
    ];

    public function property_info()
    {
        return $this->belongsTo('App\PropertyInfo');
    }

    public function payment_gateway_form()
    {
        return $this->belongsTo('App\PaymentGatewayForm');
    }

    public function user_account_id()
    {
        return $this->belongsTo('App\UserAccount');
    }

    public function user_account()
    {
        return $this->belongsTo(UserAccount::class);
    }

    public function transactions_detail()
    {
        return $this->hasMany('App\TransactionDetail');
    }

    public function refund_detail()
    {
        return $this->hasMany(RefundDetail::class);
    }

    public function authorization_detail()
    {
        return $this->hasMany(AuthorizationDetails::class);
    }


    /**
     * Get all of the PMS's Error.
     */
    public function error_logs()
    {
        return $this->morphMany('App\ErrorLog', 'errorable');
    }

    public function user_settings_bridge()
    {
        return $this->belongsTo('App\UserSettingsBridge');
    }

    /**
     * @return GateWay
     */
    public function getGatewayObject() {
        return new GateWay($this->getAttribute('gateway'));
    }

}
