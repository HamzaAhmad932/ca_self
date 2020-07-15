<?php


namespace App\Jobs\Charge\BA;


use App\BAModels\ReadyToFirstAttemptTransaction;
use App\Events\Charge\BA\ChargeResponseEvent;
use App\Events\Emails\EmailEvent;
use App\Repositories\Bookings\Bookings;
use App\Repositories\NotificationAlerts;
use App\Repositories\PaymentGateways\PaymentGateways;
use App\Repositories\Settings\PaymentTypeMeta;
use App\System\PaymentGateway\Exceptions\GatewayException;
use App\System\PaymentGateway\Models\Card;
use App\System\PaymentGateway\Models\Transaction;
use App\System\PaymentGateway\PaymentGateway;
use App\System\PMS\BookingSources\BS_Generic;
use App\System\PMS\exceptions\PmsExceptions;
use App\System\PMS\Models\Booking;
use App\System\PMS\Models\PmsOptions;
use App\System\PMS\PMS;
use App\TransactionDetail;
use App\TransactionInit;
use Illuminate\Support\Carbon;

trait BAChargeJobsHelperTrait
{
    private $current_record = [];

    /**
     * @var PaymentTypeMeta
     */
    private $heads;


    public function init_helper()
    {
        $this->heads = new PaymentTypeMeta();
    }

    /**
     * @return PaymentTypeMeta
     */
    public function getHeads(): PaymentTypeMeta
    {
        if (empty($this->heads))
            $this->init_helper();

        return $this->heads;
    }

    /**
     * @param ReadyToFirstAttemptTransaction $record
     * @param $payment_status
     * @param Transaction|null $response
     * @param null $message
     * @return TransactionDetail
     */
    public function logDetail(ReadyToFirstAttemptTransaction $record, $payment_status, Transaction $response = null, $message = null)
    {

        if (empty($message) && !empty($response))
            $message = !empty($response->exceptionMessage) ? $response->exceptionMessage : $response->message;

        $detail = new TransactionDetail();
        $detail->error_msg = $message;
        $detail->payment_status = $payment_status;
        $detail->transaction_init_id = $record->id;
        $detail->user_account_id = $record->user_account_id;
        $detail->order_id = !empty($response->order_id) ? $response->order_id : 0;
        $detail->charge_ref_no = !empty($response->token) ? $response->token : '';
        $detail->cc_info_id = !empty($record->credit_card_info_id) ? $record->credit_card_info_id : 0;
        $detail->payment_gateway_form_id = $record->user_payment_gateway->payment_gateway_form->id;
        $detail->payment_processor_response = !empty($response->fullResponse) ? $response->fullResponse : '';
        $detail->save();

        return $detail;
    }


    /**
     * @param ReadyToFirstAttemptTransaction $record
     * @return Booking|string
     */
    public function fetch_Booking_Details_json_xml(ReadyToFirstAttemptTransaction $record)
    {
        try {

            $options = new PmsOptions();
            $options->propertyID = $record->pms_property_id;
            $options->propertyKey = $record->property_key;
            $options->bookingID = $record->pms_booking_id;
            $options->includeInvoice = true;
            $options->includeInfoItems = true;

            $pms = new PMS($record->user_account);
            $pms_booking = $pms->fetch_Booking_Details_json_xml($options);

            if (!empty($pms_booking[0]) && $pms_booking[0] instanceof Booking) {
                return $pms_booking[0];
            } else {
                return 'No Booking Found';
            }

        } catch (PmsExceptions $exception) {

            log_exception_by_exception_object($exception, $this->current_record);
            return $exception->getCADefineMessage();
        }

    }

    /**
     * @param ReadyToFirstAttemptTransaction $record
     * @param Card $card
     * @param bool $is_round_error
     */
    public function chargeNow(ReadyToFirstAttemptTransaction $record, Card $card, bool $is_round_error = false)
    {

        $response = new Transaction();

        try {

            $response->amount = $card->amount;
            $response->currency_code = $card->currency;
            $response->order_id = $card->order_id;

            if ($card->amount == 0) {
                $response->status = true;
                $response->message = "Amount Was zero, So not tried for charge";
                $record->transaction_init->update(['lets_process' => 0, 'system_remarks' => $response->message]);
                $this->logDetail($record, TransactionInit::PAYMENT_STATUS_SUCCESS, $response);
                return;
            }

            /** Attempt to Charge Transaction Record */
            $PG = new PaymentGateway();
            $response = $PG->chargeWithCustomer($record->customer_object, $card, $record->user_payment_gateway);

        } catch (GatewayException $e) {

            report($e);
            $response = $this->handleChargeException($record, $response, $e);

        } finally {

            if (!empty($response->CODE_NETWORK_ERROR_RE_TRY_ABLE)) {
                $this->chargeNetworkErrorCase($record, $response);
                return; // Network Problem try to charge later

            } elseif ($response->status && $response->state == Transaction::STATE_SUCCEEDED) {
                $this->chargeSuccessCase($record, $response, $is_round_error); // Succeed

            } elseif ($response->state == Transaction::STATE_REQUIRE_ACTION) {
                $this->charge3dsRequiresCase($record, $response); // 3DS Required

            } else {
                $this->chargeFailedCase($record, $response); // Failed
            }
        }
    }


