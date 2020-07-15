<?php

namespace App;

use App\System\PaymentGateway\Models\Transaction;
use Illuminate\Database\Eloquent\Model;

/**
 * Class RefundAbleTransactionInit
 * @package App
 * @property BookingInfo booking_info
 * @property int id
 * @property int pms_booking_id
 * @property int booking_info_id
 * @property Transaction last_success_trans_obj
 * @property float t_price
 * @property string charge_ref_no
 * @property float available_amount
 */
class RefundAbleTransactionInit extends Model
{

    public function booking_info(){
        return $this->belongsTo(BookingInfo::class);
    }

    public function getLastSuccessTransObjAttribute($value) {
        return new Transaction($value);
    }


}
