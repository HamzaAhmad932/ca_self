<?php


namespace App\Repositories\CreditCardInfo;

use App\BookingInfo;
use App\CreditCardAuthorization;
use App\CreditCardInfo;
use App\Entities\Card as CardObject;
use App\Http\Controllers\Guest\GuestController;
use App\Jobs\CCReAuthJob;
use App\Jobs\ReAttemptJob;
use App\Repositories\Bookings\Bookings;
use App\Repositories\PaymentGateways\PaymentGateways;
use App\System\PaymentGateway\Exceptions\GatewayException;
use App\System\PaymentGateway\Models\Card;
use App\System\PaymentGateway\PaymentGateway;
use App\TransactionInit;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class CreditCardInfoRepo
{
    /**
     * @param CardObject $card_obj
     * @param Int $booking_info_id
     * @return bool
     */
    public function cardUpdate(CardObject $card_obj, Int $booking_info_id) {

        try{

            $bookingInfo = BookingInfo::where('id', $booking_info_id)->first();

            if(!$bookingInfo){
                return false;
            }

            $upg = new PaymentGateways(); /** To get UserPaymentGateway*/
            $userPaymentGateway = $upg->getPropertyPaymentGatewayFromBooking($bookingInfo);

            $b = new Bookings($bookingInfo->user_account_id);
            $currency_code = $b->getCurrencyCode($bookingInfo);

            $str = $card_obj->cc_exp_m.'/'.$card_obj->cc_exp_y;

            $dt = Carbon::createFromFormat('m/Y', $card_obj->cc_exp_m.'/'.$card_obj->cc_exp_y);


            $card = new Card();
            $card->cardNumber = str_replace(' ', '', $card_obj->cc_num);
            $card->expiryMonth = $dt->month;
            $card->expiryYear = $dt->year;
            $card->cvvCode = $card_obj->cc_cvv;
            $firstName = '';
            $lastName = '';

            if(isset($card_obj->cc_name) && $card_obj->cc_name != '') {
                $split = explode(' ', $card_obj->cc_name);
                if(count($split) > 1)
                    $firstName =  $split[0];
            }

            if(isset($card_obj->cc_name) && $card_obj->cc_name != '') {
                $split = explode(' ', $card_obj->cc_name);
                if(count($split) > 1) {
                    $last = '';
                    for($i = 1; $i < count($split); $i++)
                        $last .= ' ' . $split[$i];
                    $lastName =  trim($last);
                }
            }

            $card->firstName = $firstName == '' ? $bookingInfo->guest_name : $firstName;
            $card->lastName = $lastName == '' ? $bookingInfo->guest_last_name : $lastName;
            $card->eMail = $bookingInfo->guest_email;

            PaymentGateways::addMetadataInformation($bookingInfo, $card, GuestController::class);

            try{
                $pg = new PaymentGateway();
                $resp = $pg->addAsCustomer($card, $userPaymentGateway);

            }catch(GatewayException $e){

                return false;
            }

            if($resp->succeeded) {

                $cc_info = CreditCardInfo::create([
                        'booking_info_id'=>$bookingInfo->id,
                        'user_account_id'=>$bookingInfo->user_account_id,
                        'card_name' => $card->firstName . ' ' . $card->lastName,
                        'f_name'=>$resp->first_name,
                        'l_name'=>$resp->last_name,
                        'cc_last_4_digit'=>$resp->last_four_digits,
                        'cc_exp_month'=> $dt->month,
                        'cc_exp_year'=> $dt->year,
                        //'cc_cvc_num'=>$card->cvvCode,
                        'customer_object'=>json_encode($resp),
//                    'system_usage' => Card::encrypt($card),
                        'system_usage' => '',
                        'auth_token'=>$resp->token,
                        'attempts' => 1,
                        'is_vc' => 0,
                        'status'=>1]
                );

                /***********************************************************
                 * Updating Transaction Init Entries (turning lets_process 1)
                 */
                //if($bookingInfo->check_in_date > Carbon::now()->toDateTimeString()){
                $trans = $bookingInfo->transaction_init
                    ->whereIn('payment_status', [
                        TransactionInit::PAYMENT_STATUS_PENDING,
                        TransactionInit::PAYMENT_STATUS_REATTEMPT,
                        TransactionInit::PAYMENT_STATUS_FAIL
                    ])->where('transaction_type', '<', 4);

                foreach($trans as $tran){
                    $tran->lets_process = 1;
                    if (($tran->payment_status == TransactionInit::PAYMENT_STATUS_FAIL) || ($tran->payment_status == TransactionInit::PAYMENT_STATUS_REATTEMPT) ) {
                        $tran->payment_status = TransactionInit::PAYMENT_STATUS_REATTEMPT;
                        $tran->attempt = 0;
                        $tran->next_attempt_time = now()->addMinute('-5')->toDateTimeString();
                    }
                    $tran->decline_email_sent = 0;
                    $tran->save();
                }

                //}
                $auths = $bookingInfo->booking_auths();
                foreach($auths as $auth) {
                    if (($auth->status == CreditCardAuthorization::STATUS_FAILED) || ($auth->status == CreditCardAuthorization::STATUS_REATTEMPT)){
                        $auth->next_due_date = now()->addMinute('-5')->toDateTimeString();
                        $auth->status = CreditCardAuthorization::STATUS_REATTEMPT;
                    }
                    $auth->cc_info_id = $cc_info->id;
                    $auth->decline_email_sent = 0;
                    $auth->save();
                }

                return true;
            }
            
        }
        catch(Exception $e){
            Log::critical($e->getMessage(), ['file'=> CreditCardInfoRepo::class]);
            return false;
        }
    }
}
