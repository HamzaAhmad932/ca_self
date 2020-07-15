<?php


namespace App\Jobs\Auth\BA;


use App\BookingInfo;
use App\PropertyInfo;
use App\BAModels\AuthView;
use App\Repositories\Bookings\Bookings;
use App\UserPaymentGateway;
use App\AuthorizationDetails;
use Illuminate\Support\Carbon;
use App\CreditCardAuthorization;
use App\Events\Emails\EmailEvent;
use Illuminate\Support\Facades\Log;
use App\Events\PMSPreferencesEvent;
use App\Events\Auth\BA\AuthResponseEvent;
use App\System\PaymentGateway\Models\Card;
use App\System\PaymentGateway\Models\GateWay;
use App\System\PaymentGateway\PaymentGateway;
use App\Repositories\Settings\PaymentTypeMeta;
use App\System\PaymentGateway\Models\Transaction;
use App\Repositories\Settings\CreditCardValidation;
use App\Repositories\PaymentGateways\PaymentGateways;
use App\System\PaymentGateway\Exceptions\GatewayException;

trait AuthJobsHelperTrait
{

    public $paymentType;
    public $user_payment_gateway;
    public $SDAutoAuthType;
    public $CCAutoAuthType;
    public $SDManualAuthType;

    public function init_helper() {

        $this->paymentType = new PaymentTypeMeta();
        $this->user_payment_gateway = new PaymentGateways();
        $this->SDAutoAuthType = $this->paymentType->getSecurityDepositAutoAuthorize();
        $this->CCAutoAuthType = $this->paymentType->getCreditCardAutoAuthorize();
        $this->SDManualAuthType = $this->paymentType->getSecurityDepositManualAuthorize();
    }

    public function getHeads(){

        if (empty($this->user_payment_gateway)){
            $this->init_helper();
        }

        return $this->user_payment_gateway;
    }

    public function voidIfPropertyIsDisabled($bookingInfo, $cAuth, $property_status) {

        // This function is deprecated because this functionality moved to property connect/disconnect code
        try {

            if ($property_status == 0) {

                $cAuth->status = CreditCardAuthorization::STATUS_PAUSED;
                $cAuth->save();
                $message = 'Paused due to property disabled';
                $userPaymentGateway = $this->user_payment_gateway->getPropertyPaymentGatewayFromBooking($bookingInfo);
                $gwTransaction = $cAuth->transaction_obj;

                //$this->insertAuthLog($cAuth, $gwTransaction, $userPaymentGateway, $message);
                return false;
            }
            return true;

        } catch (\Exception $e) {
            Log::error($e->getMessage(), [
                'File' => __FILE__,
                'Function' => __FUNCTION__,
                'CCAUthId' => $cAuth->id]);
            return false;
        }

    }

    public function insertAuthLog($record, $payment_status, Transaction $response = null, $message = null) {

        $ccAuthDetail = new AuthorizationDetails();
        $ccAuthDetail->cc_auth_id = $record->id;
        $ccAuthDetail->user_account_id = $record->user_account_id;
        $ccAuthDetail->payment_processor_response = !empty($response) ? json_encode($response) : '';
        $ccAuthDetail->payment_gateway_name = $record->user_payment_gateway->payment_gateway_form->name;
        $ccAuthDetail->payment_gateway_form_id = $record->user_payment_gateway->payment_gateway_form->id;
        $ccAuthDetail->payment_status = $payment_status;
        $ccAuthDetail->amount = $record->hold_amount;
        $ccAuthDetail->charge_ref_no = !empty($response->token) ? $response->token : '';
        $ccAuthDetail->order_id = !empty($response->order_id) ? $response->order_id : 0;
        $ccAuthDetail->client_remarks = $message;
        $ccAuthDetail->error_msg = !empty($response->exceptionMessage) ? $response->exceptionMessage : $response->message;
        $ccAuthDetail->save();

        return $ccAuthDetail;
    }

    public function isAnyPaidTransactionFound($transactions){

        if (!empty($transactions)) {
            foreach ($transactions as $trans) {
                if ($trans->payment_status == 1) {
                    return true;
                }
            }
        }

        return false;
    }

    public function voidAuthRecord(CreditCardAuthorization $cAuth) {

        $cAuth->update(['status'=> CreditCardAuthorization::STATUS_VOID, 'next_due_date' => null, 'attempts'=> $cAuth->attempts + 1]);
    }

