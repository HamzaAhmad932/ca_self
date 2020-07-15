<?php

namespace App\Listeners;

use App\BookingInfoDetail;
use App\Events\Emails\EmailEvent;
use App\Events\GuestMailFor3DSecureEvent;
use App\Events\SendEmailEvent;
use App\GroupBookingOnHold;
use App\Jobs\BANewBookingJobNew;
use App\PropertyInfo;
use App\Repositories\Settings\ClientNotifySettings;
use App\Services\PropertySettings;
use App\System\PaymentGateway\Models\Customer;
use App\System\PMS\exceptions\PmsExceptions;
use App\System\PMS\Models\Booking;
use App\System\PMS\Models\PmsOptions;
use App\System\PMS\PMS;
use App\TransactionDetail;
use App\User;
use App\BookingInfo;
use NumberFormatter;
use App\CreditCardInfo;
use App\TransactionInit;
use App\BookingSourceForm;
use App\Mail\GenericEmail;
use App\AuthorizationDetails;
use Illuminate\Bus\Queueable;
use App\CreditCardAuthorization;
use App\Events\PaymentAttemptEvent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Events\TransactionInitEvent;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\InteractsWithQueue;
use App\System\PaymentGateway\Models\Card;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\System\PaymentGateway\PaymentGateway;
use App\System\PMS\BookingSources\BS_Generic;
use App\Repositories\Settings\PaymentSettings;
use App\Repositories\Settings\PaymentTypeMeta;
use App\System\PaymentGateway\Models\Transaction;
use App\Repositories\Settings\PaymentSettingsOptions;
use App\System\PaymentGateway\Exceptions\GatewayException;
use App\Events\PMSPreferencesEvent;
use App\Repositories\Bookings\Bookings;
use Illuminate\Support\Carbon;
use \DateTime;
use \DateTimeZone;

class TransactionInitListener implements ShouldQueue {

    use Queueable;

    public  $tries = 1;

    /**
     * @var TransactionInitEvent
     */
    private $event;

    /**
     * @var PaymentTypeMeta
     */
    private $pivotTable;

    /**
     * @var PaymentGateway
     */
    private $paymentGateway;

    private $userPaymentSettings = null;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct() {
    }