    /**
     * @param ReadyToFirstAttemptTransaction $record
     * @param Transaction $response
     */
    public function chargeNetworkErrorCase(ReadyToFirstAttemptTransaction $record, Transaction $response)
    {
        $record->transaction_init->update(['attempts_for_500' => $record->attempts_for_500 + 1]);
        event(
            new ChargeResponseEvent(
                $record->transaction_init,
                $response,
                ChargeResponseEvent::CHARGE_NETWORK_FAILURE_CASE,
                null
            )
        );
    }

    /**
     * @param ReadyToFirstAttemptTransaction $record
     * @param Transaction $response
     */
    public function chargeFailedCase(ReadyToFirstAttemptTransaction $record, Transaction $response)
    {

        $detail = $this->setRecordToReAttempt($record, $response);
        event(
            new ChargeResponseEvent(
                $record->transaction_init,
                $response,
                ChargeResponseEvent::CHARGE_FAILED_CASE,
                $detail
            )
        );
    }


    /**
     * @param ReadyToFirstAttemptTransaction $record
     * @param Transaction $response
     */
    public function charge3dsRequiresCase(ReadyToFirstAttemptTransaction $record, Transaction $response)
    {

        $record->transaction_init->update(
            [

                'last_success_trans_obj' => $response,
                'attempt' => $record->attempt + 1,
                'payment_intent_id' => $response->paymentIntentId,
                'payment_status' => TransactionInit::PAYMENT_STATUS_WAITING_APPROVAL,
                'system_remarks' => $record->system_remarks . "\n Guest needs to authenticate",
            ]
        );

        $this->logDetail(
            $record,
            TransactionInit::PAYMENT_STATUS_WAITING_APPROVAL,
            $response,
            'Guest needs to authenticate'
        );

        /* Inform 3DS Charge Authentication Required */
        event(new EmailEvent(config('db_const.emails.heads.charge_3ds_required.type'), $record->id));

    }

    /**
     * @param ReadyToFirstAttemptTransaction $record
     * @param Transaction $response
     * @param $is_round_error
     */
    public function chargeSuccessCase(ReadyToFirstAttemptTransaction $record, Transaction $response, $is_round_error)
    {

        $record->transaction_init->update(
            [
                'final_tick' => 1,
                'lets_process' => 0,
                'charge_ref_no' => $response->token,
                'last_success_trans_obj' => $response,
                'attempt' => $record->attempt + 1,
                'payment_intent_id' => $response->paymentIntentId,
                'payment_status' => TransactionInit::PAYMENT_STATUS_SUCCESS,
                'system_remarks' => $is_round_error == false
                    ? $record->system_remarks
                    : $record->system_remarks . "\n Charged with difference of round error.",
            ]
        );

        $detail = $this->logDetail($record, TransactionInit::PAYMENT_STATUS_SUCCESS, $response);

        event(
            new ChargeResponseEvent(
                $record->transaction_init,
                $response,
                ChargeResponseEvent::CHARGE_SUCCESS_CASE,
                $detail
            )
        );
    }


    /**
     * @param ReadyToFirstAttemptTransaction $record
     * @param Transaction $response
     * @param GatewayException $exception
     * @return Transaction
     */
    public function handleChargeException(ReadyToFirstAttemptTransaction $record, Transaction $response, GatewayException $exception)
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


    public function cancelAuthorization(ReadyToFirstAttemptTransaction $record)
    {
        try {

            if (!empty($record->auth_transaction_obj->status)) { // If CC Authorized.
                $PG = new PaymentGateway();
                $PG->cancelAuthorization($record->auth_transaction_obj, $record->user_payment_gateway);
            }

        } catch (GatewayException $e) {
            log_exception_by_exception_object($e, $this->current_record);
        }
    }

