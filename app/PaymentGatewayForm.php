<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * Class PaymentGatewayForm
 * @package App
 *
 * @property int id
 * @property string name
 * @property string logo
 * @property string backend_name
 * @property string gateway_form
 * @property string status
 * @property int payment_gateway_parent_id
 */

class PaymentGatewayForm extends Model implements Auditable
{
    use AuditableTrait;
    protected $fillable = ['name', 'logo', 'backend_name', 'gateway_form', 'payment_gateway_parent_id', 'status'];

    public function payment_gateway_parent() {
        return $this->belongsTo('App\PaymentGatewayParent');
    }

    public function user_payment_gateway() {
        return $this->hasMany('App\UserPaymentGateway');
    }

}
