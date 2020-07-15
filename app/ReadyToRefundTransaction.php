<?PHP

namespace App;


use Illuminate\Database\Eloquent\Model;

/**
 * Class ReadyToRefundTransaction
 * @package App
 * @property int id
 * @property int attempt
 * @property int lets_process
 * @property int payment_status
 * @property int booking_info_id
 * @property float amount_to_refund
 * @property int pms_booking_id
 * @property int property_info_id
 * @property int pms_property_id
 * @property int attempts_for_500
 * @property int user_payment_gateway_id
 * @property int user_account_id
 * @property BookingInfo booking_info
 * @property UserAccount user_account
 * @property UserPaymentGateway user_payment_gateway
 * @property PropertyInfo property_info
 * @property TransactionInit transaction_init
 * @property mixed refund_able_transactions
 */

class ReadyToRefundTransaction extends Model
{
    public function booking_info()
    {
        return $this->belongsTo(BookingInfo::class);
    }


    public function refund_able_transactions()
    {
        return $this->hasMany(RefundAbleTransactionInit::class, 'booking_info_id', 'booking_info_id');
    }



    public function user_account()
    {
        return $this->belongsTo(UserAccount::class);
    }


    public function user_payment_gateway()
    {
        return $this->belongsTo(UserPaymentGateway::class);
    }

    public function property_info()
    {
        return $this->belongsTo(PropertyInfo::class);
    }

    public function transaction_init()
    {
        return $this->belongsTo(TransactionInit::class, 'id', 'id');
    }

    public function getAmountToRefundAttribute($value) {
        return abs($value);
    }
}

