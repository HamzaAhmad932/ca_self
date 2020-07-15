<?php

namespace App;

use App\TransactionDetail;
use App\CreditCardAuthorization;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\System\PaymentGateway\Models\Customer;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * Class CreditCardInfo
 * @package App
 *
 * @property int decline_email_sent
 * @property UserAccount|null userAccount
 * @property int booking_info_id
 * @property int user_account_id
 * @property int is_vc
 * @property string f_name
 * @property string card_name
 * @property string l_name
 * @property string cc_last_4_digit
 * @property string cc_exp_month
 * @property string cc_exp_year
 * @property string system_usage
 * @property Customer customer_object
 * @property string auth_token
 * @property string due_date
 * @property int status
 * @property string error_message
 * @property int type
 * @property int id
 */
class CreditCardInfo extends Model implements Auditable
{
    use AuditableTrait;

    const TOTAL_ATTEMPTS = 4;

    protected $fillable=['booking_info_id', 'user_account_id', 'is_vc', 'card_name', 'f_name', 'l_name', 'cc_last_4_digit',
        'cc_exp_month','cc_exp_year', /*'cc_cvc_num',*/ 'system_usage','customer_object','auth_token', 'status',
        'attempts', 'error_message', 'due_date', 'country', 'is_3ds', 'type', 'is_default', 'decline_email_sent'];

    // protected $casts = ['customer_object' => Customer::class];
    protected $primaryKey = 'id';

    public function booking_info() {
        return $this->belongsTo('App\BookingInfo');
    }

    /**
     * @param $customerObject string
     * @return Customer
     */
    public function getCustomerObjectAttribute($customerObject) {
    	return new Customer($customerObject);
    }

    public function ccauth() {
        return $this->hasMany(CreditCardAuthorization::class, 'cc_info_id', 'id');
    }

    public function transactioninit() {
        // changed 'transaction_init_id' to 'booking_info_id'
        // used in CCReAuthJob.php file, consider it before changing !!!
        return $this->hasMany(TransactionInit::class, 'booking_info_id', 'booking_info_id');
    }

    public function transaction_details(){
        return $this->hasMany(TransactionDetail::class, 'cc_info_id');
    }

    public function userAccount()
    {
        return $this->belongsTo('App\UserAccount');
    }

    public function creditCardInfoAudits()
    {
        return $this->hasMany(Audit::class,'auditable_id', 'id')->where('auditable_type', 'App\CreditCardInfo');
    }

    public function upsellOrders()
    {
        return $this->hasMany(UpsellOrder::class);
    }
}

