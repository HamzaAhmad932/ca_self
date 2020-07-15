<?php

namespace App\Jobs\Runtime;

use App\Events\Emails\EmailEvent;
use App\Events\GuestMailFor3DSecureEvent;
use App\Repositories\Bookings\Bookings;
use App\System\PMS\BookingSources\BS_BookingCom;
use App\System\PMS\BookingSources\BS_Generic;
use App\System\PMS\exceptions\PmsExceptions;
use App\System\PMS\Models\Booking;
use App\System\PMS\Models\InvoiceItem;
use App\System\PMS\Models\PmsOptions;
use App\System\PMS\PMS;
use Exception;
use App\BookingInfo;
use App\UserAccount;
use App\PropertyInfo;
use App\CreditCardInfo;
use App\TransactionInit;
use App\Mail\GenericEmail;
use App\TransactionDetail;
use App\UserPaymentGateway;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Carbon;
use App\Events\PMSPreferencesEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\System\PaymentGateway\Models\Card;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\System\PaymentGateway\PaymentGateway;
use App\Repositories\Settings\PaymentTypeMeta;
use App\System\PaymentGateway\Models\Customer;
use App\System\PaymentGateway\Models\Transaction;
use App\Repositories\BookingSources\BookingSources;
use App\Repositories\PaymentGateways\PaymentGateways;
use App\Repositories\NotificationAlerts;
use App\System\PaymentGateway\Exceptions\GatewayException;

