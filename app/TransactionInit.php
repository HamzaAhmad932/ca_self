<?php

namespace App;

use App\BookingInfo;
use App\CreditCardInfo;
use App\RefundDetail;
use App\BookingSourceForm;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\System\PaymentGateway\Models\Transaction;

/**
 * Class TransactionInit
 * @package App
 * @property $booking_info_id
 * @property $pms_id
 * @property $due_date
 * @property $update_attempt_time
 * @property $price
 * @property $is_modified
 * @property $payment_status
 * @property $user_id
 * @property $user_account_id
 * @property $charge_ref_no
 * @property $lets_process
 * @property $final_tick
 * @property $system_remarks
 * @property $split
 * @property $against_charge_ref_no
 * @property $type
 * @property $status
 * @property $transaction_type
 * @property $client_remarks
 * @property $auth_token
 * @property $error_code_id
 * @property int $attempt
 * @property $attempts_for_500
 * @property $next_attempt_time
 * @property $last_success_trans_obj
 * @property $decline_email_sent
 * @property $remarks
 * @property $payment_intent_id
 * @property $id
 * @property BookingInfo booking_info
 * @property mixed transactions_detail
 * @property mixed user_account
 */
class TransactionInit extends Model implements Auditable
{
    use AuditableTrait;

    const PAYMENT_STATUS_FAIL = '0';
    const PAYMENT_STATUS_SUCCESS = '1';
    const PAYMENT_STATUS_PENDING = '2';
    const PAYMENT_STATUS_VOID = '3';
    const PAYMENT_STATUS_REATTEMPT = '4';
    const PAYMENT_STATUS_WAITING_APPROVAL = '5';
    const PAYMENT_STATUS_ABORTED = '6';
    const PAYMENT_STATUS_PAUSED = '11';
    const PAYMENT_STATUS_MANUALLY_VOID = '12';
    const PAYMENT_MARKED_AS_PAID = '13';
    const TRANSACTION_AVAILABLE_TO_PROCESS = 0;          /* Transaction Init Available to charge or Reattempt | Not Being Processing in Queue */
    const TRANSACTION_ADDED_IN_QUEUE_PROCESSING = 1;    /*  Transaction Init Being Processing in Queue  & Not Available to charge Manually         */
    const TRANSACTION_ADDED_IN_MANUAL_PROCESSING = 2;  /*   Transaction Init Being Processing  Manually & Not Available to charge By Queue       */

    const TOTAL_ATTEMPTS = '4';

    //ALL TransactionInit Transaction Types
    const TRANSACTION_TYPE_SECURITY_DAMAGE_DEPOSIT_REFUND    = 'SR';
    const TRANSACTION_TYPE_SECURITY_DAMAGE_DEPOSIT_CAPTURE   = 'CS';
    const TRANSACTION_TYPE_ADDITIONAL_SECURITY_DAMAGE_CHARGE = 'S'; //Additional Damage Charge manually by user
    const TRANSACTION_TYPE_ADDITIONAL_CHARGE = 'M'; //Additional Amount Charge / Charge More,  manually by user
    const TRANSACTION_TYPE_CHARGE = 'C';
    const TRANSACTION_TYPE_REFUND = 'R';

    public static $charge_type_transactions = [
        TransactionInit::TRANSACTION_TYPE_CHARGE,
        TransactionInit::TRANSACTION_TYPE_SECURITY_DAMAGE_DEPOSIT_CAPTURE,
        TransactionInit::TRANSACTION_TYPE_ADDITIONAL_SECURITY_DAMAGE_CHARGE,
        TransactionInit::TRANSACTION_TYPE_ADDITIONAL_CHARGE
    ];

    public static $refund_type_transactions = [
        TransactionInit::TRANSACTION_TYPE_REFUND,
        TransactionInit::TRANSACTION_TYPE_SECURITY_DAMAGE_DEPOSIT_REFUND,
    ];


    const COLUMN_TYPE = 'type';
    const COLUMN_PAYMENT_STATUS = 'payment_status';
    const COLUMN_PRICE = 'price';





    protected $fillable = ['booking_info_id', 'pms_id', 'due_date', 'update_attempt_time', 'price', 'is_modified',
        'payment_status', 'user_id', 'user_account_id', 'charge_ref_no', 'lets_process', 'final_tick', 'system_remarks',
        'split', 'against_charge_ref_no', 'type', 'status', 'transaction_type', 'client_remarks',
        'auth_token', 'error_code_id', 'attempt', 'attempts_for_500',  'next_attempt_time', 'last_success_trans_obj',
        'decline_email_sent', 'remarks', 'payment_intent_id'];

    public function user_account() {
        return $this->belongsTo('App\UserAccount');
    }
    public function transactions_detail() {
        return $this->hasMany('App\TransactionDetail');
    }
//    public function ccinfo()
//    {
//        return $this->belongsTo(CreditCardInfo::class, 'transaction_init_id', 'id');
//    }
    public function booking_info(){
        return $this->belongsTo(BookingInfo::class);
    }
    public function bookingSource(){
        return $this->belongsTo(BookingSourceForm::class, 'pms_id', 'pms_form_id');
    }

    public function refund_detail(){
        return $this->hasMany(RefundDetail::class);
    }

    //will auto set  Collection type , lower alphabets to capital alphabets
    public function getTypeAttribute($value) {
        return strtoupper($value);
    }
    
    public function setTypeAttribute($value) {
        $this->attributes['type'] = strtoupper($value);
    }

    public function setLastSuccessTransObjAttribute(Transaction $value) {
        $this->attributes['last_success_trans_obj'] = json_encode($value);
    }

    public function getLastSuccessTransObjAttribute($value) {
        return new Transaction($value);
    }

    public function pmsForm()
    {
        return $this->belongsTo('App\PmsForm', 'pms_id');
    }

    /* following function will return latest TransactionDetails credit_card_info*/


    public function cc_info_latest(){
        $trans_init_id = (!empty($this->transactions_detail()->latest()->first()->id)?$this->transactions_detail()->latest()->first()->id:"0");
        return $this->hasManyThrough(CreditCardInfo::class,TransactionDetail::class,'transaction_init_id','id','id','cc_info_id')
            ->whereRaw('transaction_details.id = '.$trans_init_id);
    }
    /** following function will return all TransactionDetails credit_card_infos */
    public function cc_infos(){
        return $this->hasManyThrough(CreditCardInfo::class,TransactionDetail::class,'transaction_init_id','id','id','cc_info_id');
    }
}

