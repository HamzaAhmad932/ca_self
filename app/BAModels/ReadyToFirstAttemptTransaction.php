<?php

namespace App\BAModels;

use App\BookingInfo;
use App\CreditCardInfo;
use App\PropertyInfo;
use App\System\PaymentGateway\Models\Customer;
use App\System\PaymentGateway\Models\Transaction;
use App\TransactionInit;
use App\UserAccount;
use App\UserBookingSource;
use App\UserPaymentGateway;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ReadyToFirstAttemptTransaction
 * @package App
 * @property int id
 * @property int booking_info_id
 * @property int pms_id
 * @property string due_date
 * @property string next_attempt_time
 * @property string update_attempt_time
 * @property string price
 * @property int payment_status
 * @property int user_account_id
 * @property string charge_ref_no
 * @property string last_success_trans_obj
 * @property int lets_process
 * @property string  final_tick
 * @property string system_remarks
 * @property int split
 * @property string against_charge_ref_no
 * @property int type
 * @property int status
 * @property int transaction_type
 * @property string client_remarks
 * @property string error_code_id
 * @property int  attempt
 * @property int attempts_for_500
 * @property int decline_email_sent
 * @property string remarks
 * @property string payment_intent_id
 * @property int  pms_booking_id
 * @property string guest_name
 * @property string guest_last_name
 * @property string guest_address
 * @property string guest_country
 * @property string guest_post_code
 * @property string guest_phone
 * @property string guestMobile
 * @property string pms_form_id
 * @property int property_info_id
 * @property int pms_property_id
 * @property string property_key
 * @property string currency_code
 * @property string f_name
 * @property string card_name
 * @property string l_name
 * @property string cc_last_4_digit
 * @property string cc_exp_month
 * @property string cc_exp_year
 * @property Customer customer_object
 * @property string credit_card_auth_token
 * @property int credit_card_info_id
 * @property Transaction auth_transaction_obj
 * @property int user_booking_source_id
 * @property int user_payment_gateway_id
 * @property string is_vc
 *
 * @property BookingInfo booking_info
 * @property UserAccount user_account
 * @property CreditCardInfo cc_info
 * @property UserPaymentGateway user_payment_gateway
 * @property PropertyInfo property_info
 * @property UserBookingSource user_booking_source
 * @property TransactionInit transaction_init
*/


class ReadyToFirstAttemptTransaction extends Model
{
    public function booking_info(){
        return $this->belongsTo(BookingInfo::class);
    }

    public function user_account() {
        return $this->belongsTo(UserAccount::class);
    }

    public function cc_info() {
        return $this->belongsTo(CreditCardInfo::class, 'credit_card_info_id', 'id');
    }

    public function user_payment_gateway() {
        return $this->belongsTo(UserPaymentGateway::class);
    }

    public function property_info() {
        return $this->belongsTo(PropertyInfo::class);
    }

    public function user_booking_source() {
        return $this->belongsTo(UserBookingSource::class);
    }

    public function transaction_init() {
        return $this->belongsTo(TransactionInit::class, 'id', 'id');
    }

    /**
     * @param $customerObject string
     * @return Customer
     */
    public function getCustomerObjectAttribute($customerObject) {
        return new Customer($customerObject);
    }

    /* Accessors */
    public function getAuthTransactionObjAttribute($transaction_obj){
        return new Transaction($transaction_obj);
    }
}