class BAChargeJobRuntime implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $customer;
    private $gateway;
    private $bookingInfo;
    /**
     * @var int
     */
    private $bookingInfoId;

    /**
     * Create a new job instance.
     *
     * @param int $bookingInfoId
     */
    public function __construct(int $bookingInfoId){
        self::onQueue('ba_charge');
        $this->bookingInfoId = $bookingInfoId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {

        try {

            $trans = TransactionInit::with('booking_info')->where('final_tick', 0)
                ->where('lets_process', 1)
                ->where('booking_info_id', $this->bookingInfoId)
                ->where('type', 'C')
                ->where('attempt', '<', TransactionInit::TOTAL_ATTEMPTS)
                ->where('payment_status', TransactionInit::PAYMENT_STATUS_WAITING_APPROVAL)
                ->where('due_date', '<=',Carbon::now()->toDateTimeString())
                ->where('in_processing', TransactionInit::TRANSACTION_AVAILABLE_TO_PROCESS)
                ->get();
            $msg = '';

            if (!$trans->isEmpty()) {
                foreach($trans as $tran) {
                    $userAccount = UserAccount::where('id', $tran->user_account_id)->get();
                    $detailArr = ['user_account_id'=> $tran->user_account_id, 'booking_info_id'=>$tran->booking_info_id , 'TransactionInit ID'=>$tran->id];

                    if($userAccount[0]->status == '1' or $userAccount[0]->status == '5') {
                        $tran->update(['in_processing' => TransactionInit::TRANSACTION_ADDED_IN_QUEUE_PROCESSING]);
                        $bookingInfo = $tran->booking_info;

                        $isVC = false;
                        if($bookingInfo->is_vc == 'VC')
                            $isVC = true;

                        $ccInfo = null;
                        if($isVC){
                            $ccInfo = CreditCardInfo::where('booking_info_id', $tran->booking_info_id)
                                ->where('is_vc', 1)
                                ->latest('id')
                                ->limit(1)
                                ->get();
                            $vcFlag = true;
                        }else{
                            $ccInfo = CreditCardInfo::where('booking_info_id', $tran->booking_info_id)
                                ->latest('id')
                                ->limit(1)
                                ->get();
                            $vcFlag = false;
                        }

                        // $gateway = UserPaymentGateway::where('user_account_id', $tran->user_account_id)->get();

                        $propertyInfo = PropertyInfo::where('pms_property_id', $bookingInfo->property_id)
                            ->where('pms_id', $bookingInfo->pms_id)
                            ->where('user_account_id', $tran->user_account_id)
                            ->get();

                        /************************
                         * Get UserPayment Gateway
                         */
                        $upg = new PaymentGateways();
                        $gateway = $upg->getPropertyPaymentGatewayFromProperty($propertyInfo[0]);

                        /************************
                         * End Get UserPayment Gateway
                         */

                        //UserPaymentGateway::where('user_account_id', $tran->user_account_id)->get();

                        $propertyStatus = isset($propertyInfo[0]->status) ? $propertyInfo[0]->status : false;

                        if($propertyStatus == true && isset($ccInfo[0]) && isset($gateway)) {

                            $bookingSourceRepo = new BookingSources();
                            $is_activeBookingSource = $bookingSourceRepo->isBookingSourceActive($propertyInfo[0],
                                $bookingSourceRepo::getBookingSourceFormIdByChannelCode($bookingInfo->pms_id, $bookingInfo->channel_code));

                            if( !$is_activeBookingSource ) {
                                $tran->update(['in_processing' => TransactionInit::TRANSACTION_AVAILABLE_TO_PROCESS]);
                                continue;
                            }

                            if(count($ccInfo) > 0 ) {
                                if(empty($ccInfo[0]->auth_token)) {

                                    $tran->attempt = 1;
                                    $tran->payment_status = TransactionInit::PAYMENT_STATUS_REATTEMPT;
                                    $tran->next_attempt_time = Carbon::now()->addMinutes(5)->toDateTimeString();
                                    $tran->save();

                                    $message = "Customer object not created yet.";
                                    $this->createTransactionDetailLog($tran, $ccInfo[0], $userAccount[0],
                                        $bookingInfo, $propertyInfo[0], $gateway, $message);
                                    $tran->update(['in_processing' => TransactionInit::TRANSACTION_AVAILABLE_TO_PROCESS]);
                                    continue;
                                }
                            }

                            $bookingFromPms = $this->fetch_Booking_Details($propertyInfo[0], $userAccount[0], $bookingInfo->pms_booking_id);

                            $paymentTypeMeta = new PaymentTypeMeta();

                            if($bookingFromPms != null && is_array($bookingFromPms) && count($bookingFromPms) > 0) {
                                /**
                                 * @var Booking $bookingFromPmsObject
                                 */
                                $bookingFromPmsObject = $bookingFromPms[0];

                                $chargeStatus = $bookingFromPmsObject->isValidToChargeByCheckingBalanceOnPMS($tran->price);

                                if ((!$chargeStatus['status']) && $chargeStatus['isRoundError'] == false && ($isVC == false)  && $tran->transaction_type != $paymentTypeMeta->getBookingCancellationAutoCollectionFull()) {

                                    try {
                                        $message = 'Aborting this transaction because its Paid (Partially Paid) on BookingAutomation.';
                                        // Void this Transaction.
                                        $tran->payment_status = TransactionInit::PAYMENT_STATUS_ABORTED;
                                        $tran->lets_process = 0;
                                        $tran->final_tick = 0;
                                        $tran->attempt = $tran->attempt + 1;
                                        $systemRemarks = $tran->system_remarks;
                                        $systemRemarks .= ' ' . $message;
                                        $tran->system_remarks = $systemRemarks;
                                        $tran->save();

                                        $transDetail = $this->createTransactionDetailLog($tran, $ccInfo[0], $userAccount[0],
                                            $bookingInfo, $propertyInfo[0], $gateway, $message);

                                        try {
                                            // Send Email to Client that This transaction is already charged.
                                            event(new EmailEvent(config('db_const.emails.heads.payment_aborted.type'), $transDetail->id));

                                            //create alert for the same to show notification
                                            //use common repo to create alert
                                            $notificationRepo = new NotificationAlerts($bookingInfo->user_id, $bookingInfo->user_account_id);
                                            $chat = $notificationRepo->create($bookingInfo->id, 0, 'payment_failed', $bookingInfo->pms_booking_id, 1);

                                        } catch (\Exception $e) {
                                            Log::error($e->getMessage(), ['File' => BAChargeJobRuntime::class, 'Stack' => $e->getTraceAsString(), 'Detail' => $detailArr]);
                                        }

                                        // Update Booking on BA that this amount is already charged.
                                        $_pms = new PMS($userAccount[0]);
                                        $_pmsOptions = new PmsOptions();
                                        $bookingToUpdateData = new Booking();

                                        $_pmsOptions->propertyID = $propertyInfo[0]->pms_property_id;
                                        $_pmsOptions->propertyKey = $propertyInfo[0]->property_key;
                                        $_pmsOptions->requestType = PmsOptions::REQUEST_TYPE_JSON;

                                        $bookingToUpdateData->id = $bookingInfo->pms_booking_id;
                                        $bookingToUpdateData->notes = $bookingInfo->notes . ' ' . $message;
                                        $bookingToUpdateData->propertyId = $propertyInfo[0]->pms_property_id;
                                        $_pms->update_booking($_pmsOptions, $bookingToUpdateData);

                                    } catch (PmsExceptions $e) {
                                        Log::error($e->getMessage(), ['pms_code' => $e->getPMSCode(), 'File' => BAChargeJobRuntime::class, 'Line' => __LINE__, 'Detail' => $detailArr]);
                                    } catch (Exception $e) {
                                        Log::error($e->getMessage(), ['File' => BAChargeJobRuntime::class, 'Stack' => $e->getTraceAsString(), 'Detail' => $detailArr]);
                                    }
                                    $tran->update(['in_processing' => TransactionInit::TRANSACTION_AVAILABLE_TO_PROCESS]);
                                    continue;
                                }

                            } else {
                                $tran->system_remarks = $bookingFromPms;
                                $this->reattempt($tran);
                                $tran->save();

                                $this->createTransactionDetailLog($tran, $ccInfo[0], $userAccount[0],
                                    $bookingInfo, $propertyInfo[0], $gateway, $bookingFromPms);
                                $tran->update(['in_processing' => TransactionInit::TRANSACTION_AVAILABLE_TO_PROCESS]);
                                continue;
                            }


                            $card = new Card();
                            $card->firstName = $ccInfo[0]->customer_object->first_name;
                            $card->lastName = $ccInfo[0]->customer_object->last_name;
                            $card->token = $ccInfo[0]->customer_object->token;
                            $card->amount = $chargeStatus['isRoundError'] == false ? $tran->price : $chargeStatus['amountToCharge'];
                            $card->currency = $propertyInfo[0]->currency_code;
                            $card->order_id = (int) round(microtime(true) * 1000);

                            PaymentGateways::addMetadataInformation($bookingInfo, $card, BAChargeJobRuntime::class);

                            $pg = new PaymentGateway();

                            /**
                             * @var $authTransactionObj Transaction
                             */
                            // $authTransactionObj = $ccInfo[0]->ccauth->transaction_obj;
                            $auths = $ccInfo[0]->ccauth;

                            $securityId = $paymentTypeMeta->getAuthTypeSecurityDamageValidation();

                            if($auths != null && isset($auths->token) && $auths->token != ''){
                                foreach($auths as $auth){
                                    if($auth->type != $securityId) {

                                        try {
                                            $cancelAuth = $pg->cancelAuthorization($auth->transaction_obj, $gateway);

                                        } catch (GatewayException $e) {
                                            Log::notice($e->getMessage(), ['File'=>BAChargeJobRuntime::class, 'Location' => "Cancel Authorization"]);
                                        }
                                    }
                                }
                            }
                            // if($authTransactionObj != null && isset($authTransactionObj->token) && $authTransactionObj->token != '')
                            // $cancelAuth = $pg->cancelAuthorization($authTransactionObj, $gateway);

                            $resp = new Transaction();

                            if($card->amount > 0) {
                                try {

                                    $resp = $pg->chargeWithCustomer($ccInfo[0]->customer_object, $card, $gateway);

                                    $tran->payment_intent_id = $resp->paymentIntentId;

                                } catch (GatewayException $e) {

                                    if($e->getCode() == PaymentGateway::ERROR_CODE_3D_SECURE) {

                                        $resp->state = Transaction::STATE_REQUIRE_ACTION;
                                        $resp->paymentIntentId = null;

                                    } else {

                                        report($e);
                                        $resp->exceptionMessage = $e->getDescription();
                                        $resp->order_id = $card->order_id;

                                        if ($e->getCode() == PaymentGateway::CODE_NETWORK_ERROR_RE_TRY_ABLE) {
                                            $tran->attempts_for_500 = 1;
                                            $tran->save();
                                            $tran->update(['in_processing' => TransactionInit::TRANSACTION_AVAILABLE_TO_PROCESS]);
                                            continue;
                                        }

                                        Bookings::BA_reportInvalidCardForBDCChannel($bookingInfo);

                                        $detailArr['Attempts'] = $tran->attempt;
                                        Log::notice($e->getMessage(), ['File' => BAChargeJobRuntime::class, 'Detail' => $detailArr]);

                                        /**
                                         * Reporting Invalid card to BookingAutomation only when its Credit Card and Booking Source is Booking.com
                                         */
                                        if ($bookingInfo->channel_code == BS_BookingCom::BA_CHANNEL_CODE && $bookingInfo->is_vc == BS_Generic::PS_CREDIT_CARD) {

                                            $bookingRepo = new Bookings($userAccount[0]->id);
                                            $bookingRepo->reportInvalidCard($bookingInfo, $userAccount[0], $propertyInfo[0]);

                                        }
                                    }

                                }

                            } else {
                                $resp->fullResponse = '';
                                $resp->status = true;
                                $resp->token = '';
                                $resp->order_id = $card->order_id;
                                $resp->message = "Amount Was zero, So not tried for charge";
                            }

                            $transDetail = new TransactionDetail();
                            $transDetail->transaction_init_id = $tran->id;
                            $transDetail->cc_info_id = $ccInfo[0]->id;
                            // $transDetail->user_id = $trans[0]->user_id;
                            $transDetail->user_account_id = $tran->user_account_id;
                            // $transDetail->name =
                            $transDetail->payment_processor_response = $resp->fullResponse;
                            $transDetail->payment_gateway_form_id = $gateway->payment_gateway_form->id;
                            $transDetail->payment_status = $resp->status;
                            $transDetail->charge_ref_no = $resp->token;
                            // $transDetail->client_remarks = $tran->client_remarks;
                            $transDetail->order_id = $resp->order_id;
                            $transDetail->error_msg = ($resp->exceptionMessage != '' ? $resp->exceptionMessage : $resp->message);
                            // turn processing OFF after charge

                            $sendSuceess = false;
                            $sendFaied = false;

                            if ($resp->status && $resp->state == Transaction::STATE_SUCCEEDED) {
                                $tran->lets_process = 0;
                                $tran->payment_status = TransactionInit::PAYMENT_STATUS_SUCCESS;
                                $tran->charge_ref_no = $resp->token;
                                $tran->last_success_trans_obj = $resp;
                                $msg = '';
                                $tran->system_remarks = $chargeStatus['isRoundError'] == false ? $tran->system_remarks : $tran->system_remarks . ' .Charged with difference of round error.';
                                $sendSuceess = true;

                            }
//                            elseif($resp->state == Transaction::STATE_REQUIRE_ACTION) {
//
//                                $message = 'Guest needs to authenticate';
//                                $transDetail->error_msg = $message;
//                                $transDetail->payment_status = TransactionInit::PAYMENT_STATUS_WAITING_APPROVAL;
//
//                                $resp->amount = $card->amount;
//                                $resp->currency_code = $card->currency;
//
//                                $tran->last_success_trans_obj = $resp;
//                                $tran->attempt = $tran->attempt + 1;
//                                $tran->payment_status = TransactionInit::PAYMENT_STATUS_WAITING_APPROVAL;
//                                $tran->payment_intent_id = $resp->paymentIntentId;
//
//                                event(new GuestMailFor3DSecureEvent($resp, $propertyInfo[0], $userAccount[0],
//                                    $bookingInfo, GuestMailFor3DSecureEvent::MAIL_FOR_3DS_CHARGE, $tran, null));
//
//
//                            }

                            $transDetail->save();
                            $tran->save();

                            if($sendSuceess) {

                                /**
                                 * Below code block send email to client on successful charge.
                                 * But we are also sending separate email on successful charge of adjustment entry in case of cancellation.
                                 * So to avoid duplication we are checking if transaction is not of cancellation collection.
                                 */
                                if($tran->transaction_type != $paymentTypeMeta->getBookingCancellationAutoCollectionFull()) {

                                    try {
                                        event(new EmailEvent(config('db_const.emails.heads.payment_successful.type'), $transDetail->id));
                                    } catch (\Exception $e) {
                                        Log::error($e->getMessage(), ['File' => BAChargeJobRuntime::class, 'Stack' => $e->getTraceAsString(), 'Detail' => $detailArr]);
                                    }

                                }

                                try {

                                    if($paymentTypeMeta->getBookingCancellationAutoCollectionFull() == $tran->transaction_type) {
                                        $preferenceFormId = config('db_const.user_preferences.preferences.PAYMENT_COLLECTION_ON_CANCELLATION');
                                        event(new EmailEvent(config('db_const.emails.heads.payment_collected_for_cancelled_booking.type'), $transDetail->id));

                                    } else {
                                        $preferenceFormId = config('db_const.user_preferences.preferences.PAYMENT_SUCCESS');
                                    }

                                    event(new PMSPreferencesEvent($userAccount[0], $bookingInfo, $tran->id, $preferenceFormId));

                                } catch (\Exception $e) {
                                    Log::error($e->getMessage(), ['File'=>BAChargeJobRuntime::class, 'Stack'=>$e->getTraceAsString(), 'Detail'=> $detailArr]);
                                }

                            }

                            $tran->attempt = 1;
                            $transDetail->save();
                            $tran->save();

                        } else {
                            // $tran->lets_process = 0;
                            // $tran->attempt = $tran->attempt + 1;
                            $tran->payment_status = TransactionInit::PAYMENT_STATUS_PAUSED;
                            $tran->save();
                            $message = "Paused due to property disabled";
                            $this->createTransactionDetailLog($tran, $ccInfo[0], $userAccount[0],
                                $bookingInfo, $propertyInfo[0], $gateway, $message);
                        }
                    }
                    $tran->update(['in_processing' => TransactionInit::TRANSACTION_AVAILABLE_TO_PROCESS]);
                }
            }


        } catch (Exception $e) {
            Log::error($e->getMessage(), ['File'=>BAChargeJobRuntime::class, 'Stack'=>$e->getTraceAsString()]);
        }
    }

    private function reattempt(TransactionInit &$tran){
        $user = $tran->booking_info;
        $tran->payment_status = TransactionInit::PAYMENT_STATUS_REATTEMPT;
        $oldAttemptTime = $tran->next_attempt_time;
        $nextAttemptTime = new Carbon($oldAttemptTime);
        $tran->next_attempt_time = $nextAttemptTime->addHour(1);
        $tran->attempt = 1;
    }

    private function fetch_Booking_Details(PropertyInfo $propertyInfo, UserAccount $userAccount, $bookingId){


        try {

            $pms = new PMS($userAccount);
            $pmsOptions = new PmsOptions();
            $pmsOptions->requestType = PmsOptions::REQUEST_TYPE_XML;
            $pmsOptions->includeInfoItems = true;
            $pmsOptions->includeCard = true;
            $pmsOptions->includeInvoice = true;
//            $pmsOptions->propertyID = $this->propertyId;
            $pmsOptions->bookingID = $bookingId;
            $pmsOptions->propertyKey = $propertyInfo->property_key;

            $result = $pms->fetch_Booking_Details($pmsOptions);

            if(count($result) == 0)
                return [];

            /*
             * NOTE: calling again BookingAutomation API with JSON type request to fetch
             * infoItems, which are not present in XML type request
             */
            $pms = new PMS($userAccount);
            $pmsOptions = new PmsOptions();
            $pmsOptions->includeInfoItems = true;
            $pmsOptions->includeCard = true;
            $pmsOptions->includeInvoice = true;
            $pmsOptions->bookingID = $bookingId;
            $pmsOptions->propertyKey = $propertyInfo->property_key;
            $pmsOptions->requestType = PmsOptions::REQUEST_TYPE_JSON;
            $pmsOptions->propertyID = $propertyInfo->pms_property_id;
            $resultFromJsonRequest = $pms->fetch_Booking_Details($pmsOptions);

            for($j = 0; $j < count($resultFromJsonRequest); $j++) {
                for($x = 0; $x < count($result); $x++) {
                    if($resultFromJsonRequest[$j]->id == $result[$x]->id) {
                        $result[$x]->infoItems = $resultFromJsonRequest[$j]->infoItems;
                        $result[$x]->currencyCode = $resultFromJsonRequest[$j]->currencyCode;
                        continue;
                    }
                }
            }


            return $result;

        } catch (PmsExceptions $e) {
            Log::error($e->getMessage(), ['File'=>BAChargeJobRuntime::class, 'BookingId' => $bookingId, 'Function'=>__FUNCTION__, 'pms_code' => $e->getPMSCode()]);
            return $e->getMessage();
        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['File'=>BAChargeJobRuntime::class, 'BookingId' => $bookingId, 'Function'=>__FUNCTION__, 'Stack'=>$e->getTraceAsString()]);
            return $e->getMessage();
        }
    }

    /**
     * return 'true' if charged or overcharged
     * return 'false' if it is not charged of $amountToCharge is less than uncharged invoice items.
     *
     * @param Booking $booking
     * @param $amountToCharge
     * @return bool
     */
    public function detectChargedOrOvercharged(Booking $booking, $amountToCharge) {

        $toCharge = 0;
        $charged = 0;

        if($booking->invoice != null && is_array($booking->invoice) && count($booking->invoice) > 0) {

            /**
             * @var $item InvoiceItem
             */
            foreach ($booking->invoice as $item) {
                if( ((int) $item->type) < 200) {
                    $toCharge += ((float)$item->price) * ((int)$item->quantity);

                } elseif (((int) $item->type) >= 200) {
                    $charged += ((float)$item->price) * abs(((int)$item->quantity));
                }
            }

            /**
             * amountToCharge   toCharge    charged     =   result
             * 400              400         200         =   true
             * 400              400         400         =   true
             * 400              400         500         =   true
             * 400              500         50          =   false
             *
             * 400              400         0           =   false
             * 400              500         0           =   false
             */

            /**
             * Some thing has been charged by client already.
             */
            if($charged > 0) {
                $flag = abs($toCharge - $charged) < $amountToCharge;
                Log::notice("detectChargedOrOvercharged", [
                    'Something has been charged: ' => $flag,
                    'toCharge: ' => $toCharge,
                    'charged: ' => $charged,
                    'toCharge - charged = ' => abs($toCharge - $charged),
                    'amountToCharge: ' => $amountToCharge,
                    'aLgo: ' => 'abs($toCharge - $charged) < $amountToCharge;',
                    'booking id' => $booking->id
                ]);
                return $flag;
            }

            $flag = $toCharge < $amountToCharge;
            Log::notice("detectChargedOrOvercharged", [
                'Something has NOT been charged: ' => $flag,
                'toCharge: ' => $toCharge,
                'charged: ' => $charged,
                'amountToCharge: ' => $amountToCharge,
                'aLgo: ' => '$toCharge < $amountToCharge;',
                'booking id' => $booking->id
            ]);
            return $flag;

        } else {
            $flag = $amountToCharge > $booking->price;
            Log::notice("detectChargedOrOvercharged", [
                'invoice was null or empty ' => true,
                'flag: ' => $flag,
                'amountToCharge: ' => $amountToCharge,
                'booking price: ' => $booking->price,
                'booking id' => $booking->id
            ]);
            return $flag;
        }

    }

    private function createTransactionDetailLog(TransactionInit $tran, CreditCardInfo $ccInfo, UserAccount $userAccount, BookingInfo $bookingInfo, PropertyInfo $propertyInfo, UserPaymentGateway $gateway, string $exceptionMessage) {
        // Add log in Transaction Detail.
        $transDetail = new TransactionDetail();
        $transDetail->transaction_init_id = $tran->id;
        $transDetail->cc_info_id = $ccInfo->id;
        $transDetail->user_account_id = $tran->user_account_id;
        $br = new Bookings($userAccount->id);
        $_t = new Transaction();
        $_t->currency_code = $br->getCurrencyCode($bookingInfo, $propertyInfo);
        $transDetail->payment_processor_response = json_encode($_t);
        $transDetail->payment_gateway_form_id = $gateway->payment_gateway_form->id;
        $transDetail->payment_status = TransactionInit::PAYMENT_STATUS_VOID;
        $transDetail->charge_ref_no = '';
        $transDetail->order_id = 0;
        $transDetail->error_msg = $exceptionMessage == null ? "" : $exceptionMessage;
        $transDetail->save();

        return $transDetail;
    }

}