    public function cancelAuthorization(AuthView $record, Transaction $transaction_obj, UserPaymentGateway $gateway) {

        try {

            $pg = new PaymentGateway();
            $cancellationResponse = $pg->cancelAuthorization($transaction_obj, $gateway);
            $cAuth = $record->credit_card_authorization;
            $this->insertAuthLog($record, $cancellationResponse->status, $transaction_obj, 'Cancelled Authorization, for ReAuthorization.');
            /**
             * SD Auth Refund Success | Failed
             */
            if ((($cAuth->type == $this->SDAutoAuthType) || ($cAuth->type == $this->SDManualAuthType)) && $cancellationResponse->status)
                event(new PMSPreferencesEvent($cAuth->userAccount, $cAuth->booking_info, 0, config('db_const.user_preferences.preferences.SECURITY_DEPOSIT_REFUND_SUCCESS'), $cAuth->id));
            else if ((($cAuth->type == $this->SDAutoAuthType) || ($cAuth->type == $this->SDManualAuthType)) && !$cancellationResponse->status)
                event(new PMSPreferencesEvent($cAuth->userAccount, $cAuth->booking_info, 0, config('db_const.user_preferences.preferences.SECURITY_DEPOSIT_REFUND_FAILED'), $cAuth->id));

        } catch (GatewayException $e) {

            log_exception_by_exception_object($e);
        }
    }

    /**
     * If Checkout Date has passed then it cancels authorization and voids it
     * @param BookingInfo $bookingInfo
     * @param AuthView $record
     * @param UserPaymentGateway $userPaymentGateway
     * @return bool
     */
    public function isCheckoutDatePassed(BookingInfo $bookingInfo, AuthView $record, UserPaymentGateway $userPaymentGateway) {

        $checkoutTime = Carbon::parse($bookingInfo->check_out_date, 'GMT');
        $cAuth = $record->credit_card_authorization;

        if($checkoutTime->isPast()) {

            if($this->isAuthCancelable($cAuth, $cAuth->transaction_obj, $this->shouldAutoReAuth($cAuth))) {
                $this->cancelAuthorization($record, $cAuth->transaction_obj, $userPaymentGateway);
            }

            $this->voidAuthRecord($cAuth);

            return true;
        }

        return false;
    }

    public function shouldAutoReAuth(CreditCardAuthorization $cAuth) {
        return $cAuth->is_auto_re_auth == 1;
    }

    public function isAutoAuthEnable($is_auto_auth){
        return $is_auto_auth == 1;
    }

    public function isAuthCancelable(CreditCardAuthorization $cAuth, Transaction $gwTransaction, bool $shouldAutoReAuth = false) {
        return $gwTransaction != null && $gwTransaction->token != null && $gwTransaction->token != '' && $cAuth->token != null && $shouldAutoReAuth;
    }

    public function shouldAuth(CreditCardAuthorization $cAuth, Transaction $gwTransaction, bool $shouldAutoReAuth = false) {
        return $shouldAutoReAuth || (($gwTransaction != null && ($gwTransaction->token == '' || $gwTransaction->token == null)) && ($cAuth->token == null || $cAuth->token == ''));
    }

    public function getCard($record){

        $card = new Card();
        $card->firstName = $record->customer_object->first_name;
        $card->lastName = $record->customer_object->last_name;
        $card->token = $record->customer_object->token;
        $card->amount = $record->hold_amount;
        $card->currency = $record->transaction_obj->currency_code;
        $card->order_id = (int)round(microtime(true) * 1000);

        PaymentGateways::addMetadataInformation($record->booking_info, $card, __CLASS__);
        return $card;
    }

    public function authorizeNow(AuthView $record, Card $card)
    {

        $response = new Transaction();

        try {

            $response->amount = $card->amount;
            $response->currency_code = $card->currency;
            $response->order_id = $card->order_id;

            if ($card->amount == 0) {
                $response->status = true;
                $response->message = "Amount Was zero, auth not attempted";
                $record->credit_card_authorization->update(['next_due_date' => null, 'status' => CreditCardAuthorization::STATUS_VOID, 'remarks' => $response->message]);
                $this->insertAuthLog($record, CreditCardAuthorization::STATUS_ATTEMPTED, $response);
                return;
            }

            /** Attempt to auth */
            $PG = new PaymentGateway();
            $response = $PG->authorizeWithCustomer($record->customer_object, $card, $record->user_payment_gateway);

        } catch (GatewayException $e) {

            report($e);
            $response = $this->handleAuthException($record, $response, $e);

        } finally {

            if (!empty($response->CODE_NETWORK_ERROR_RE_TRY_ABLE)) {
                $this->authNetworkErrorCase($record, $response);
                return; // Network Problem try to charge later

            } elseif ($response->status && $response->state == Transaction::STATE_SUCCEEDED || $response->state == Transaction::STATE_REQUIRE_CAPTURE) {
                $this->authSuccessCase($record, $response); // Succeed

            } elseif ($response->state == Transaction::STATE_REQUIRE_ACTION) {
                $this->auth3dsRequiresCase($record, $response, $card); // 3DS Required

            } else {
                $this->authFailedCase($record, $response); // Failed
            }
        }
    }


