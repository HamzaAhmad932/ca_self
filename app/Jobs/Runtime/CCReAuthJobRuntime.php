<?php

namespace App\Jobs\Runtime;

use App\BookingInfo;
use App\Events\GuestMailFor3DSecureEvent;
use App\Events\PMSPreferencesEvent;
use App\Mail\GenericEmail;
use App\PropertyInfo;
use App\Repositories\Bookings\Bookings;
use App\UserAccount;
use App\UserBookingSource;
use App\UserPaymentGateway;
use App\AuthorizationDetails;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Carbon;
use App\CreditCardAuthorization;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\System\PaymentGateway\Models\Card;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\System\PaymentGateway\Models\GateWay;
use App\System\PaymentGateway\PaymentGateway;
use App\Repositories\Settings\PaymentTypeMeta;
use App\System\PaymentGateway\Models\Transaction;
use App\Repositories\Settings\CreditCardValidation;
use App\Repositories\PaymentGateways\PaymentGateways;
use App\System\PaymentGateway\Exceptions\GatewayException;
use Illuminate\Support\Facades\Mail;

class CCReAuthJobRuntime implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var PaymentTypeMeta
     */
    private $paymentType = null;

    /**
     * @var PaymentGateways
     */
    private $upg = null;
    /**
     * @var int
     */
    private $bookingInfoId;

    /**
     * Create a new job instance.
     *
     * @param int $bookingInfoId
     */
    public function __construct(int $bookingInfoId)
    {
        self::onQueue('reauth');
        $this->bookingInfoId = $bookingInfoId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Status: 0=Pending, 1=Attempted, 3=Void, 4=Manual-Pending, 5=Fail, 7=Reattempt
        $this->paymentType = new PaymentTypeMeta();
        $SDAutoAuthType = $this->paymentType->getSecurityDepositAutoAuthorize();
        $CCAutoAuthType = $this->paymentType->getCreditCardAutoAuthorize();
        $ccAuth = CreditCardAuthorization::where('status', CreditCardAuthorization::STATUS_WAITING_APPROVAL)
            ->where('booking_info_id', $this->bookingInfoId)
            ->where('next_due_date', '<=', Carbon::now()->toDateTimeString())
            ->where('attempts', '<', CreditCardAuthorization::TOTAL_ATTEMPTS)
            ->whereIn('type', [$CCAutoAuthType, $SDAutoAuthType])
            ->get();

        if(!$ccAuth->isEmpty()) {

            $authFailed = false; //default attempted auth not failed , true on auth failure
            $this->upg = new PaymentGateways();

            /**
             * @var $cAuth CreditCardAuthorization
             */
            foreach($ccAuth as $cAuth) {

                try {

                    $continueFlag = false;
                    $bookingInfo = $cAuth->booking_info;

                    if(!$this->voidIfPropertyIsDisabled($bookingInfo, $cAuth))
                        continue;

                    if ($bookingInfo) {
                        $transactions = $bookingInfo->transaction_init;
                        if (!empty($transactions)) {
                            foreach ($transactions as $trans) {
                                if ($trans->payment_status == 1) {
                                    $continueFlag = true;
                                }
                            }
                        }
                        unset($transactions);
                    }
                    $sDMA = $this->paymentType->getSecurityDepositManualAuthorize();

                    //Code for check Auth Re-auth enabled disabled
                    $propertyInfo = $bookingInfo->property_info->where('user_account_id', $cAuth->user_account_id)->first();

                    $userBookingSource = UserBookingSource::with('credit_card_validation_setting',
                        'security_damage_deposit_setting')->where([['user_account_id', $cAuth->user_account_id],
                        ['booking_source_form_id', $bookingInfo->bookingSourceForm->id],
                        ['property_info_id', GetPropertyInfoIdForBridgeSettings($propertyInfo)]])->first();

                    if (empty($userBookingSource)) {
                        Log::notice('User Booking Source not Active (Suggestions => Need to Refactor Business Logic for CCReAuth Jobs)',
                            ['BS_form_id' => $bookingInfo->bookingSourceForm->id, 'booking_info_id' => $bookingInfo->id, 'File' =>__FILE__]);
                        $cAuth->next_due_date = Carbon::now()->addHours(2)->toDateTimeString();
                        $cAuth->save();
                        continue;
                    }
                    $userAuthSettings = CheckAuthReAuthEnabledOrDisabled($userBookingSource, ($cAuth->type == $SDAutoAuthType ? 'SD' : 'CC'));

                    // Filtering out security damage deposit auth, only they are performed even payments had been made.
                    if($cAuth->type != $SDAutoAuthType && $cAuth->type != $sDMA)
                        if($continueFlag) {
                            $this->voidAuthRecord($cAuth);
                            continue;
                        }

                    if($this->isCheckoutDatePassed($bookingInfo, $cAuth))
                        continue;

                    /**
                     * @var $gwTransaction Transaction
                     */
                    $gwTransaction = $cAuth->transaction_obj;
                    $shouldAutoReAuth = $this->shouldAutoReAuth($cAuth);
                    $isAuthCancelable = $this->isAuthCancelable($cAuth, $gwTransaction, $shouldAutoReAuth);
                    $shouldAuth = $this->shouldAuth($cAuth, $gwTransaction, $shouldAutoReAuth);


                    /**
                     * @var $gateway UserPaymentGateway
                     */
                    $gateway = $this->upg->getPropertyPaymentGatewayFromBooking($bookingInfo);
                    if($isAuthCancelable) {
                        $this->cancelAuthorization($cAuth, $gwTransaction, $gateway);
                        //Conditions for check Auth and Re-auth enabled disabled
                        if ((filter_var($userAuthSettings->status , FILTER_VALIDATE_BOOLEAN)  == false)|| (filter_var($userAuthSettings->autoReauthorize , FILTER_VALIDATE_BOOLEAN) == false)){
                            $cAuth->update(['next_due_date' => null, 'status' => CreditCardAuthorization::STATUS_VOID ]);
                            unset($cAuth);
                            continue;
                        }
                    } else {
                        if ((filter_var($userAuthSettings->status , FILTER_VALIDATE_BOOLEAN)  == false)) {
                            $cAuth->update(['next_due_date' => null, 'status' => CreditCardAuthorization::STATUS_VOID ]);
                            unset($cAuth);
                            continue;
                        }
                    }

                    if($shouldAuth) {

                        $card = new Card();
                        $card->amount = $cAuth->hold_amount;
                        $card->currency = $gwTransaction->currency_code;
                        $card->order_id = (int) round(microtime(true) * 1000);

                        PaymentGateways::addMetadataInformation($bookingInfo, $card, CCReAuthJobRuntime::class);

                        $_token = $cAuth->ccinfo->customer_object->token;
                        if($_token == '' || $_token == null)
                            continue;

                        $reAuthResponse = new Transaction();
                        $reAuthResponse->order_id = $card->order_id;

                        try {
                            $pg = new PaymentGateway();
                            $reAuthResponse = $pg->authorizeWithCustomer($cAuth->ccinfo->customer_object, $card, $gateway);
                        } catch (GatewayException $e) {

                            if ($e->getCode() == PaymentGateway::ERROR_CODE_3D_SECURE) {
                                $reAuthResponse->state = Transaction::STATE_REQUIRE_ACTION;
                                $reAuthResponse->paymentIntentId = null;

                            } else {

                                report($e);

                                Log::error("Gateway Exception: " . $e->getDescription(), [
                                    'File' => __FILE__,
                                    'Function' => __FUNCTION__,
                                    'CCAUthId' => $cAuth->id]);

                                try {
                                    if ($e->getCode() == PaymentGateway::CODE_NETWORK_ERROR_RE_TRY_ABLE) {
                                        $cAuth->attempts_for_500 += 1;
                                        $cAuth->save();

                                        if ($cAuth->attempts_for_500 == 4) {

                                            Mail::to(config('db_const.app_developers.emails.to'))->cc(
                                                config('db_const.app_developers.emails.cc'))->send(new GenericEmail(array(
                                                'subject' => 'Network Failure',
                                                'noReply' => true,
                                                'markdown' => 'emails.network_error_email_markdown',
                                                'message' => $e->getDescription(),
                                                'any_json_object' => json_encode($cAuth, JSON_PRETTY_PRINT))));
                                        }
                                        continue;
                                    } else {
                                        Bookings::BA_reportInvalidCardForBDCChannel($bookingInfo);
                                    }
                                } catch (\Exception $e) {
                                    $message = $e->getMessage();
                                    Log::error($e->getMessage(), [
                                        'File' => __FILE__,
                                        'function' => __FUNCTION__,
                                        'CCAUthId' => $cAuth->id,
                                        'stack' => $e->getTraceAsString()
                                    ]);
                                }
                            }
                        }

                        $numAttempts = $cAuth->attempts;
                        $cAuth->attempts = $numAttempts + 1;
                        if(!isset($message))
                            $message = '';

                        if($reAuthResponse->status && ($reAuthResponse->state == Transaction::STATE_SUCCEEDED || $reAuthResponse->state == Transaction::STATE_REQUIRE_CAPTURE)) {

                            $cAuth->token = $reAuthResponse->token;
                            $cAuth->transaction_obj = json_encode($reAuthResponse);

                            if ($shouldAutoReAuth) {
                                $dueDate = $cAuth->next_due_date;
                                $dateFinder = new Carbon($dueDate);
                                $nextDueDate = $dateFinder->addDays(CreditCardValidation::$autoReauthorizeDays);
                                $cAuth->next_due_date = $nextDueDate;

                            } else {
                                $cAuth->next_due_date = null;
                            }

                            $cAuth->status = 1;
                            $message = 'Successfully Authorized';

                        }

                        $cAuth->save();

                        $this->insertAuthLog($cAuth, $reAuthResponse, $gateway, $message);


                        if ($cAuth->status == 1){
                            if (($cAuth->type == $SDAutoAuthType) || ($cAuth->type == $sDMA))
                                event(new PMSPreferencesEvent($cAuth->userAccount, $cAuth->booking_info, 0, config('db_const.user_preferences.preferences.SECURITY_DEPOSIT_AUTH_CAPTURE_SUCCESS'), $cAuth->id));
                            else
                                event(new PMSPreferencesEvent($cAuth->userAccount, $cAuth->booking_info, 0, config('db_const.user_preferences.preferences.CREDIT_CARD_VALIDATION_AUTH_SUCCESS'), $cAuth->id));
                        }

                    }
                } catch (\Exception $e) {
                    Log::error("CCReAuthJob: General Exception: " . $e->getMessage(), [
                        'File' => __FILE__,
                        'Function' => __FUNCTION__,
                        'CCAUthId' => $cAuth->id,
                        'Stack' => $e->getTraceAsString()
                    ]);
                }

            }
        }

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
            $ccAuthDetail->charge_ref_no = $transaction->token == null ? '' : $transaction->token;
            $ccAuthDetail->order_id = $transaction->order_id;
            $ccAuthDetail->client_remarks = $message;
            $ccAuthDetail->error_msg = ($transaction->exceptionMessage != '' ? $transaction->exceptionMessage : $transaction->message);
            $ccAuthDetail->save();
        } catch (\Exception $e) {
            Log::error($e->getMessage(),
                [
                    'File'=>CCReAuthJobRuntime::class,
                    'Function' => __FUNCTION__,
                    'Stack'=>$e->getTraceAsString()
                ]);
        }
    }

    private function shouldAutoReAuth(CreditCardAuthorization $cAuth) {
        return $cAuth->is_auto_re_auth == 1;
    }

    private function isAuthCancelable(CreditCardAuthorization $cAuth, Transaction $gwTransaction, bool $shouldAutoReAuth = false) {
        return $gwTransaction != null && $gwTransaction->token != null && $gwTransaction->token != '' && $cAuth->token != null && $shouldAutoReAuth;
    }

    private function shouldAuth(CreditCardAuthorization $cAuth, Transaction $gwTransaction, bool $shouldAutoReAuth = false) {
        return $shouldAutoReAuth || (($gwTransaction != null && ($gwTransaction->token == '' || $gwTransaction->token == null)) && ($cAuth->token == null || $cAuth->token == ''));
    }

    private function cancelAuthorization(CreditCardAuthorization $cAuth, Transaction $gwTransaction, UserPaymentGateway $gateway) {
        try {

            $pg = new PaymentGateway();
            $cancellationResponse = $pg->cancelAuthorization($gwTransaction, $gateway);
            $this->insertAuthLog($cAuth, $cancellationResponse, $gateway, 'Canceled Authorization, for ReAuthorization.');

            $SDAutoAuthType = $this->paymentType->getSecurityDepositAutoAuthorize();
            $sDMA = $this->paymentType->getSecurityDepositManualAuthorize();
            /**
             * SD Auth Refund Success | Failed
             */
            if ((($cAuth->type == $SDAutoAuthType) || ($cAuth->type == $sDMA)) && $cancellationResponse->status)
                event(new PMSPreferencesEvent($cAuth->userAccount, $cAuth->booking_info, 0, config('db_const.user_preferences.preferences.SECURITY_DEPOSIT_REFUND_SUCCESS'), $cAuth->id));
            else if ((($cAuth->type == $SDAutoAuthType) || ($cAuth->type == $sDMA)) && !$cancellationResponse->status)
                event(new PMSPreferencesEvent($cAuth->userAccount, $cAuth->booking_info, 0, config('db_const.user_preferences.preferences.SECURITY_DEPOSIT_REFUND_FAILED'), $cAuth->id));

        } catch (GatewayException $e) {
            Log::error($e->getMessage(), [
                'File' => __FILE__,
                'Function' => __FUNCTION__,
                'CCAUthId' => $cAuth->id]);
        }
    }

    /**
     * If Checkout Date has passed then it cancels authorization and voids it
     * @param BookingInfo $bookingInfo
     * @param CreditCardAuthorization $cAuth
     * @return bool
     */
    private function isCheckoutDatePassed(BookingInfo $bookingInfo, CreditCardAuthorization $cAuth) {

        $checkoutTime = Carbon::parse($bookingInfo->check_out_date, 'GMT');

        if($checkoutTime->isPast()) {

            if($this->isAuthCancelable($cAuth, $cAuth->transaction_obj, $this->shouldAutoReAuth($cAuth))) {
                $userPaymentGateway = $this->upg->getPropertyPaymentGatewayFromBooking($bookingInfo);
                $this->cancelAuthorization($cAuth, $cAuth->transaction_obj, $userPaymentGateway);
            }

            $this->voidAuthRecord($cAuth);

            return true;
        }

        return false;
    }

    private function voidAuthRecord(CreditCardAuthorization $cAuth) {
        $cAuth->status = CreditCardAuthorization::STATUS_VOID;
        $cAuth->next_due_date = null;
        $cAuth->attempts = $cAuth->attempts + 1;
        $cAuth->save();
    }

    private function voidIfPropertyIsDisabled(BookingInfo $bookingInfo, CreditCardAuthorization $cAuth) {

        try {

            $propertyInfo = PropertyInfo::where('pms_property_id', $bookingInfo->property_id)
                ->where('pms_id', $bookingInfo->pms_id)
                ->where('user_account_id', $bookingInfo->user_account_id)
                ->first();

            if ($propertyInfo->status == 0) {

                //$this->voidAuthRecord($cAuth);
                $cAuth->status = CreditCardAuthorization::STATUS_PAUSED;
                $cAuth->save();
                $message = 'Paused due to property disabled';
                $userPaymentGateway = $this->upg->getPropertyPaymentGatewayFromBooking($bookingInfo);
                $gwTransaction = $cAuth->transaction_obj;

                $this->insertAuthLog($cAuth, $gwTransaction, $userPaymentGateway, $message);
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

}
