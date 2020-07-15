<?php

namespace App;

use App\PaymentGatewayForm;
use App\UserPaymentGateway;
use App\CreditCardAuthorization;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\System\PaymentGateway\PaymentGateway;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @property int cc_auth_id
 * @property int user_account_id
 * @property false|string payment_processor_response
 * @property int payment_gateway_form_id
 * @property bool payment_status
 * @property string charge_ref_no
 * @property double order_id
 * @property int cc_info_id
 */
class AuthorizationDetails extends Model implements Auditable {

    use AuditableTrait;

    protected $fillable = ['cc_auth_id', 'cc_info_id', 'user_account_id', 'name', 'payment_processor_response',
        'payment_gateway_form_id', 'payment_gateway_name', 'amount', 'payment_status', 'charge_ref_no', 'client_remarks',
        'order_id', 'error_msg'];

    public function payment_gateway_form() {
        return $this->belongsTo(PaymentGatewayForm::class);
    }

    public function cc_auth() {
    	return $this->belongsTo(CreditCardAuthorization::class);
    }
}
