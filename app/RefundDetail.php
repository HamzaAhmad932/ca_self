<?php

namespace App;

use App\TransactionInit;
use App\UserPaymentGateway;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class RefundDetail extends Model implements Auditable
{
    use AuditableTrait;

    protected $fillable = ['transaction_init_id', 'booking_info_id', 'user_id', 'user_account_id', 'name',
        'payment_processor_response', 'user_payment_gateway_id', 'payment_status', 'charge_ref_no',
        'against_charge_ref_no', 'amount', 'order_id'
    ];

    public function transaction_init(){
    	return $this->belongsTo(TransactionInit::class);
    }

    public function user_payment_gateway() {
        return $this->belongsTo(UserPaymentGateway::class);
    }
    public function booking_info() {
        return $this->belongsTo(BookingInfo::class);
    }

    public function user_account() {
        return $this->belongsTo(UserAccount::class);
    }
}
