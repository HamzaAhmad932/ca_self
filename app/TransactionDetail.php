<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * Class TransactionDetail
 * @package App
 * @property int id
 * @property int transaction_init_id
 * @property string name
 * @property int user_account_id
 * @property int user_id
 * @property string payment_processor_response
 * @property int payment_status
 * @property string client_remarks
 * @property string charge_ref_no
 * @property string error_msg
 * @property double amount
 * @property double order_id
 * @property int payment_gateway_form_id
 * @property int cc_info_id
 */
class TransactionDetail extends Model implements Auditable
{
    use AuditableTrait;

    protected  $fillable = ['transaction_init_id', 'name', 'user_account_id', 'user_id', 'payment_processor_response',
        'payment_status', 'client_remarks', 'charge_ref_no', 'error_msg', 'amount', 'order_id',
        'payment_gateway_form_id', 'cc_info_id'];

    public function user_account() {
        return $this->belongsTo('App\UserAccount');
    }
    public function transaction_init() {
        return $this->belongsTo('App\TransactionInit');
    }
    public function payment_gateway_form() {
        return $this->belongsTo('App\PaymentGatewayForm');
    }
    public function ccinfo()
    {
        return $this->belongsTo(CreditCardInfo::class, 'cc_info_id');
    }


//    public function booking_info() {
//        return $this->hasManyThrough(BookingInfo::class,TransactionInit::class,'id','_4t3id','p_id','booking_info_id');
//    }

}
