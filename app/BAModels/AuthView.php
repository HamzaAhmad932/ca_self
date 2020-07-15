<?php

namespace App\BAModels;

use App\BookingInfo;
use App\UserAccount;
use App\UserBookingSource;
use App\UserPaymentGateway;
use App\CreditCardAuthorization;
use Illuminate\Database\Eloquent\Model;
use App\System\PaymentGateway\Models\Customer;
use App\System\PaymentGateway\Models\Transaction;

class AuthView extends Model
{
    protected $table = 'auth_view';

    public function booking_info(){
        return $this->belongsTo(BookingInfo::class);
    }

    public function credit_card_authorization(){
        return $this->belongsTo(CreditCardAuthorization::class, 'id', 'id');
    }

    public function user_payment_gateway() {
        return $this->belongsTo(UserPaymentGateway::class);
    }

    public function user_booking_source() {
        return $this->belongsTo(UserBookingSource::class);
    }

    public function user_account() {
        return $this->belongsTo(UserAccount::class);
    }
    /**
     * @param $customerObject string
     * @return Customer
     */
    public function getCustomerObjectAttribute($customerObject) {
        return new Customer($customerObject);
    }

    /* Accessors */
    public function getTransactionObjAttribute($transaction_obj){
        return new Transaction($transaction_obj);
    }
}