    /**
     * @param AuthView $record
     * @param Transaction $response
     * @param GatewayException $exception
     * @return Transaction
     */
    public function handleAuthException(AuthView $record, Transaction $response, GatewayException $exception)
    {
        // Insufficient Funds Report PMS
        if ($exception->getDeclineCode() == GatewayException::ERROR_INSUFFICIENT_FUNDS)
            Bookings::BA_reportInvalidCardForBDCChannel($record->booking_info);

        switch ($exception->getCode()) {

            case PaymentGateway::CODE_NETWORK_ERROR_RE_TRY_ABLE :
                $response->CODE_NETWORK_ERROR_RE_TRY_ABLE = true;
                $response->exceptionMessage = $exception->getDescription();
                break;

            case PaymentGateway::ERROR_CODE_3D_SECURE :
                $response->paymentIntentId = null;
                $response->state = Transaction::STATE_REQUIRE_ACTION;
                break;

            default :
                $response->exceptionMessage = $exception->getDescription();
                break;
        }


        return $response;
    }

    public function authNetworkErrorCase(AuthView $record, Transaction $response)
    {
        $record->credit_card_authorization->update(['attempts_for_500' => $record->attempts_for_500 + 1]);
        event(
            new AuthResponseEvent(
                $record->credit_card_authorization,
                $response,
                AuthResponseEvent::AUTH_NETWORK_FAILURE_CASE,
                null
            )
        );
    }

    public function authSuccessCase(AuthView $record, Transaction $response)
    {
        $should_auto_reauth = $this->shouldAutoReAuth($record->credit_card_authorization);
        if($should_auto_reauth){
            $date_finder = new Carbon($record->next_due_date);
            $next_due_date = $date_finder->addDays(CreditCardValidation::$autoReauthorizeDays);
        }

        $record->credit_card_authorization->update(
            [
                'next_due_date'=> $should_auto_reauth ? $next_due_date : null,
                'token' => $response->token,
                'transaction_obj' => json_encode($response),
                'attempts' => $record->attempts + 1,
                'status' => CreditCardAuthorization::STATUS_ATTEMPTED,
            ]
        );

        $detail = $this->insertAuthLog($record, CreditCardAuthorization::STATUS_ATTEMPTED, $response);

        event(
            new AuthResponseEvent(
                $record->credit_card_authorization,
                $response,
                AuthResponseEvent::AUTH_SUCCESS_CASE,
                $detail
            )
        );
    }

    public function auth3dsRequiresCase(AuthView $record, Transaction $response, Card $card)
    {
        $response->status = CreditCardAuthorization::STATUS_WAITING_APPROVAL;
        $response->token = $response->paymentIntentId;
        $response->exceptionMessage = 'Guest needs to authenticate';
        $response->amount = $card->amount;
        $response->currency_code = $card->currency;

        $record->credit_card_authorization->update(
            [
                'transaction_obj' => json_encode($response),
                'attempts' => $record->attempts + 1,
                'payment_intent_id' => $response->paymentIntentId,
                'status' => CreditCardAuthorization::STATUS_WAITING_APPROVAL,
                'remarks' => $record->system_remarks . "\n Guest needs to authenticate",
            ]
        );

        $auth_detail = $this->insertAuthLog(
                $record,
                CreditCardAuthorization::STATUS_WAITING_APPROVAL,
                $response,
                'Guest needs to authenticate'
            );

        event(
            new AuthResponseEvent(
                $record->credit_card_authorization,
                $response,
                AuthResponseEvent::AUTH_3DS_REQUIRED_CASE,
                $auth_detail
            )
        );
    }

    public function setAuthRecordToReAttempt(AuthView $record, Transaction $response = null)
    {

        $attempts_full = ($record->attempts >= CreditCardAuthorization::TOTAL_ATTEMPTS - 1);
        $status        = $attempts_full
                        ? CreditCardAuthorization::STATUS_FAILED
                        : CreditCardAuthorization::STATUS_REATTEMPT;
        $next_due_date = $attempts_full
                        ? null
                        : Carbon::parse($record->next_due_date)->addHour($record->attempts + 1)->toDateTimeString();
        $reason = $status == CreditCardAuthorization::STATUS_REATTEMPT ? 'Will be reattempted at ' . $next_due_date : 'Failed after reattempts';

        $record->credit_card_authorization->update(
            [
                'attempts' => $record->attempts + 1,

                'remarks' => $record->remarks . (!empty($reason) ? "\n$reason" : ''),

                'status' => $status,

                'next_due_date' => $next_due_date,
            ]
        );

        return $this->insertAuthLog($record, $status, $response, $reason);

    }

    public function authFailedCase(AuthView $record, Transaction $response)
    {

        $detail = $this->setAuthRecordToReAttempt($record, $response);
        event(
            new AuthResponseEvent(
                $record->credit_card_authorization,
                $response,
                AuthResponseEvent::AUTH_FAILED_CASE,
                $detail
            )
        );
    }
}