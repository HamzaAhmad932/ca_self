<?php

namespace App\Jobs;

use App\AuthorizationDetails;
use App\BookingInfo;
use App\CreditCardAuthorization;
use App\CreditCardInfo;
use App\Events\Emails\EmailEvent;
use App\Events\PMSPreferencesEvent;
use App\Http\Controllers\PaymentConfirmation;
use App\PropertyInfo;
use App\Repositories\Bookings\Bookings;
use App\Repositories\PaymentGateways\PaymentGateways;
use App\Repositories\Settings\PaymentTypeMeta;
use App\System\PaymentGateway\Exceptions\GatewayException;
use App\System\PaymentGateway\Models\GateWay;
use App\System\PaymentGateway\Models\Transaction;
use App\System\PaymentGateway\PaymentGateway;
use App\System\PMS\BookingSources\BS_Generic;
use App\TransactionDetail;
use App\TransactionInit;
use App\UserAccount;
use App\UserPaymentGateway;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Stripe\Event;
use Stripe\PaymentIntent;


class PaymentConfirmationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const SOURCE_STRIPE_WEB_HOOK = 1;
    const SOURCE_STRIPE_REDIRECT_AFTER_3DS = 2;
    const SOURCE_CONTROLLER_CHARGE_SUCCESS = 3;
    const SOURCE_CONTROLLER_AUTH_SUCCESS = 4;

    /**
     * @var int
     */
    private $source;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var null
     */
    private $userAccountID;
    /**
     * @var string|null
     */

    private $isHook = false;
    /**
     * @var Event|null
     */
    private $event;
    /**
     * @var Transaction|null
     */
    private $transaction;
    /**
     * @var null
     */
    private $transactionInit_or_CC_auth;

    /**
     * Create a new job instance.
     *
     * @param int $source
     * @param Request $request
     * @param null $userAccountID
     * @param Event|null $event
     * @param Transaction|null $transaction
     * @param null $transactionInit_or_CC_auth
     */
    public function __construct(int $source, Request $request = null, $userAccountID = null, Event $event = null, Transaction $transaction = null, $transactionInit_or_CC_auth = null) {
        $this->source = $source;
        $this->request = $request;
        $this->userAccountID = $userAccountID;
        $this->event = $event;
        $this->transaction = $transaction;
        $this->transactionInit_or_CC_auth = $transactionInit_or_CC_auth;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        try {
            switch ($this->source) {
                case self::SOURCE_STRIPE_REDIRECT_AFTER_3DS:
                    $this->afterAuthentication($this->request, $this->userAccountID);
                    break;
                case self::SOURCE_STRIPE_WEB_HOOK:
                    $this->stripeWebHook($this->event);
                    break;
                case self::SOURCE_CONTROLLER_CHARGE_SUCCESS:
                    $this->successCaseCharge($this->transaction, $this->transactionInit_or_CC_auth);
                    break;
                case self::SOURCE_CONTROLLER_AUTH_SUCCESS:
                    $this->successCaseAuth($this->transaction, $this->transactionInit_or_CC_auth);
                    break;
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage(), [
                'File' => __FILE__,
                'Function' => __FUNCTION__,
                'FileObject' => json_decode(json_encode($this), true),
                'Stack' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * @param Event $event
     */
    public function stripeWebHook(Event $event) {

        $this->isHook = true;

        try {

            /**
             * @var $intent PaymentIntent
             */
            // Handle the event
            switch ($event->type) {

                case 'payment_intent.succeeded':
                    $intent = $event->data->object;
                    $transaction = $this->fetchPaymentIntentForStripe($intent->id, $intent->client_secret, null);

                    if ($transaction == null || $transaction->paymentIntentId == null)
                        return;// response(['OK'], 200);

                    $type = $this->getIntentTypeFor($transaction->paymentIntentId);

                    if ($type['type'] == PaymentConfirmation::INTENT_FOR_CHARGE)
                        $this->successCaseCharge($transaction, $type['object']);
                    elseif ($type['type'] == PaymentConfirmation::INTENT_FOR_AUTH)
                        $this->successCaseAuth($transaction, $type['object']);
                    break;

                case 'payment_intent.payment_failed':
                    $intent = $event->data->object;
                    $transaction = $this->fetchPaymentIntentForStripe($intent->id, $intent->client_secret, null);

                    if ($transaction == null || $transaction->paymentIntentId == null)
                        return; // response(['OK'], 200);

                    $type = $this->getIntentTypeFor($transaction->paymentIntentId);

                    if (!$transaction->status)
                        if ($type['type'] == PaymentConfirmation::INTENT_FOR_CHARGE)
                            $this->failedCaseCharge($transaction, $type['object']);
                        elseif ($type['type'] == PaymentConfirmation::INTENT_FOR_AUTH)
                            $this->failedCaseAuth($transaction, $type['object']);
                    break;

                default:
                    Log::error('Unexpected event type: ' . $event->type, ['File' => __FILE__, 'Function' => __FUNCTION__, 'Reason' => 'Unexpected event type']);
            }

        } catch (\Exception $e) {
            Log::error($e->getMessage(), [
                'File' => __FILE__,
                'Function' => __FUNCTION__,
                'FileObject' => json_decode(json_encode($this), true),
                'Stack' => $e->getTraceAsString()
            ]);
        }

    }

    /**
     * For 3D secure when guest Authenticates or denies request.
     * Route defined in web.php
     * @param Request $request
     * @param $userAccountID
     */
    public function afterAuthentication(Request $request, $userAccountID) {

        /**
         * Below code to place in afterAuthentication function for PaymentConfirmation Controller
         *
         * $guestPortalLink = URL::signedRoute('guest_booking_details', ['id'=>$type['object']->booking_info_id]);
         * header('location:' . $guestPortalLink);
         */

        if ($request->has('payment_intent') && $request->has('payment_intent_client_secret') && $userAccountID != null) {

            $transaction = $this->fetchPaymentIntentForStripe(
                $request->get('payment_intent'),
                $request->get('payment_intent_client_secret'),
                $userAccountID);

            if($transaction !== null) {

                $type = $this->getIntentTypeFor($transaction->paymentIntentId);

                if($type != null) {

                    if ($transaction->status) {

                        if($type['type'] == PaymentConfirmation::INTENT_FOR_CHARGE)
                            $this->successCaseCharge($transaction, $type['object']);
                        elseif($type['type'] == PaymentConfirmation::INTENT_FOR_AUTH)
                            $this->successCaseAuth($transaction, $type['object']);

                    } else {

                        if($type['type'] == PaymentConfirmation::INTENT_FOR_CHARGE)
                            $this->failedCaseCharge($transaction, $type['object']);
                        elseif($type['type'] == PaymentConfirmation::INTENT_FOR_AUTH)
                            $this->failedCaseAuth($transaction, $type['object']);
                    }

                }  else {
                    Log::error("Type Null", [
                        'File' => __FILE__,
                        'userAccountId' => $this->userAccountID,
                        'Request' => json_encode($this->request->toArray())
                    ]);
                }

            } else {
                Log::error("Transaction Null", [
                    'File' => __FILE__,
                    'userAccountId' => $this->userAccountID,
                    'Request' => json_encode($this->request->toArray())
                ]);
            }

        } else {
            Log::error("Not Found: 'payment_intent' or 'payment_intent_client_secret' or userAccountID != null", [
                'File' => __FILE__,
                'userAccountId' => $this->userAccountID,
                'Request' => json_encode($this->request->toArray())
            ]);
        }

    }

    /**
     * @param string $paymentIntentId
     * @param string $client_secret
     * @param int|null $userAccountID
     * @return Transaction|null
     */
    private function fetchPaymentIntentForStripe(string $paymentIntentId, string $client_secret = null, int $userAccountID = null) {

        $userAccount = null;

        if($userAccountID === null) {
            $transactionInit = TransactionInit::where('payment_intent_id', $paymentIntentId)->first();
            if($transactionInit == null)
                return null;
            $userAccountID = $transactionInit->user_account_id;
        }

        $userAccount = UserAccount::find($userAccountID);

        $userPaymentGateway = $userAccount->user_payment_gateways->first();

        $paymentGateway = new PaymentGateway();

        try {

            return $paymentGateway->afterAuthentication($paymentIntentId, $client_secret, $userPaymentGateway);

        }  catch (GatewayException $e) {
            Log::error($e->getMessage(), [
                'File' => PaymentConfirmation::class,
                'user_account_id' => $userAccountID,
                'stack' => $e->getTraceAsString()
            ]);
            // abort(403, 'Invalid Request Parameters');
        }

        return null;
    }

    /**
     * @param Transaction $transaction
     * @param TransactionInit $transactionInit
     * @return bool
     */
    public function successCaseCharge(Transaction $transaction, TransactionInit $transactionInit) {

        if(!empty($transaction->token)) {

            if($transactionInit->payment_status == TransactionInit::PAYMENT_STATUS_SUCCESS)
                return true;

            if ($transactionInit != null) {

                $transactionInit->attempt = $transactionInit->attempt + 1;
                $transactionInit->lets_process = 0;
                $transactionInit->payment_status = TransactionInit::PAYMENT_STATUS_SUCCESS;
                $transactionInit->charge_ref_no = $transaction->token;
                $transactionInit->last_success_trans_obj = $transaction;
                $msg = 'Successfully Charged via 3DS' . ($this->isHook ? '.' : '');
                $sysRemarks = $transactionInit->system_remarks;
                $transactionInit->system_remarks = empty($sysRemarks) ? $msg : $sysRemarks . '. ' . $msg;
                $transactionInit->save();

                $userAccount = UserAccount::find($transactionInit->user_account_id);
                $bookingInfo = BookingInfo::find($transactionInit->booking_info_id);
                $preferenceFormId = config('db_const.user_preferences.preferences.PAYMENT_SUCCESS');

                $propertyInfo = PropertyInfo::where('pms_property_id', $bookingInfo->property_id)
                    ->where('pms_id', $bookingInfo->pms_id)
                    ->where('user_account_id', $transactionInit->user_account_id)
                    ->first();

                $upg = new PaymentGateways();
                $gateway = $upg->getPropertyPaymentGatewayFromProperty($propertyInfo);

                $isVC = false;
                if($bookingInfo->is_vc == 'VC')
                    $isVC = true;

                $ccInfo = null;
                if($isVC){
                    $ccInfo = CreditCardInfo::where('booking_info_id', $transactionInit->booking_info_id)->where('is_vc', 1)->latest('id')->limit(1)->get();
                }else{
                    $ccInfo = CreditCardInfo::where('booking_info_id', $transactionInit->booking_info_id)->latest('id')->limit(1)->get();
                }

                $transactionInit->message = $msg;
                $transactionInit->exceptionMessage = '';
                $this->insertTransactionDetailLog($transactionInit, $transaction, $ccInfo[0], $gateway);

                if ($userAccount != null && $bookingInfo != null) {
                    event(new PMSPreferencesEvent($userAccount, $bookingInfo, $transactionInit->id, $preferenceFormId));
                }

            }

        } else {
            Log::error("3DS payment not charged.",
                ['File'=>PaymentConfirmation::class,
                    'Transaction' => json_decode(json_encode($transaction), true)]);
        }

        return true;
    }

    /**
     * @param Transaction $transaction
     * @param CreditCardAuthorization $creditCardAuthorization
     * @return bool
     */
    public function successCaseAuth(Transaction $transaction, CreditCardAuthorization $creditCardAuthorization) {
        if($transaction !== null && $creditCardAuthorization !== null) {

            $transaction->type = 'Authorize';
            $creditCardAuthorization->status = CreditCardAuthorization::STATUS_ATTEMPTED;
            $creditCardAuthorization->token = $transaction->token;
            $creditCardAuthorization->transaction_obj = json_encode($transaction);

            if($creditCardAuthorization->is_auto_re_auth == 1) {
                $creditCardAuthorization->next_due_date = Carbon::now('GMT')->addDays(7)->toDateTimeString();
            }

            $creditCardAuthorization->save();

            $paymentType = new PaymentTypeMeta();
            $sDAA = $paymentType->getSecurityDepositAutoAuthorize();
            $sDMA = $paymentType->getSecurityDepositManualAuthorize();

            $bookingInfo = $creditCardAuthorization->booking_info;
            $userAccount = $creditCardAuthorization->userAccount;

            $message = 'Successfully Authorized';
            $upg = new PaymentGateways();
            $gateway = $upg->getPropertyPaymentGatewayFromBooking($bookingInfo);
            $this->insertAuthLog($creditCardAuthorization, $transaction, $gateway, $message);

            if (($creditCardAuthorization->type == $sDAA) || ($creditCardAuthorization->type == $sDMA))
                event(new PMSPreferencesEvent($userAccount, $bookingInfo, 0, config('db_const.user_preferences.preferences.SECURITY_DEPOSIT_AUTH_CAPTURE_SUCCESS'), $creditCardAuthorization->id));
            else
                event(new PMSPreferencesEvent($userAccount, $bookingInfo, 0, config('db_const.user_preferences.preferences.CREDIT_CARD_VALIDATION_AUTH_SUCCESS'), $creditCardAuthorization->id));

        }
        return true;
    }

    /**
     * @param Transaction $transaction
     * @param CreditCardAuthorization $creditCardAuthorization
     * @return bool
     */
    private function failedCaseAuth(Transaction $transaction, CreditCardAuthorization $creditCardAuthorization) {

        if($transaction !== null && $creditCardAuthorization !== null) {

            $transaction->type = 'Authorize';
            $bookingInfo = $creditCardAuthorization->booking_info;
            $userAccount = $creditCardAuthorization->userAccount;

            $creditCardAuthorization->status = 5;
            $creditCardAuthorization->attempts = $creditCardAuthorization->attempts + 1;
            $creditCardAuthorization->save();

            Bookings::BA_reportInvalidCardForBDCChannel($bookingInfo);

            $paymentType = new PaymentTypeMeta();
            $sDAA = $paymentType->getSecurityDepositAutoAuthorize();
            $sDMA = $paymentType->getSecurityDepositManualAuthorize();

            if ($creditCardAuthorization->decline_email_sent == 0) {

                if ($creditCardAuthorization->type == $paymentType->getAuthTypeCCValidation()) {
                    $auth_failed = true;
                } else if ($creditCardAuthorization->type == $paymentType->getAuthTypeSecurityDamageValidation()) {

                    /**
                     * @var $gwTransaction Transaction
                     */
                    $gwTransaction = $creditCardAuthorization->transaction_obj;

                    $amountWithSymbol = $gwTransaction->currency_code . ' ' . number_format($creditCardAuthorization->hold_amount, 2);
                    $sd_authorization_failed = true;
                }
            }

            $message = 'Authentication denied or Failed';
            $upg = new PaymentGateways();
            $gateway = $upg->getPropertyPaymentGatewayFromBooking($bookingInfo);
            $ccAuthDetail = $this->insertAuthLog($creditCardAuthorization, $transaction, $gateway, $message);

            if (!empty($auth_failed) || !empty($sd_authorization_failed)) {

                /* Send Email to Guest on Auth Failed */

                if (!empty($sd_authorization_failed))
                    event(new EmailEvent(config('db_const.emails.heads.sd_auth_failed.type'),  $creditCardAuthorization->id, ['error_msg' => $message]));


                if (!empty($auth_failed))
                    event(new EmailEvent(config('db_const.emails.heads.auth_failed.type'), $ccAuthDetail->id));

                $creditCardAuthorization->decline_email_sent = 1;
                $creditCardAuthorization->save();
            }

            /**
             * SDD Auth | CC Auth Failed
             */
            if (($creditCardAuthorization->type == $sDAA) || ($creditCardAuthorization->type == $sDMA))
                event(new PMSPreferencesEvent($userAccount, $bookingInfo, 0, config('db_const.user_preferences.preferences.SECURITY_DEPOSIT_AUTH_CAPTURE_FAILED'), $creditCardAuthorization->id));
            else
                event(new PMSPreferencesEvent($userAccount, $bookingInfo, 0, config('db_const.user_preferences.preferences.CREDIT_CARD_VALIDATION_AUTH_FAILED'), $creditCardAuthorization->id));

        }
        return true;
    }

    /**
     * @param Transaction $transaction
     * @param TransactionInit $transactionInit
     * @return bool
     */
    private function failedCaseCharge(Transaction $transaction, TransactionInit $transactionInit) {

        $tdCount = TransactionDetail::where('charge_ref_no', $transaction->paymentIntentId)
            ->where('payment_status', TransactionInit::PAYMENT_STATUS_FAIL)
            ->where('transaction_init_id', $transactionInit->id)
            ->count();

        if($tdCount > 0) {
            return true;
        }

        $propertyInfo = null;

        if($transactionInit != null) {

            $bookingInfo = BookingInfo::find($transactionInit->booking_info_id);

            $isVc = false;
            if($bookingInfo->is_vc == BS_Generic::PS_VIRTUAL_CARD)
                $isVc = true;

            $ccInfo = null;
            if($isVc)
                $ccInfo = CreditCardInfo::where('booking_info_id', $transactionInit->booking_info_id)->where('is_vc', 1)->latest('id')->limit(1)->get();
            else
                $ccInfo = CreditCardInfo::where('booking_info_id', $transactionInit->booking_info_id)->latest('id')->limit(1)->get();

            $userAccount = UserAccount::find($transactionInit->user_account_id);

            if($userAccount != null && $bookingInfo != null) {

                $propertyInfo = PropertyInfo::where('pms_property_id', $bookingInfo->property_id)
                    ->where('pms_id', $bookingInfo->pms_id)
                    ->where('user_account_id', $transactionInit->user_account_id)
                    ->first();
            }

            $transactionInit->attempt = $transactionInit->attempt + 1;
            $transactionInit->payment_status = TransactionInit::PAYMENT_STATUS_REATTEMPT;
            $transactionInit->last_success_trans_obj = $transaction;
            $msg = 'Authentication denied or Failed';
            $sysRemarks = $transactionInit->system_remarks;
            $transactionInit->system_remarks = empty($sysRemarks) ? $msg : $sysRemarks . '. ' . $msg;
            $oldAttemptTime = $transactionInit->next_attempt_time;
            $nextAttemptTime = new Carbon($oldAttemptTime);
            $transactionInit->next_attempt_time = $nextAttemptTime->addHour(1);
            $transactionInit->save();

            if($propertyInfo != null) {
                $upg = new PaymentGateways();
                $gateway = $upg->getPropertyPaymentGatewayFromProperty($propertyInfo);

                $transactionInit->message = $msg;
                $transactionInit->exceptionMessage = '';
                $transactionInit->payment_status = TransactionInit::PAYMENT_STATUS_FAIL;
                $this->insertTransactionDetailLog($transactionInit, $transaction, $ccInfo[0], $gateway);


                if(!$isVc){

                    if($transactionInit->decline_email_sent == 0) {

                        if($propertyInfo != null) {

                            try {
                                /* Send Email to Guest on Payment Failed */
                                event(new EmailEvent(config('db_const.emails.heads.payment_failed.type'), $transactionInit->id, ['reason' => $msg]));

                                $transactionInit->decline_email_sent = 1;
                            } catch (\Exception $e) {
                                Log::error($e->getMessage(), ['File' => BAChargeJob::class, 'Stack' => $e->getTraceAsString()]);
                            }
                        }
                    }
                }

            }

        }

        return true;
    }

    private function insertTransactionDetailLog(TransactionInit $transactionInit, Transaction $transaction, CreditCardInfo $ccInfo, UserPaymentGateway $gateway) {

        $transDetail = new TransactionDetail();
        $transDetail->transaction_init_id = $transactionInit->id;
        $transDetail->cc_info_id = $ccInfo->id;
        $transDetail->user_account_id = $transactionInit->user_account_id;
        $transDetail->payment_processor_response = $transaction->fullResponse;
        $transDetail->payment_gateway_form_id = $gateway->payment_gateway_form->id;
        $transDetail->payment_status = $transactionInit->payment_status;
        $transDetail->charge_ref_no = !empty($transaction->token) ? $transaction->token : $transaction->paymentIntentId;
        $transDetail->order_id = $transaction->order_id;
        $transDetail->error_msg = ($transactionInit->exceptionMessage != '' ? $transactionInit->exceptionMessage : $transactionInit->message);
        $transDetail->save();

    }

    /**
     * @param string $paymentIntentId
     * @return array|null
     */
    private function getIntentTypeFor(string $paymentIntentId) {

        $result = [];

        $transactionInit = TransactionInit::where('payment_intent_id', $paymentIntentId)->first();

        if($transactionInit != null) {
            $result['type'] = PaymentConfirmation::INTENT_FOR_CHARGE;
            $result['object'] = $transactionInit;
            return $result;
        }

        $creditCardAuthorization = CreditCardAuthorization::where('payment_intent_id', $paymentIntentId)->first();

        if($creditCardAuthorization != null) {
            $result['type'] = PaymentConfirmation::INTENT_FOR_AUTH;
            $result['object'] = $creditCardAuthorization;
            return $result;
        }

        return null;

    }

    private function insertAuthLog(CreditCardAuthorization $ccAuth, Transaction $transaction, UserPaymentGateway $userPaymentGateway, string $message) {
        try {
            $ccAuthDetail = new AuthorizationDetails();
            $ccAuthDetail->cc_auth_id = $ccAuth->id;
            $ccAuthDetail->user_account_id = $ccAuth->user_account_id;
            $ccAuthDetail->payment_processor_response = json_encode($transaction);
            $ccAuthDetail->payment_gateway_name = (new GateWay($userPaymentGateway->gateway))->name;
            $ccAuthDetail->payment_gateway_form_id = $userPaymentGateway->payment_gateway_form->id;
            $ccAuthDetail->payment_status = $transaction->status;
            $ccAuthDetail->amount = $ccAuth->hold_amount;
            $ccAuthDetail->charge_ref_no = !empty($transaction->token) ? $transaction->token : $transaction->paymentIntentId;
            $ccAuthDetail->order_id = $transaction->order_id;
            $ccAuthDetail->client_remarks = $message;
            $ccAuthDetail->error_msg = ($transaction->exceptionMessage != '' ? $transaction->exceptionMessage : $transaction->message);
            $ccAuthDetail->save();
            return $ccAuthDetail;
        } catch (\Exception $e) {
            Log::error("Error at insertAuthLog in CCReAuthJob",
                ['File'=>PaymentConfirmation::class, 'message'=>$e->getMessage(), 'stack'=>$e->getTraceAsString()]);
        }
    }

}
