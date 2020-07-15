<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class UpsellOrder extends Model implements Auditable
{
    use AuditableTrait;
    protected $table = 'upsell_orders';
    protected $fillable = ['booking_info_id', 'cc_info_id', 'user_account_id', 'user_id', 'final_amount', 'status', 'commission_fee', 'charge_ref_no', 'last_success_trans_obj'];

    const STATUS_UNPAID = 0;
    const STATUS_PAID = 1;
    public function userAccount() {
        return $this->belongsTo(UserAccount::class);
    }


    public function bookingInfo() {
        return $this->belongsTo(BookingInfo::class);
    }

    

    public function upsellOrderDetails() {
        return $this->hasMany(UpsellOrderDetail::class);
    }

    public function ccInfo() {
        return $this->belongsTo(CreditCardInfo::class);
    }

}
