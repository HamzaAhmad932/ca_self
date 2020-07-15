<?php

namespace App;

use App\UserAccount;
use App\CreditCardInfo;
use App\TransactionInit;
use App\BookingInfo;
use App\AuthorizationDetails;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\System\PaymentGateway\Models\Transaction;

/**
 * Class CreditCardAuthorization
 * @package App
 *
 * @property Transaction transaction_obj
 */
class CreditCardAuthorization extends Model implements Auditable
{
    const STATUS_PENDING = 0;
    const STATUS_ATTEMPTED = 1;
    const STATUS_VOID = 3;
    const STATUS_MANUAL_PENDING = 4;
    const STATUS_FAILED = 5;
    const STATUS_CHARGED = 6;
    const STATUS_REATTEMPT = 7;
    const STATUS_WAITING_APPROVAL = 10;
    const STATUS_PAUSED = 11;

    const TOTAL_ATTEMPTS = 4;
    
    use AuditableTrait;

    protected $fillable=['booking_info_id','cc_info_id','user_account_id','hold_amount', 'attempts', 'attempts_for_500',
        'token','transaction_obj', 'is_auto_re_auth', 'type', 'due_date', 'next_due_date','status', 'decline_email_sent',
        'remarks', 'payment_intent_id'];
    
    protected $dates = ['next_due_date'];

    public function ccinfo()
    {
        return $this->belongsTo(CreditCardInfo::class, 'cc_info_id');
    }
    public function booking_info()
    {
        return $this->belongsTo(BookingInfo::class);
    }

    public function userAccount()
    {
        return $this->belongsTo(UserAccount::class);
    }

    /* Accessors */
    public function getTransactionObjAttribute($transaction_obj){
        return new Transaction($transaction_obj);
    }

    public function authorization_details(){
        return $this->hasMany(AuthorizationDetails::class, 'cc_auth_id');
    }
}
