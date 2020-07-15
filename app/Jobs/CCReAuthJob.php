<?php

namespace App\Jobs;

use App\BookingInfo;
use App\Events\Emails\EmailEvent;
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

class CCReAuthJob implements ShouldQueue
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
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        self::onQueue('reauth');
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

        $ccAuth = CreditCardAuthorization::whereIn('status', [0, 1, 7])
            ->where('next_due_date', '<=', Carbon::now()->toDateTimeString())
            ->where('attempts', '<', CreditCardAuthorization::TOTAL_ATTEMPTS)
            ->whereIn('type', [$CCAutoAuthType, $SDAutoAuthType])
            ->get();

//        Log::notice('CCReAuthJob', ['TotalRecord'=>$ccAuth->count()]);

        if(!$ccAuth->isEmpty()) {

            $this->upg = new PaymentGateways();

            /**
             * @var $cAuth CreditCardAuthorization
             */
            foreach($ccAuth as $cAuth) {

                try {
                    $authFailed = false; //default attempted auth not failed , true on auth failure
                    $continueFlag = false;
                    $bookingInfo = $cAuth->booking_info;

                    /**
                     * This mostly does not happen but on test server we delete bookings often which spits error on
                     * slack so to stop that this/below check is added.
                     */
                    if($bookingInfo == null) {
                        $cAuth->remarks = "BookingInfo was null";
                        $cAuth->status = CreditCardAuthorization::STATUS_FAILED;
                        $cAuth->save();
                        $this->bugTrace('1', $cAuth);
                        continue;
                    }

                    if(!$this->voidIfPropertyIsDisabled($bookingInfo, $cAuth)) {
                        $this->bugTrace('2', $cAuth);
                        continue;
                    }

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
                    $propertyInfo = $bookingInfo->property_info;

                    $userBookingSource = UserBookingSource::with('credit_card_validation_setting',
                        'security_damage_deposit_setting')->where([['user_account_id', $cAuth->user_account_id],
                        ['booking_source_form_id', $bookingInfo->bookingSourceForm->id],
                        ['property_info_id', GetPropertyInfoIdForBridgeSettings($propertyInfo)]])->first();


                    if (empty($userBookingSource)) {
                        Log::notice('User Booking Source not Active (Suggestions => Need to Refactor Business Logic for CCReAuth Jobs)',
                            ['BS_form_id' => $bookingInfo->bookingSourceForm->id, 'booking_info_id' => $bookingInfo->id,'File' =>__FILE__]);
                        $cAuth->next_due_date = Carbon::now()->addHours(2)->toDateTimeString();
                        $cAuth->save();
                        $this->bugTrace('3', $cAuth);
                        continue;
                    }

                    $userAuthSettings = CheckAuthReAuthEnabledOrDisabled($userBookingSource, ($cAuth->type == $SDAutoAuthType ? 'SD' : 'CC'));

                    // Filtering out security damage deposit auth, only they are performed even payments had been made.
                    if($cAuth->type != $SDAutoAuthType && $cAuth->type != $sDMA)
                        if($continueFlag) {
                            $this->voidAuthRecord($cAuth);
                            $this->bugTrace('4', $cAuth);
                            continue;
                        }

                    if($this->isCheckoutDatePassed($bookingInfo, $cAuth)) {
                        $this->bugTrace('5', $cAuth);
                        continue;
                    }

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
                        if (($userAuthSettings->status == false) || ($userAuthSettings->autoReauthorize == false)) {
                            $cAuth->update(['next_due_date' => null, 'status' => CreditCardAuthorization::STATUS_VOID ]);
                            $this->bugTrace('6', $cAuth);
                            unset($cAuth);
                            continue;
                        }
                    } else {
                        if ($userAuthSettings->status == false) {
                            $cAuth->update(['next_due_date' => null, 'status' => CreditCardAuthorization::STATUS_VOID ]);
                            $this->bugTrace('7', $cAuth);
                            unset($cAuth);
                            continue;
                        }
                    }

                    if($cAuth->ccinfo == null) {
                        $cAuth->status = CreditCardAuthorization::STATUS_FAILED;
                        $cAuth->save();
                        $t = new Transaction();
                        $t->order_id = '0';
                        $t->status = CreditCardAuthorization::STATUS_FAILED;
                        $t->exceptionMessage = "No Credit Card, record found";
                        $this->insertAuthLog($cAuth, $t, $gateway, $t->exceptionMessage);
                        $this->bugTrace('8', $cAuth);
                        continue;
                    }

                    if($shouldAuth) {

                        $card = new Card();
                        $card->amount = $cAuth->hold_amount;
                        $card->currency = $gwTransaction->currency_code;
                        $card->order_id = (int) round(microtime(true) * 1000);

                        PaymentGateways::addMetadataInformation($bookingInfo, $card, CCReAuthJob::class);

                        $_token = $cAuth->ccinfo->customer_object->token;
                        if($_token == '' || $_token == null) {
                            $this->bugTrace('9', $cAuth);
                            continue;
                        }

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
                                        $this->bugTrace('10', $cAuth);
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

                        } elseif($cAuth->attempts == CreditCardAuthorization::TOTAL_ATTEMPTS) {
                            $cAuth->status = 5; // Fail
                            $cAuth->next_due_date = null;
                            $message = 'Failed after reattempts';
                            $authFailed = true;

                        } elseif ($reAuthResponse->state == Transaction::STATE_REQUIRE_ACTION) {

                            $cAuth->payment_intent_id = $reAuthResponse->paymentIntentId;
                            $cAuth->status = CreditCardAuthorization::STATUS_WAITING_APPROVAL;
                            $cAuth->attempts = $cAuth->attempts +1;

                            $message = 'Guest needs to authenticate';

                            $reAuthResponse->status = CreditCardAuthorization::STATUS_WAITING_APPROVAL;
                            $reAuthResponse->token = $reAuthResponse->paymentIntentId;
                            $reAuthResponse->exceptionMessage = $message;

                            $reAuthResponse->amount = $card->amount;
                            $reAuthResponse->currency_code = $card->currency;

                            if($cAuth->type == $SDAutoAuthType || $cAuth->type == $sDMA)
                            {
                                //inform guest for 3DS charge authentication
                                event(new EmailEvent(config('db_const.emails.heads.sd_3ds_required.type'),$cAuth->id ));
                            } else {
                                //inform guest for 3DS charge authentication
                                event(new EmailEvent(config('db_const.emails.heads.auth_3ds_required.type'),$cAuth->id ));
                            }

                        } else {
                            $cAuth->next_due_date = (new Carbon($cAuth->next_due_date))->addHours($numAttempts + 1)->toDateTimeString();
                            $message = 'Will be reattempted at ' . $cAuth->next_due_date;
                            $cAuth->status = CreditCardAuthorization::STATUS_REATTEMPT;
                            $authFailed = true;

                        }
                        $cAuth->save();

                        $ccAuthDetail  =$this->insertAuthLog($cAuth, $reAuthResponse, $gateway, $message);

                        if($authFailed) {
                            Bookings::BA_reportInvalidCardForBDCChannel($bookingInfo);
                            /**
                             * SDD Auth | CC Auth Failed
                             */
                            if (($cAuth->type == $SDAutoAuthType) || ($cAuth->type == $sDMA))
                                event(new PMSPreferencesEvent($cAuth->userAccount, $cAuth->booking_info, 0, config('db_const.user_preferences.preferences.SECURITY_DEPOSIT_AUTH_CAPTURE_FAILED'), $cAuth->id));
                            else
                                event(new PMSPreferencesEvent($cAuth->userAccount, $cAuth->booking_info, 0, config('db_const.user_preferences.preferences.CREDIT_CARD_VALIDATION_AUTH_FAILED'), $cAuth->id));


                            if ($cAuth->decline_email_sent == 0) {

                                if ($cAuth->type == $this->paymentType->getAuthTypeCCValidation()) {
                                    /* Send Email on Auth Failed */
                                    event(
                                        new EmailEvent(
                                            config('db_const.emails.heads.credit_card_authorization_failed.type'),
                                            $ccAuthDetail->id,
                                            ['error_msg' => $message ]
                                        )
                                    );

                                    $cAuth->decline_email_sent = 1;
                                    $cAuth->save();

                                } else if ($cAuth->type == $this->paymentType->getAuthTypeSecurityDamageValidation()) {

                                    $amountWithSymbol = $gwTransaction->currency_code . ' ' . number_format($cAuth->hold_amount, 2);

                                    // To Both Client and Guest
                                    event(
                                        new EmailEvent(
                                            config('db_const.emails.heads.sd_auth_failed.type'),
                                            $cAuth->id,
                                            ['error_msg' => $message ]
                                        )
                                    );

                                    $cAuth->decline_email_sent = 1;
                                    $cAuth->save();

                                }
                            }
                        } else {
                            if ($cAuth->status == 1){
                                if (($cAuth->type == $SDAutoAuthType) || ($cAuth->type == $sDMA)) {
                                    event(new PMSPreferencesEvent($cAuth->userAccount, $cAuth->booking_info, 0, config('db_const.user_preferences.preferences.SECURITY_DEPOSIT_AUTH_CAPTURE_SUCCESS'), $cAuth->id));

                                    //send email to client for successful SD
                                    event(new EmailEvent(config('db_const.emails.heads.sd_authorization_successful.type'),$ccAuthDetail->id ));
                                }
                                else {
                                    event(new PMSPreferencesEvent($cAuth->userAccount, $cAuth->booking_info, 0, config('db_const.user_preferences.preferences.CREDIT_CARD_VALIDATION_AUTH_SUCCESS'), $cAuth->id));

                                    //send email to client for successful AUTH
                                    event(new EmailEvent(config('db_const.emails.heads.credit_card_authorization_successful.type'),$ccAuthDetail->id ));
                                }
                            }
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
            return $ccAuthDetail;
        } catch (\Exception $e) {
            Log::error($e->getMessage(),
                [
                    'File'=>CCReAuthJob::class,
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

    private function bugTrace($location, CreditCardAuthorization $cca) {
        //Log::notice('CCReAuth Line: ' . $location, ['Object' => $cca]);
    }

}