    /**
     * @param ReadyToFirstAttemptTransaction $record
     * @param Transaction $response
     * @param string $reason
     * @return TransactionDetail
     */
    public function setRecordToReAttempt(ReadyToFirstAttemptTransaction $record, Transaction $response = null, $reason = '')
    {


        $attempts_full = ($record->attempt >= TransactionInit::TOTAL_ATTEMPTS - 1);

        $record->transaction_init()->update(
            [
                'attempt' => $record->attempt + 1,

                'system_remarks' => $record->system_remarks . (!empty($reason) ? "\n$reason" : ''),

                'payment_status' => $attempts_full
                    ? TransactionInit::PAYMENT_STATUS_FAIL
                    : TransactionInit::PAYMENT_STATUS_REATTEMPT,

                'lets_process' => $attempts_full
                    ? 0
                    : $record->lets_process,

                'next_attempt_time' => $attempts_full
                    ? $record->next_attempt_time
                    : Carbon::parse($record->next_attempt_time)
                        ->addHour(1)->toDateTimeString(),
            ]
        );

        return $this->logDetail($record, $record->payment_status, $response, $reason);

    }

    /**
     * @param int $status
     * @param array $transaction_ids
     */
    public function setRecordsAvailableToProcess(int $status, array $transaction_ids = [])
    {
        TransactionInit::whereIn('id', $transaction_ids)->update(['in_processing' => $status]);
    }

    /**
     * @param ReadyToFirstAttemptTransaction $record
     * @param Booking $pms_booking
     * @param array $charge_able
     * @return bool
     */
    public function isAlreadyPaidOnPMS(ReadyToFirstAttemptTransaction $record, Booking $pms_booking, array $charge_able = [])
    {

        if (!$charge_able['status']
            && $charge_able['isRoundError'] == false
            && $record->is_vc == BS_Generic::PS_VIRTUAL_CARD
            && $record->transaction_type != $this->getHeads()->getBookingCancellationAutoCollectionFull()) {

            $reason = 'Aborting this transaction because its Paid (Partially Paid) on BookingAutomation.';

            // Abort | Void Transaction.
            $record->transaction_init()->update(
                [
                    'payment_status' => TransactionInit::PAYMENT_STATUS_ABORTED,
                    'lets_process' => 0,
                    'final_tick' => 0,
                    'attempt' => $record->attempt + 1,
                    'system_remarks' => $record->system_remarks . "\n" . $reason
                ]
            );

            $detail = $this->logDetail(
                $record,
                TransactionInit::PAYMENT_STATUS_ABORTED,
                null,
                'Aborting this transaction because its Paid (Partially Paid) on BookingAutomation'
            );

            /* Send Email that this transaction is already charged */
            event(new EmailEvent(config('db_const.emails.heads.payment_aborted.type'), $detail->id));

            /* Dashboard Notifications */
            $this->notify($record, 'payment_failed');

            /* PMS notes update*/
            $this->updateNotes($record, $pms_booking, $reason);

            return true;
        }

        return false;
    }

    /**
     * @param ReadyToFirstAttemptTransaction $record
     * @param Booking $pms_booking
     * @param $message
     */
    public function updateNotes(ReadyToFirstAttemptTransaction $record, Booking $pms_booking, $message)
    {
        try {
            $pms = new PMS($record->user_account);
            $options = new PmsOptions();
            $booking = new Booking();

            $options->propertyID = $record->pms_property_id;
            $options->propertyKey = $record->property_key;
            $options->requestType = PmsOptions::REQUEST_TYPE_JSON;

            $booking->id = $record->pms_booking_id;
            $booking->notes = $pms_booking->notes . "\n $message";
            $booking->propertyId = $record->pms_property_id;
            $pms->update_booking($options, $booking);

        } catch (PmsExceptions $exception) {
            log_exception_by_exception_object($exception, $this->current_record);
        }
    }


    public function getCard(ReadyToFirstAttemptTransaction $record, $amount)
    {

        $card = new Card();

        if (!empty($record->cc_info)) {
            $card->firstName = $record->customer_object->first_name;
            $card->lastName = $record->customer_object->last_name;
            $card->token = $record->customer_object->token;
            $card->amount = $amount;
            $card->currency = $record->currency_code;
            $card->order_id = (int)round(microtime(true) * 1000);

            PaymentGateways::addMetadataInformation($record->booking_info, $card, __CLASS__);
        }


        return $card;
    }


    /**
     * @param ReadyToFirstAttemptTransaction $record
     * @param string $alert_type
     */
    public function notify(ReadyToFirstAttemptTransaction $record, string $alert_type)
    {
        $notify = new NotificationAlerts(0, $record->user_account_id);
        $notify->create($record->booking_info_id, 0, $alert_type, $record->pms_booking_id, 1);
    }
}