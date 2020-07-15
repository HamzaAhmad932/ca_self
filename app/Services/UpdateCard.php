<?php


namespace App\Services;

use App\Repositories\TransactionInit\TransactionInitRepository;
use Carbon\Carbon;
use App\BookingInfo;
use App\CreditCardInfo;
use App\TransactionInit;
use App\CreditCardAuthorization;
use App\Entities\Card as CardObject;
use Illuminate\Support\Facades\Log;
use App\Repositories\Bookings\Bookings;
use App\Exceptions\UpdateCardException;
use App\System\PaymentGateway\Models\Card;
use App\System\PaymentGateway\PaymentGateway;
use App\System\PMS\BookingSources\BS_Generic;
use App\Repositories\Settings\PaymentTypeMeta;
use App\Http\Controllers\v2\client\BookingController;
use App\System\PaymentGateway\Exceptions\GatewayException;
use App\Repositories\PaymentGateways\PaymentGateways as PaymentGatewayRepo;
use App\Repositories\CreditCardAuthorization\CreditCardAuthorizationRepository;

trait UpdateCard
{
    /**
     * @param CardObject $card_obj
     * @param int $booking_id
     * @return mixed
     * @throws GatewayException
     * @throws UpdateCardException
     */
    public function updateCard(CardObject $card_obj, int $booking_id)
    {

        if(empty($card_obj->first_name)) {
            throw new UpdateCardException('First name is required.', 422);

        } elseif(empty($card_obj->last_name)) {
            throw new UpdateCardException('Last name is required.', 422);

        } elseif(empty($card_obj->token)) {
            throw new UpdateCardException('Number is required.', 422);
        }

        $booking_info = BookingInfo::where('id', $booking_id)->first();

        if(empty($booking_info)){
            throw new UpdateCardException('Booking not found.', 422);
        }
        
       try {

            $upg = new PaymentGatewayRepo();
            $user_payment_gateway = $upg->getPropertyPaymentGatewayFromBooking($booking_info);

            $card = new Card();
            $card->firstName = $card_obj->first_name;
            $card->lastName = $card_obj->last_name;
            $card->eMail = $booking_info->guest_email;
            $card->token = $card_obj->token;
            
            PaymentGatewayRepo::addMetadataInformation($booking_info, $card, BookingController::class);

            $pg = new PaymentGateway();
            $resp = $pg->addAsCustomerWithToken($card, $user_payment_gateway);

            if ($resp->succeeded) {

                $cc_info = CreditCardInfo::create([
                    'booking_info_id' => $booking_info->id,
                    'user_account_id' => $booking_info->user_account_id,
                    'card_name' => $card->firstName . ' ' . $card->lastName,
                    'f_name' => $resp->first_name,
                    'l_name' => $resp->last_name,
                    'cc_last_4_digit' => $resp->last_four_digits,
                    'cc_exp_month'=> $resp->month,
                    'cc_exp_year'=> $resp->year,
                    'customer_object' => json_encode($resp),
                    'system_usage' => '',
                    'auth_token' => $resp->token,
                    'attempts' => 1,
                    'is_vc' => 0,
                    'status' => 1]
                );

                if (!empty($cc_info)) {

                    // Updating Transaction Init Entries (turning lets_process 1)
                    TransactionInitRepository::updateTransactionInitsOnCustomerSuccess($booking_info, true);

                    // SD  & CC AUTH
                    CreditCardAuthorizationRepository::updateCreditCardAuthOnCustomerSuccess($booking_info, $cc_info->id);
                    CreditCardAuthorizationRepository::updateSecurityDamageDepositAuthOnCustomerSuccess($booking_info, $cc_info->id);
                }

                return $cc_info;

            } else {

                return null;
            }
        }
        catch (GatewayException $e){
            report($e);
            throw $e;
        }
        catch (UpdateCardException $e){
            throw $e;
        }
        catch (\Exception $e){
            report($e);
            throw $e;
        }
    }

    /**
     * THIS FUNCTION IS DEPRECATED AND FUNCTIONALITY MOVED TO CLASS "CreditCardAuthorizationRepository"
     * SD  & CC AUTH Entries update
     * @param BookingInfo $booking_info
     * @param int $cc_info_id
     *
     * THIS FUNCTION IS DEPRECATED AND FUNCTIONALITY MOVED TO CLASS "CreditCardAuthorizationRepository"
     */
    public static function creditCardAuthorizationEntriesUpdate(BookingInfo $booking_info, int $cc_info_id)
    {
        //THIS FUNCTION IS DEPRECATED AND FUNCTIONALITY MOVED TO CLASS "CreditCardAuthorizationRepository"

//        $auths = $booking_info->credit_card_authorization;
//
//        foreach ($auths as $auth) {
//
//            if ($auth->status == CreditCardAuthorization::STATUS_FAILED) {
//                $auth->status = CreditCardAuthorization::STATUS_REATTEMPT;
//            } elseif ($auth->status == CreditCardAuthorization::STATUS_MANUAL_PENDING) {
//                $auth->status = CreditCardAuthorization::STATUS_PENDING;
//            }
//
//            // Change Manual Type to Auto to auto-process in CC ReAuth Job
//            if($auth->type == config('db_const.credit_card_authorizations.type.security_damage_deposit_manual_auth')) {
//                $auth->type = config('db_const.credit_card_authorizations.type.security_damage_deposit_auto_auth');
//
//            } elseif ($auth->type == config('db_const.credit_card_authorizations.type.credit_card_manual_authorize')) {
//                $auth->type = config('db_const.credit_card_authorizations.type.credit_card_auto_authorize');
//            }
//
//            // Change Next due date to due date if status failed or reAttempt.
//            $auth->next_due_date = in_array($auth->status, [CreditCardAuthorization::STATUS_FAILED, CreditCardAuthorization::STATUS_REATTEMPT])
//                ? $auth->due_date : $auth->next_due_date;
//
//
//
//            //Void the CCauth if the booking payment due date is less than or equal to ccauth due date
//            if ($auth->type == config('db_const.credit_card_authorizations.type.credit_card_auto_authorize')
//                && $booking_info->transaction_init->where('due_date', '<=', now()->toDateTimeString())->where('type', TransactionInit::TRANSACTION_TYPE_CHARGE)->count()) {
//
//                $auth->status = !in_array($auth->status, [CreditCardAuthorization::STATUS_ATTEMPTED, CreditCardAuthorization::STATUS_CHARGED])
//                ? CreditCardAuthorization::STATUS_VOID : $auth->status;
//
//                $auth->is_auto_re_auth = 0;
//            }
//
//            $auth->cc_info_id = $cc_info_id;
//            $auth->attempts = 1;
//            $auth->save();
//        }

        Log::error('Deprecated function call, please use "CreditCardAuthorizationRepository" to call the right function.', [
            'file: '=> __FILE__,
        ]);
    }
}