    /**
     * Handle the event.
     *
     * @param  TransactionInitEvent  $event
     * @return void
     */
    public function handle(TransactionInitEvent $event) {

        $this->event = $event;
        $this->pivotTable = resolve(PaymentTypeMeta::class);
        $this->paymentGateway = new PaymentGateWay();

        $isGroupBooking = $event->booking->isGroupBooking();

        try {

            if($this->event->bookingInfoNewObject->total_amount > 0) {

                switch ($this->event->typeOfPaymentSource) {

                    case BS_Generic::PS_CREDIT_CARD:
                        if($isGroupBooking)
                            $this->case_credit_card_group_booking();
                        else
                            $this->case_credit_card_normal();
                        break;

                    case BS_Generic::PS_VIRTUAL_CARD:
                        if($isGroupBooking)
                            $this->case_virtual_card_group_booking();
                        else
                            $this->handleVirtualCard();
                        break;

                    case BS_Generic::PS_BANK_TRANSFER:
                        $this->case_bank_transfer_normal();
                        break;
                }

            }

            $this->dispatch_any_group_booking_onHold();

        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['File'=>TransactionInit::class, 'Stack'=>$e->getTraceAsString()]);
        }
    }


    /**
     * Checking for any group bookings on hold. And if any then Dispatch them
     */
    private function dispatch_any_group_booking_onHold() {

        try {

            if ($this->event->booking->isMasterBooking()) {

                $groupBookings = GroupBookingOnHold::where('user_account_id', $this->event->userAccount->id)
                    ->where('master_id', $this->event->bookingInfoNewObject->pms_booking_id)
                    ->get();

                /**
                 * @var $gBooking GroupBookingOnHold
                 */
                foreach ($groupBookings as $gBooking) {
                    BANewBookingJobNew::
                    dispatch($this->event->userAccount, $gBooking->pms_booking_id, $gBooking->channel_code, $gBooking->pms_property_id,
                        $gBooking->booking_status, $gBooking->token, $gBooking->cvv)
                        ->delay(now()->addMinutes(1))
                        ->onQueue('ba_new_bookings');

                    $gBooking->delete();
                }

            }
        } catch (\Exception $e) {
            Log::error($e->getMessage(), [
                'File' => TransactionInit::class,
                'Function' => __FUNCTION__,
                'booking_id' => $this->event->bookingInfoNewObject->id,
                'master_id' => $this->event->bookingInfoNewObject->master_id,
                'Stack' => $e->getTraceAsString()]);
        }
    }

    private function case_bank_transfer_normal() {

        if(!empty($this->event->cc_info)) {
            $this->caseBankDraft($this->event->cc_info->id);
            event(new PMSPreferencesEvent($this->event->userAccount, $this->event->bookingInfoNewObject,0, config('db_const.user_preferences.preferences.BOOKINGS_THAT_WILL_NOT_BE_CHARGED') ));
            return;
        }
    }

    private function case_credit_card_normal() {


        if($this->event->customer_object['status']) {
            $this->caseCreditCard($this->event->customer_object['customer'], $this->event->customer_object['cc_info_id'], $this->event->customer_object['cc_info']);

        } else {

            $exceptionMsg = $this->event->customer_object['error_message'];

            $reason = key_exists('reason', $this->event->customer_object) ? $this->event->customer_object['reason'] : 3; // 3 means some other reason

            if (!empty($this->event->cc_info)) {
                $this->caseCreditCard_WithOutCustomer($this->event->cc_info->id);
                $transactionDetails = $this->getSettings();

                if (!empty($transactionDetails['creditCardValidation'])
                    || !empty($transactionDetails['securityDeposit'])
                    || !empty($transactionDetails['paymentSchedule'])) {


                    if (empty($this->event->bookingInfoNewObject->maintenance)
                        && $this->event->bookingInfoNewObject->is_process_able === BookingInfo::PROCESSABLE) {

                        if($reason == 1) { // 1 means missing credit card reason
                            event(new EmailEvent(config('db_const.emails.heads.credit_card_missing.type'), $this->event->cc_info->id, [ 'exceptionMsg' => $exceptionMsg ]));
                        } else {
                            event(new EmailEvent(config('db_const.emails.heads.credit_card_invalid.type'), $this->event->cc_info->id, [ 'exceptionMsg' => $exceptionMsg ] ));
                        }

//                    elseif ($reason == 2) // 2 means payment gateway error
//                        {
//                            //inform client
//                        event(new SendEmailNewEvent(config('db_const.emails.heads.credit_card_not_added_payment_gateway_error.type'), $this->event->bookingInfoNewObject->id, [ 'exceptionMsg' => $exceptionMsg ] ));
//                    }


                        event(new PMSPreferencesEvent(
                            $this->event->userAccount,
                            $this->event->bookingInfoNewObject,
                            0,
                            config('db_const.user_preferences.preferences.UNAVAILABLE_CARD_DETAILS')
                        ));

                    } else {
                        $this->updateBookingOnPMSToGetTokenInModify();

                    }
                }
            }
        }
    }

    private function case_credit_card_group_booking() {

        $result = $this->get_cc_info_for_group_booking();

        if($result['status']) {
            $this->caseCreditCard($result['customer'], $result['cc_info_id'], $result['cc_info']);

        } elseif(key_exists('cc_info_id', $result)) {
            $this->caseCreditCard_WithOutCustomer($result['cc_info_id']);

        } else {
            Log::error('No record created for group booking: ' . $this->event->bookingInfoNewObject->pms_booking_id);
        }
    }

    private function case_virtual_card_group_booking() {

        $result = $this->get_cc_info_for_group_booking();

        if(key_exists('cc_info_id', $result)) {
            $this->caseVirtualCard($result['cc_info_id'], key_exists('customer', $result));
        }
    }

    /**
     * Handle a job failure.
     *
     * @param  TransactionInitEvent  $event
     * @param  \Exception  $exception
     * @return void
     */
    public function failed(TransactionInitEvent $event, $exception) {
        // TODO: handle failed case
    }

    private function caseVirtualCard($cc_info_id , $customerObjectCreated = false) {

        try {

            $transactionDetails = $this->getSettings();

            $dueDate = $this->getVCDueDate();

            /*_______________Security Damage Deposit___________________*/
            if(!is_null($transactionDetails['securityDeposit'])) {

                try{
                    $ccAuth = $this->createCreditCardAuthorizationRecord(
                        $transactionDetails,
                        $cc_info_id,
                        CreditCardAuthorization::STATUS_MANUAL_PENDING,
                        'securityDeposit',
                        0,
                        $this->pivotTable->getSecurityDepositManualAuthorize()
                    );
                    //event(new EmailEvent(config('db_const.emails.heads.sd_required_for_vc_booking.type'),$this->event->bookingInfoNewObject->id ));
                    event(new EmailEvent(config('db_const.emails.heads.sd_required_for_vc_booking.type'),$ccAuth->id ));

                }catch (\Exception $e){
                    Log::error($e->getTraceAsString(),['Line'=>230 ,'file'=>TransactionInitListener::class]);
                }
            }
            /*___________Security Damage Deposit End___________________*/
            if(!is_null($transactionDetails['paymentSchedule'])) {
                $paymentTypeMetaId = $this->pivotTable->getBookingPaymentAutoCollectionFull();
                TransactionInit::create([
                    'booking_info_id' => $this->event->bookingInfoNewObject->id,
                    'pms_id' => $this->event->bookingInfoNewObject->pms_id,
                    'due_date' => ($dueDate < $this->event->bookingInfoNewObject->booking_time ? $this->event->bookingInfoNewObject->booking_time : $dueDate), // Charge Time could not be less than booking_time
                    'price' => $this->event->bookingInfoNewObject->total_amount, /* Balance Price, as from Invoice*/
                    'is_modified' => '',
                    'payment_status' => TransactionInit::PAYMENT_STATUS_PENDING,
                    'user_id' => $this->event->userId,
                    'user_account_id' => $this->event->userAccount->id,
                    'charge_ref_no' => '',
                    'lets_process' => ($customerObjectCreated == true ? 1 : 0),
                    'final_tick' => 0,
                    'system_remarks' => '',
                    'split' => $paymentTypeMetaId,
                    'against_charge_ref_no' => '',
                    'type' => 'C',
                    'status' => 1,
                    'transaction_type' => $paymentTypeMetaId,
                    'client_remarks' => '',
                    'auth_token' => '',
                    'error_code_id' => '']);
            }
        } catch (\Exception  $e) {
            Log::error($e->getMessage(), array('File'=>TransactionInitListener::class, 'stack'=>$e->getTraceAsString()));
        }

    }

    private function createCreditCardAuthorizationRecord($transactionDetails, $cc_info_id, $status, $key, $manual_auto_auth=false, $manual_type=false){

        $tran = new Transaction();
        $tran->currency_code = $this->event->card->currency;
        $transactionObject = json_encode($tran);
        $auto_auth = $transactionDetails[$key]['autoReauthorize'] == true ? 1 : 0;
        $auth_type = $transactionDetails[$key]['paymentTypeMeta'];
        if($manual_auto_auth != false){
            $auto_auth = $manual_auto_auth;
        }

        if($manual_type != false){
            $auth_type = $manual_type;
        }

        return CreditCardAuthorization::create([
            'booking_info_id'=> $this->event->bookingInfoNewObject->id,
            'cc_info_id' => $cc_info_id,
            'user_account_id'=>$this->event->userAccount->id,
            'hold_amount' => $transactionDetails[$key]['amount'],
            'token' => '',
            'transaction_obj' => $transactionObject,
            'is_auto_re_auth' => $auto_auth,
            'type' => $auth_type,
            'due_date'=> $transactionDetails[$key]['dueDate'],
            'next_due_date' => $transactionDetails[$key]['dueDate'],
            'status' => $status,
        ]);
    }

    /**
     * This function is Deprecated, instead use "createCreditCardAuthorizationRecord" function
     *
     * */
    private function _creditCard_case_validation($customerObject, $cc_info_id, $transactionDetails, $cc_info) {

        //This function is Deprecated, instead use "createCreditCardAuthorizationRecord" function
        try {





            $status = 0;
            $detailEntry = false;
            $detailEntryMsg = '';
            $decline_email_sent = false;

            if(!is_null($transactionDetails['creditCardValidation'])) {
                $this->event->card->amount=$transactionDetails['creditCardValidation']['amount'];
                $authorizeWithCustomerObject = new Transaction();
                
                // Check if date less than current time then auth
                if($transactionDetails['creditCardValidation']['dueDate'] <=  date('Y-m-d H:i:s', time()) && !empty($this->event->userPaymentGateway)) {
                    
                    try {
                        $detailEntry = true;
                        $authorizeWithCustomerObject = $this->paymentGateway->authorizeWithCustomer($customerObject,
                            $this->event->card, $this->event->userPaymentGateway);
                        $detailEntryMsg =($authorizeWithCustomerObject->exceptionMessage != '' ? $authorizeWithCustomerObject->exceptionMessage : $authorizeWithCustomerObject->message);


                    } catch (GatewayException $e) {

                        if($e->getCode() == PaymentGateway::ERROR_CODE_3D_SECURE) {

                            $authorizeWithCustomerObject->state = Transaction::STATE_REQUIRE_ACTION;
                            $authorizeWithCustomerObject->paymentIntentId = null;
                            $authorizeWithCustomerObject->token = '';
                            $authorizeWithCustomerObject->currency_code = $this->event->card->currency;

                        } else {
                            $detailEntryMsg = $e->getDescription();
                            report($e);
                            // Log::debug($e->getDescription(), array('File' => 'TransactionInitListener'));
                            $authorizeWithCustomerObject->exceptionMessage = $e->getDescription();
                            $authorizeWithCustomerObject->currency_code = $this->event->card->currency;

                            // Send Email
                            $auth_failed = true;

                            if ($e->getCode() != PaymentGateway::CODE_NETWORK_ERROR_RE_TRY_ABLE) {
                                Bookings::BA_reportInvalidCardForBDCChannel($this->event->bookingInfoNewObject);
                            }
                        }
                    }

                    $token = $authorizeWithCustomerObject->token;
                    $status = ($authorizeWithCustomerObject->status == true ? 1 : 7);
                    $this->event->card->amount = ''; // Again remove amount from card getting Auth
                    $nextDueDateEntry = null;

                }else{
                    $token = '';
                    $authorizeWithCustomerObject->currency_code = $this->event->card->currency;
                    $status = 0;
                    $nextDueDateEntry = $transactionDetails['creditCardValidation']['dueDate'];
                } // else duedate > current

                $nextDueDate = ($status == 5 ?  $nextDueDateEntry :
                    ($transactionDetails['creditCardValidation']['autoReauthorize'] == true ?
                        ($status == 0 ? $nextDueDateEntry :
                            (date('Y-m-d H:i:s',strtotime($transactionDetails['creditCardValidation']['dueDate']) +
                                ($transactionDetails['creditCardValidation']['autoReauthorizeDays'] * 86400))) ) : $nextDueDateEntry));

                $authorizeWithCustomerObject->status = ( $status == 1 ? true  : false);

                $payment_intent_id = null;
                if($authorizeWithCustomerObject->state == Transaction::STATE_REQUIRE_ACTION) {
                    $status = CreditCardAuthorization::STATUS_WAITING_APPROVAL;
                    $authorizeWithCustomerObject->status = $status;
                    $authorizeWithCustomerObject->amount = $transactionDetails['creditCardValidation']['amount'];
                    $authorizeWithCustomerObject->currency_code = $this->event->card->currency;
                    $payment_intent_id = $authorizeWithCustomerObject->paymentIntentId;
                    $detailEntryMsg = 'Guest needs to authenticate';
                }

                $ccAuth = CreditCardAuthorization::create([
                    'booking_info_id'=> $this->event->bookingInfoNewObject->id,
                    'cc_info_id' => $cc_info_id,
                    'user_account_id'=>$this->event->userAccount->id,
                    'hold_amount' => $transactionDetails['creditCardValidation']['amount'],
                    'token' => $token,
                    'transaction_obj' => json_encode($authorizeWithCustomerObject),
                    'is_auto_re_auth' => $transactionDetails['creditCardValidation']['autoReauthorize'] == true ? 1 : 0,
                    'type' => $transactionDetails['creditCardValidation']['paymentTypeMeta'],
                    'due_date'=> $transactionDetails['creditCardValidation']['dueDate'],
                    'next_due_date' => $nextDueDate,
                    'status' => $status,
                    'decline_email_sent' => $decline_email_sent ? 1 : 0,
                    'payment_intent_id' => $payment_intent_id]);




                if($authorizeWithCustomerObject->state == Transaction::STATE_REQUIRE_ACTION) {
                    event(new EmailEvent(config('db_const.emails.heads.auth_3ds_required.type'),$ccAuth->id ));
                }

                if($detailEntry){
                    
                    $paymentGatewayFormId = 0;
                    if(!empty($this->event->userPaymentGateway))
                        $paymentGatewayFormId = $this->event->userPaymentGateway->payment_gateway_form->id;
                    
                    $ccAuthDetail = new AuthorizationDetails();
                    $ccAuthDetail->cc_auth_id =$ccAuth->id;
                    $ccAuthDetail->user_account_id = $this->event->userAccount->id;
                    $ccAuthDetail->payment_processor_response = json_encode($authorizeWithCustomerObject);
                    $ccAuthDetail->payment_gateway_form_id = $paymentGatewayFormId;
                    $ccAuthDetail->payment_status = $authorizeWithCustomerObject->status;
                    $ccAuthDetail->amount = $transactionDetails['creditCardValidation']['amount'];
                    $ccAuthDetail->charge_ref_no = $payment_intent_id == null ? $token : $payment_intent_id;
                    $ccAuthDetail->order_id = $this->event->card->order_id;
                    $ccAuthDetail->error_msg = $detailEntryMsg;
                    $ccAuthDetail->save();

                    if (!empty($auth_failed)) {
                        /* Send Email to Guest on Auth Failed */
                        event(new EmailEvent(config('db_const.emails.heads.auth_failed.type'), $ccAuthDetail->id));
                        $ccAuth->decline_email_sent = 1;
                        $ccAuth->save();
                    }

                    /**
                     * CC Auth Success | Failed
                     */
                    if ($ccAuth->status == 1){
                        event(new PMSPreferencesEvent($this->event->userAccount, $this->event->bookingInfoNewObject, 0, config('db_const.user_preferences.preferences.CREDIT_CARD_VALIDATION_AUTH_SUCCESS'), $ccAuth->id));

                        event(new EmailEvent(config('db_const.emails.heads.credit_card_authorization_successful.type'),$ccAuthDetail->id ));

                    } else if (($ccAuth->status == 7) || ($ccAuth->status == 5)){
                        if (!isset($e) || ( $e->getCode() != PaymentGateway::CODE_NETWORK_ERROR_RE_TRY_ABLE)){
                            event(new PMSPreferencesEvent($this->event->userAccount, $this->event->bookingInfoNewObject, 0, config('db_const.user_preferences.preferences.CREDIT_CARD_VALIDATION_AUTH_FAILED'), $ccAuth->id));
                            Bookings::BA_reportInvalidCardForBDCChannel($this->event->bookingInfoNewObject);
                            event(new EmailEvent(config('db_const.emails.heads.credit_card_authorization_failed.type'),$ccAuthDetail->id, ['error_msg' => $detailEntryMsg ]  ));

                        }


                    }

                }

            } else {
                // Log::debug("creditCardValidation is null for PMS BookingID: " . $this->event->bookingInfoNewObject->id);
            }

        } catch (\Exception $e) {
            Log::error($e->getMessage(), [
                'File'=>'TransactionInitListener',
                'Stack' => $e->getTraceAsString()]);
            return null;
        }
    }

    /**
     * This function is Deprecated, instead use "createCreditCardAuthorizationRecord" function
     *
     * */
    private function _creditCard_case_security_damage_deposit($customerObject, $cc_info_id, $transactionDetails, $cc_info) {

        //This function is Deprecated, instead use "createCreditCardAuthorizationRecord" function

        try {

            $status = 0;
            $detailEntry = false;
            $detailEntryMsg = '';
            $decline_email_sent = false;

            if(!is_null($transactionDetails['securityDeposit'])) {
                $this->event->card->amount=$transactionDetails['securityDeposit']['amount'];

                if($this->event->card->amount <= 0)
                    return null;

                $authorizeWithCustomerObject = new Transaction();
                // Check if date less than current time then auth
                if($transactionDetails['securityDeposit']['dueDate'] <=  date('Y-m-d H:i:s', time()) && !empty($this->event->userPaymentGateway)) {

                    try {
                        $detailEntry = true;
                        $authorizeWithCustomerObject = $this->paymentGateway->authorizeWithCustomer($customerObject,
                            $this->event->card, $this->event->userPaymentGateway);
                        $status  = ($authorizeWithCustomerObject->status == true ? 1 : 7); // Attempted
                        $detailEntryMsg =($authorizeWithCustomerObject->exceptionMessage != '' ? $authorizeWithCustomerObject->exceptionMessage : $authorizeWithCustomerObject->message);

                    }  catch (GatewayException $e) {

                        if($e->getCode() == PaymentGateway::ERROR_CODE_3D_SECURE) {

                            $authorizeWithCustomerObject->state = Transaction::STATE_REQUIRE_ACTION;
                            $authorizeWithCustomerObject->paymentIntentId = null;
                            $authorizeWithCustomerObject->token = '';
                            $authorizeWithCustomerObject->currency_code = $this->event->card->currency;

                        } else {

                            $detailEntryMsg = $e->getDescription();
                            report($e);
                            // Log::debug($e->getDescription(), array('File' => 'TransactionInitListener'));
                            $status = CreditCardAuthorization::STATUS_FAILED;
                            $authorizeWithCustomerObject->exceptionMessage = $e->getDescription();
                            $authorizeWithCustomerObject->currency_code = $this->event->card->currency;
                            $authorizeWithCustomerObject->status = false;

                            if ($e->getCode() != PaymentGateway::CODE_NETWORK_ERROR_RE_TRY_ABLE) {
                                Bookings::BA_reportInvalidCardForBDCChannel($this->event->bookingInfoNewObject);
                            }
                        }
                    }

                    $token = $authorizeWithCustomerObject->token;
                    $transactionObject = json_encode($authorizeWithCustomerObject);
                    $this->event->card->amount = ''; // Again remove amount from card getting Auth
                    // $nextDueDateEntry = null;
                    $nextDueDateEntry = $transactionDetails['securityDeposit']['dueDate'];

                } else {
                    $nextDueDateEntry = $transactionDetails['securityDeposit']['dueDate'];
                    $token = '';
                    $authorizeWithCustomerObject->currency_code = $this->event->card->currency;
                    $transactionObject = json_encode($authorizeWithCustomerObject);
                    $status = CreditCardAuthorization::STATUS_PENDING;
                } // else dueDate > current

                $nextDueDate = ($transactionDetails['securityDeposit']['autoReauthorize'] == true ?
                    ($status != 1 ? $nextDueDateEntry : (date('Y-m-d H:i:s',strtotime($transactionDetails['securityDeposit']['dueDate']) +
                        ($transactionDetails['securityDeposit']['autoReauthorizeDays'] * 86400))) ) : $nextDueDateEntry);
                /* --------------------------------
                getting next date by multiplying seconds of 24 Hours to autoReauthorizeDays for converting days into seconds
                -----------------------------------*/

                $payment_intent_id_ssd = null;
                if($authorizeWithCustomerObject->state == Transaction::STATE_REQUIRE_ACTION) {
                    $status = CreditCardAuthorization::STATUS_WAITING_APPROVAL;
                    $authorizeWithCustomerObject->status = $status;
                    $authorizeWithCustomerObject->amount = $transactionDetails['securityDeposit']['amount'];
                    $authorizeWithCustomerObject->currency_code = $this->event->card->currency;
                    $payment_intent_id_ssd = $authorizeWithCustomerObject->paymentIntentId;
                    $detailEntryMsg = 'Guest needs to authenticate';
                }

                $ccAuth = CreditCardAuthorization::create([
                    'payment_intent_id' => $payment_intent_id_ssd,
                    'booking_info_id'=> $this->event->bookingInfoNewObject->id,
                    'cc_info_id' => $cc_info_id,
                    'user_account_id'=>$this->event->userAccount->id,
                    'hold_amount' => $transactionDetails['securityDeposit']['amount'],
                    'token' => $token,
                    'transaction_obj' => $transactionObject,
                    'is_auto_re_auth' => $transactionDetails['securityDeposit']['autoReauthorize'] == true ? 1 : 0,
                    'type' => $transactionDetails['securityDeposit']['paymentTypeMeta'],
                    'due_date'=> $transactionDetails['securityDeposit']['dueDate'],
                    'next_due_date' => $nextDueDate,
                    'status' => $status,
                    'decline_email_sent' => $decline_email_sent ? 1 : 0]);

                if($authorizeWithCustomerObject->state == Transaction::STATE_REQUIRE_ACTION) {

                    event(new EmailEvent(config('db_const.emails.heads.sd_3ds_required.type'),$ccAuth->id ));
                } elseif ($status == CreditCardAuthorization::STATUS_FAILED) {
                    event(new EmailEvent(config('db_const.emails.heads.sd_auth_failed.type'),  $ccAuth->id, ['error_msg' => $detailEntryMsg ]));
                    $ccAuth->decline_email_sent = 1;
                    $ccAuth->save();
                }

                if($detailEntry){
                    
                    $paymentGatewayFormId = 0;
                    if(!empty($this->event->userPaymentGateway))
                        $paymentGatewayFormId = $this->event->userPaymentGateway->payment_gateway_form->id;
                    
                    $ccAuthDetail = new AuthorizationDetails();
                    $ccAuthDetail->cc_auth_id = $ccAuth->id;
                    $ccAuthDetail->user_account_id = $this->event->userAccount->id;
                    $ccAuthDetail->payment_processor_response = json_encode($authorizeWithCustomerObject);
                    $ccAuthDetail->payment_gateway_form_id = $paymentGatewayFormId;
                    $ccAuthDetail->payment_status = $authorizeWithCustomerObject->status;
                    $ccAuthDetail->amount = $transactionDetails['securityDeposit']['amount'];
                    $ccAuthDetail->charge_ref_no = $payment_intent_id_ssd == null ? $token : $payment_intent_id_ssd;
                    $ccAuthDetail->order_id = $this->event->card->order_id;
                    $ccAuthDetail->error_msg = $detailEntryMsg;
                    $ccAuthDetail->save();
                    /**
                     * SD Auth Success | Failed
                     */
                    if ($ccAuth->status == 1) {

                        event(new PMSPreferencesEvent($this->event->userAccount, $this->event->bookingInfoNewObject, 0, config('db_const.user_preferences.preferences.SECURITY_DEPOSIT_AUTH_CAPTURE_SUCCESS'), $ccAuth->id));
                        event(new EmailEvent(config('db_const.emails.heads.sd_authorization_successful.type'),$ccAuthDetail->id ));

                    } else if (($ccAuth->status == 7) || ($ccAuth->status == 5)){

                        if (!isset($e) || ($e->getCode() != PaymentGateway::CODE_NETWORK_ERROR_RE_TRY_ABLE)) {
                            event(new PMSPreferencesEvent($this->event->userAccount, $this->event->bookingInfoNewObject, 0, config('db_const.user_preferences.preferences.SECURITY_DEPOSIT_AUTH_CAPTURE_FAILED'), $ccAuth->id));
                        }

                        //event(new EmailEvent(config('db_const.emails.heads.sd_auth_failed.type'),  $ccAuth->id, ['error_msg' => $detailEntryMsg ]));


                    }
                }

            }
            //else {
            // Log::debug("securityDeposit is null for PMS BookingID: " . $this->event->bookingInfoNewObject->id,array('File'=>'TransactionInitListener'));
            //}

        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['File'=>TransactionInitListener::class, 'Stack'=>$e->getTraceAsString()]);
            return null;
        }
    }

    private function caseCreditCard($customerObject, $cc_info_id, $cc_info) {

        try {

            $transactionDetails = $this->getSettings(); // Getting All Transaction Settings securityDeposit

            if(!is_null($transactionDetails['creditCardValidation'])) {
                $this->createCreditCardAuthorizationRecord(
                    $transactionDetails,
                    $cc_info_id,
                    CreditCardAuthorization::STATUS_PENDING,
                    'creditCardValidation'
                );
            }

            if(!is_null($transactionDetails['securityDeposit'])){
                $this->createCreditCardAuthorizationRecord(
                    $transactionDetails,
                    $cc_info_id,
                    CreditCardAuthorization::STATUS_PENDING,
                    'securityDeposit'
                );
            }

            /* __________PaymentSchedule Settings__________________________ */
            if(!is_null($transactionDetails['paymentSchedule'])) {

                $futureCharge = false;
                $tranId = 0;

                foreach ($transactionDetails['paymentSchedule'] as $charge) {
                    $transactionInit = TransactionInit::create([
                        'booking_info_id'=>$this->event->bookingInfoNewObject->id,
                        'pms_id' => $this->event->bookingInfoNewObject->pms_id,
                        'due_date'=>$charge['dueDate'],
                        'price'=>$charge['amount'],
                        'is_modified'=>'',
                        'payment_status'=>2,
                        'user_id'=>$this->event->userId,
                        'user_account_id'=>$this->event->userAccount->id,
                        'charge_ref_no'=>'',
                        'lets_process'=> 1,
                        'final_tick'=> 0,
                        'system_remarks'=>'',
                        'split'=>$charge['paymentTypeMeta'],
                        'against_charge_ref_no'=>'',
                        'type'=>'C',
                        'status' => 1,
                        'transaction_type'=>$charge['paymentTypeMeta'],
                        'client_remarks'=>'',
                        'auth_token'=>'',
                        'error_code_id'=>'']);
                    $futureCharge = ($charge['dueDate'] > date('Y-m-d H:i:s') ? true : false);
                    $tranId = $transactionInit->id;
                }

                if($futureCharge) {
                    event(new PMSPreferencesEvent($this->event->userAccount,
                        $this->event->bookingInfoNewObject,
                        $tranId,
                        config('db_const.user_preferences.preferences.BOOKINGS_THAT_WILL_BE_CHARGED_IN_FUTURE')));
                }

            } else {
                // Log::notice("Payment Schedule Settings not active for Booking_id :".$this->event->bookingInfoNewObject->id,array('File'=>'TransactionInitListener'));
            }

        } catch(\Exception $e) {
            Log::error($e->getMessage(), array('File'=>'TransactionInitListener','booking id'=>$this->event->bookingInfoNewObject->id, 'Stack'=>$e->getTraceAsString()));
            return null;
        }
    }

    private function caseBankDraft($cc_info_id) {

        $transactionDetails=$this->getSettings();

        /* _____________Security Damage Deposit________________ */
        if(!is_null($transactionDetails['securityDeposit'])) {

            $this->createCreditCardAuthorizationRecord(
                $transactionDetails,
                $cc_info_id,
                CreditCardAuthorization::STATUS_MANUAL_PENDING,
                'securityDeposit',
                0,
                $this->pivotTable->getSecurityDepositManualAuthorize()
            );
        }
        /*_______Security Damage Deposit End________________*/

        //event(new PaymentAttemptEvent($this->event->bookingInfoNewObject)); //TODO : Check  for this event because in bank transfer case we are not using transaction init entries so its relation in that will throw an exception of collection object not found OR null
    }

    /**
     * For Getting DB ID of booking_source from channel code got from jobs...
     * @param $channelCode
     * @return mixed
     */
    private function getBsIdFromChannelCode($channelCode) {
        $bookingSource = BookingSourceForm::select('id')->where('channel_code',$channelCode)->first();

        if ($bookingSource == null){
            Log::emergency('Booking Source Against Channel Code => '.$channelCode.' not Available in BookingSourceForm or not Valid',['BookingInfo' => json_encode($this->event->bookingInfoNewObject->id), 'File'=> TransactionInitListener::class]);
            return 0;
        } else{
            return $bookingSource->id;
        }
    }

    /**
     * For Getting Settings Transaction amount and due date against booking
     * @return array
     */
    private function getSettings()
    {
        if ($this->userPaymentSettings === null){
            $options = new PaymentSettingsOptions();
            $options->property_info_id = $this->event->propertyInfoId;
            $options->user_account_id = $this->event->userAccount->id;
            $options->booking_id = $this->event->bookingInfoNewObject->id;
            $options->booking_source_id = $this->event->bookingInfoNewObject->bookingSourceForm->id; //$this->getBsIdFromChannelCode($this->event->booking->channelCode);
            $options->totalAmount = $this->event->bookingInfoNewObject->total_amount;
            $options->timeZone = $this->event->bookingInfoNewObject->property_time_zone;

            //Local Hotel DateTime
            $options->bookingTime = Carbon::parse($this->event->bookingInfoNewObject->booking_time, 'GMT')->setTimezone($this->event->bookingInfoNewObject->property_time_zone)->toDateTimeString(); //Local BookingTime
            $options->checkInDate = Carbon::parse($this->event->bookingInfoNewObject->check_in_date, 'GMT')->setTimezone($this->event->bookingInfoNewObject->property_time_zone)->toDateTimeString(); // Local Check-in date
            $options->checkOutDate = Carbon::parse($this->event->bookingInfoNewObject->check_out_date, 'GMT')->setTimezone($this->event->bookingInfoNewObject->property_time_zone)->toDateTimeString(); // Local Check-out date

            $options->isNonRefundable = $this->event->booking->isNonRefundableBooking();
            $paymentSetting = new PaymentSettings($options);
            $this->userPaymentSettings = $paymentSetting->transactionDetails($this->event->booking);

            $this->logSettings($paymentSetting->allSettings());

            if (empty( $this->userPaymentSettings['paymentSchedule'] ) && empty( $this->userPaymentSettings['creditCardValidation'] ) &&
                empty( $this->userPaymentSettings['securityDeposit'] ) ) {
                BookingInfo::where( 'id', $this->event->bookingInfoNewObject->id)->update(['is_process_able' => 0]);
                Log::notice('Booking_info ID # '.$this->event->bookingInfoNewObject->id .' Not able to entertain, because all settings are deactivated. So its is_process_able => 0');
            }
        }
        return $this->userPaymentSettings;
    }

    private function caseCreditCard_WithOutCustomer($cc_info_id) {
        try {

            
            $transactionDetails = $this->getSettings(); // Getting All Transaction Settings
            /*------------------ CcValidation (auth) -------------------*/

            if(!is_null($transactionDetails['creditCardValidation'])) {
                $this->createCreditCardAuthorizationRecord(
                    $transactionDetails,
                    $cc_info_id,
                    CreditCardAuthorization::STATUS_PENDING,
                    'creditCardValidation'
                );
            }

            if(!is_null($transactionDetails['securityDeposit'])){
                $this->createCreditCardAuthorizationRecord(
                    $transactionDetails,
                    $cc_info_id,
                    CreditCardAuthorization::STATUS_PENDING,
                    'securityDeposit'
                );
            }

            $futureCharge = false;
            /* -------------- Payment Schedule --------------- */
            if(!is_null($transactionDetails['paymentSchedule'])) {
                foreach ($transactionDetails['paymentSchedule'] as $charge) {
                    $transactionInit = TransactionInit::create([
                        'booking_info_id'=>$this->event->bookingInfoNewObject->id,
                        'pms_id' => $this->event->bookingInfoNewObject->pms_id,
                        'due_date'=>$charge['dueDate'],
                        //'update_attempt_time'=>'',
                        'price'=>$charge['amount'],
                        'is_modified'=>'',
                        'payment_status'=>2,
                        'user_id'=>$this->event->userId,
                        'user_account_id'=>$this->event->userAccount->id,
                        'charge_ref_no'=>'',
                        'lets_process'=> 0, //lets proccess 0 due to creating customerObject  Failed
                        'final_tick'=> 0,
                        'system_remarks'=>'',
                        'split'=>$charge['paymentTypeMeta'],
                        'against_charge_ref_no'=>'',
                        'type'=>'C',
                        'status' => 1,
                        'transaction_type'=>$charge['paymentTypeMeta'],
                        'client_remarks'=>'',
                        'auth_token'=>'',
                        'error_code_id'=>'']);
                }
            } else {

                event(new PMSPreferencesEvent($this->event->userAccount, $this->event->bookingInfoNewObject,0, config('db_const.user_preferences.preferences.BOOKINGS_THAT_WILL_NOT_BE_CHARGED') ));
                // Log::notice("Payment Schedule Settings not active for Booking_id :".$this->event->bookingInfoNewObject->id,array('File'=>'TransactionInitListener'));
            }

        } catch (\Exception $e) {
            Log::error($e->getMessage(),array('File'=>'TransactionInitListener'));
            Log::error($e->getTraceAsString(),array('File'=>'TransactionInitListener'));
        }

        return $transactionDetails;
    }


    private function VcSendGuestSecurityDepositMail() {

        try {

            // $clientNotifySettings = new ClientNotifySettings($this->event->userAccount->id);
            // $guestCorrespondence = $clientNotifySettings->isActiveMail(config('db_const.user_notify_Settings.guestCorrespondence'));

            if ($this->event->bookingInfoNewObject->guest_email != null ) {

                $url = URL::signedRoute('guest_booking_details', ['id' => $this->event->bookingInfoNewObject->id]);

                $propertyInfo = $this->event->userAccount->properties_info->where('pms_property_id', $this->event->bookingInfoNewObject->property_id)->first();

                $fromArr = [($propertyInfo->property_email != null ? $propertyInfo->property_email : $this->event->userAccount->email), $propertyInfo->name];

                $check_property_image = getImageNameOrInitials( $propertyInfo, config('db_const.logos_directory.property.value') );
                $logo = asset('storage/uploads/property_logos/'.$check_property_image['property_image']);

                $email = array(
                    'subject' => 'Security Damage Deposit Authorization BookingID #' . $this->event->bookingInfoNewObject->pms_booking_id . ' ' . $this->event->bookingInfoNewObject->guest_title . ' ' . $this->event->bookingInfoNewObject->guest_name . ' ' . $this->event->bookingInfoNewObject->guest_last_name,
                    'markdown' => 'emails.GuestInformToAddDamageDepositCard',
                    'name' => $this->event->bookingInfoNewObject->guest_name,
                    'url' => $url,
                    'from' => $fromArr,
                    'companyName' => $propertyInfo->name,
                    'companyImage' => $logo,
                    'companyInitials' => $check_property_image['property_initial'],
                    'checkIndate' => $this->event->bookingInfoNewObject->check_in_date,
                    'checkOutdate' => $this->event->bookingInfoNewObject->check_out_date,);


                Mail::to($this->event->bookingInfoNewObject->guest_email)->send(new GenericEmail($email));


                event(new PMSPreferencesEvent($this->event->userAccount, $this->event->bookingInfoNewObject,0, config('db_const.user_preferences.preferences.BOOKINGS_THAT_WILL_NOT_BE_CHARGED') ));
                // Log::notice("Payment Schedule Settings not active for Booking_id :".$this->event->bookingInfoNewObject->id,array('File'=>'TransactionInitListener'));
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage(),array('File'=>'TransactionInitListener'));
            Log::error($e->getTraceAsString(),array('File'=>'TransactionInitListener'));
        }
    }

    private function handleVirtualCard() {

        /*
        * Customer object creation is OFF for VC booking case that's why second parameter will be false
        */
        $this->caseVirtualCard($this->event->cc_info->id, false);

    }

    private function getVcDueDate() {

        $dueDate = $this->event->booking->getDueDateFromGuestComments();
        $dueDate = Carbon::parse($dueDate)->toDateTimeString();

        $bookingDate = Carbon::parse($this->event->bookingInfoNewObject->booking_time)->toDateString();
        $checkInDate = Carbon::parse($this->event->bookingInfoNewObject->check_in_date)->toDateString();

        if ($bookingDate >= $checkInDate) {

            $dueDate = Carbon::parse($dueDate)->addMinute(10)->toDateTimeString();

        } else {

            $repoBookings = new Bookings($this->event->userAccount->id);
            $dueDateAddedHours = $repoBookings->addCheckInHours($dueDate); /* add Hours To Check-in Datetime */
            $dueDate = $repoBookings->setVcDueDateWithTimeZone(
                $this->event->userAccount,
                $this->event->userAccount->properties_info->where('id', $this->event->propertyInfoId)->first(),
                $dueDateAddedHours);
        }
        return $dueDate;

    }

    private  function updateBookingOnPMSToGetTokenInModify(){
        try {
            $pms = new PMS($this->event->userAccount);
            $pmsOptions = new PmsOptions();
            $pmsOptions->propertyID = $this->event->bookingInfoNewObject->property_info->pms_property_id;
            $pmsOptions->propertyKey = $this->event->bookingInfoNewObject->property_info->property_key;
            $pmsOptions->bookingID = $this->event->bookingInfoNewObject->pms_booking_id;
            $pmsOptions->requestType = PmsOptions::REQUEST_TYPE_JSON;
            $bookingToUpdateData = new  Booking();
            $bookingToUpdateData->id = $this->event->bookingInfoNewObject->pms_booking_id;
            $bookingToUpdateData->notes = $this->event->booking->notes . " ";
            $response = $pms->update_booking($pmsOptions, $bookingToUpdateData);
        } catch (PmsExceptions $exception) {
            Log::error($exception->getMessage(),
                ['BookingInfo' => $this->event->bookingInfoNewObject->id, 'Mode' => 'maintenance', 'File' => __FILE__, 'Function' => __FUNCTION__]);
        }
    }

    /**
     * This function will run for group/sub/child booking.
     * It will get cc_info of master and replicate it for group booking.
     * @return array
     */
    private function get_cc_info_for_group_booking() {

        try {

            /**
             * @var $masterBookingInfo BookingInfo
             */
            $masterBookingInfo = BookingInfo::where('pms_booking_id', $this->event->booking->masterId)
                ->where('user_account_id', $this->event->userAccount->id)
                ->where('pms_id', $this->event->bookingInfoNewObject->pms_id)
                ->first();

            if ($masterBookingInfo != null) {

                $is_vc = $masterBookingInfo->is_vc == BS_Generic::PS_VIRTUAL_CARD ? 1 : 0;

                /**
                 * @var $ccInfo CreditCardInfo
                 * @var $groupCCInfo CreditCardInfo
                 */

                // Fetch CC Info of Master Booking
                $ccInfo = CreditCardInfo::where('is_vc', $is_vc)
                    ->where('user_account_id', $this->event->userAccount->id)
                    ->where('booking_info_id', $masterBookingInfo->id)
                    ->latest('id')
                    ->limit(1)
                    ->first();

                $groupCCInfo = $ccInfo->replicate();
                $groupCCInfo->booking_info_id = $this->event->bookingInfoNewObject->id;
                $groupCCInfo->save();

                if (!empty($groupCCInfo->auth_token)) {

                    return ['status' => true,
                        'cc_info_id' => $groupCCInfo->id,
                        'customer' => $groupCCInfo->customer_object,
                        'cc_info' => $groupCCInfo,
                        'error_message' => 'Successfully Created Customer Object. Group Booking'];

                } else {
                    return ['status' => false,
                        'cc_info_id' => $groupCCInfo->id,
                        'cc_info' => $groupCCInfo,
                        'error_message' => 'Customer not created yet. Group Booking'];
                }

            } else {
                Log::error('CC info not found', [
                    'function' => __FUNCTION__,
                    'bookingID' => $this->event->bookingInfoNewObject->pms_booking_id,
                    'pms_booking_id' => $this->event->booking->masterId,
                    'user_account_id' => $this->event->userAccount->id,
                    'pms_id' => $this->event->bookingInfoNewObject->pms_id
                ]);
            }

            return ['status' => false,
                'error_message' => 'Master Booking not Found.',
                'type'=> config('db_const.credit_card_infos.type.customer_obj_not_created')];

        } catch (\Exception $e) {

            Log::error($e->getMessage(), [
                'function' => __FUNCTION__,
                'bookingID' => $this->event->bookingInfoNewObject->pms_booking_id,
                'pms_booking_id' => $this->event->booking->masterId,
                'user_account_id' => $this->event->userAccount->id,
                'pms_id' => $this->event->bookingInfoNewObject->pms_id
            ]);

            return ['status' => false,
                'error_message' => $e->getMessage(),
                'type'=> config('db_const.credit_card_infos.type.customer_obj_not_created')];
        }

    }

    public function logSettings(PropertySettings $settings = null) {

        if (!empty($settings)) {

            $payment_rules = $settings->paymentRules($this->event->bookingInfoNewObject->bookingSourceForm->id);
            $payment_gateway = $settings->paymentGateway();

            if (BookingInfoDetail::where('booking_info_id', $this->event->bookingInfoNewObject->id)->count() == 0) {
                BookingInfoDetail::create([
                    'booking_info_id' => $this->event->bookingInfoNewObject->id,
                    'cc_auth_settings' => $payment_rules->creditCardValidationSetting(),
                    'security_deposit_settings' => $payment_rules->securityDepositSetting(),
                    'payment_schedule_settings' => $payment_rules->paymentScheduleSetting(),
                    'cancellation_settings' => $payment_rules->cancellationSetting(),
                    'payment_gateway_settings' => !empty($payment_gateway->gateway) ? $payment_gateway->gateway : null,
                    'full_response' => json_encode($this->event->booking),
                    'use_bs_settings' => $this->event->bookingInfoNewObject->property_info->bs_setting_property_id > 0,
                    'use_pg_settings' => $this->event->bookingInfoNewObject->property_info->pg_setting_property_id > 0,
                ]);
            }
        }
    }
}
