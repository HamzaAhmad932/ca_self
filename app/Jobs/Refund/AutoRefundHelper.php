<?php


namespace App\Jobs\Refund;


use App\Events\Emails\EmailEvent;
use App\ReadyToRefundTransaction;
use App\RefundAbleTransactionInit;
use App\RefundDetail;
use App\System\PaymentGateway\Exceptions\GatewayException;
use App\System\PaymentGateway\Models\Transaction;
use App\System\PaymentGateway\PaymentGateway;
use App\TransactionDetail;
use App\TransactionInit;
use Exception;
use Illuminate\Support\Facades\Log;

trait AutoRefundHelper
{

    /**
     * @param ReadyToRefundTransaction $record
     */
    public function refundNow(ReadyToRefundTransaction $record)
    {

        if ($this->refundAmountValid($record)) {
            foreach ($record->refund_able_transactions as $refund_able_transaction_inits) {

                if ($record->amount_to_refund == 0)
                    return;

                $this->refundPaidTransaction($record, $refund_able_transaction_inits);
            }
        }
    }


    /**
     * @param ReadyToRefundTransaction $record
     * @param RefundAbleTransactionInit $refund_able_transaction_init
     */
    public function refundPaidTransaction(ReadyToRefundTransaction &$record, RefundAbleTransactionInit $refund_able_transaction_init)
    {
        $transaction_model = $this->getTransactionModel($record, $refund_able_transaction_init);

        try {

            $PG = new PaymentGateway();
            $transaction_model = $PG->refund($transaction_model, $record->user_payment_gateway);

        } catch (GatewayException $e) {
            report($e);
            log_exception_by_exception_object($e);
            $network_failure = $this->networkFailure($e, $record);
            $transaction_model->exceptionMessage = $e->getDescription();

        } catch (Exception $e) {
            log_exception_by_exception_object($e);
            return;

        } finally {

            if (!empty($transaction_model->status)) {
                $this->refundSuccessCase($record, $refund_able_transaction_init, $transaction_model);
            } else {
                $this->refundFailedCase($record, $refund_able_transaction_init, $transaction_model, !empty($network_failure));
            }
        }
    }

    /**
     * @param ReadyToRefundTransaction $record
     * @param RefundAbleTransactionInit $refundable_transaction_init
     * @param Transaction $transaction_model
     */
    public function refundSuccessCase(ReadyToRefundTransaction &$record, RefundAbleTransactionInit $refundable_transaction_init, Transaction $transaction_model)
    {
        $record->amount_to_refund -= $transaction_model->amount;
        $record->transaction_init()->update([
            'lets_process' => 0,
            'final_tick' => 1,
            'attempts' => $record->attempt + 1,
            'payment_status' => TransactionInit::PAYMENT_STATUS_SUCCESS
        ]);

        $detail = $this->refundDetailsEntry($record, $transaction_model, $refundable_transaction_init->charge_ref_no);

        event(new EmailEvent(config('db_const.emails.heads.refund_successful.type'), $detail->id));
    }


    /**
     * @param ReadyToRefundTransaction $record
     * @param RefundAbleTransactionInit $refundable_transaction_init
     * @param Transaction $transaction_model
     * @param bool $network_failure
     */
    public function refundFailedCase(ReadyToRefundTransaction $record, RefundAbleTransactionInit $refundable_transaction_init, Transaction $transaction_model, bool $network_failure = false)
    {

        if (!empty($network_failure)) // Network Fail Try Later
            return;

        $this->setRecordForReAttempt($record);

        $detail = $this->refundDetailsEntry($record, $transaction_model, $refundable_transaction_init->charge_ref_no);

        event(
            new EmailEvent(
                config('db_const.emails.heads.refund_failed.type'),
                $detail->id,
                [
                    'reason' => $transaction_model->exceptionMessage ?? $transaction_model->message,
                    'amount_to_refund' => $transaction_model->amount
                ]
            )
        );
    }


    /**
     * @param GatewayException $e
     * @param ReadyToRefundTransaction $record
     * @return bool
     */
    public function networkFailure(GatewayException $e, ReadyToRefundTransaction $record)
    {
        switch ($e->getCode()) {
            case PaymentGateway::CODE_NETWORK_ERROR_RE_TRY_ABLE :

                $record->transaction_init()->update(['attempts_for_500' => $record->attempts_for_500 + 1]);

                if ($record->transaction_init->attempts_for_500 >= 4) {
                    sendMailToAppDevelopers('Network Failure',
                        $e->getDescription(),
                        json_encode($record->transaction_init, JSON_PRETTY_PRINT));
                }

                return true;
                break;
        }

        return false;
    }


    /**
     * @param ReadyToRefundTransaction $record
     * @return bool
     */
    public function refundAmountValid(ReadyToRefundTransaction $record)
    {

        if ($record->amount_to_refund > $record->refund_able_transactions()->sum('available_amount')) {

            $record->transaction_init()->update(['attempt' => $record->attempt + 1, 'lets_process' => 0]);

            Log::notice("Refund amount is greater than Charged Amount",
                [
                    'Trans ID' => $record->id,
                    'AmountToRefund' => $record->amount_to_refund,
                ]
            );

            return false;
        }

        return true;
    }

    /**
     * @param ReadyToRefundTransaction $record
     * @param RefundAbleTransactionInit $refund_able_transaction_init
     * @return Transaction
     */
    public function getTransactionModel(ReadyToRefundTransaction $record, RefundAbleTransactionInit $refund_able_transaction_init)
    {
        $is_partial = $record->amount_to_refund > $refund_able_transaction_init->available_amount;

        $transaction_model = new Transaction();
        $transaction_model->isPartial = $is_partial;
        $transaction_model->amount = $is_partial ? $refund_able_transaction_init->available_amount : $record->amount_to_refund;
        $transaction_model->description = 'requested_by_customer';
        $transaction_model->token = $refund_able_transaction_init->charge_ref_no;
        $transaction_model->order_id = $refund_able_transaction_init->last_success_trans_obj->order_id;
        $transaction_model->currency_code = $refund_able_transaction_init->last_success_trans_obj->currency_code;

        return $transaction_model;
    }

    /**
     * @param ReadyToRefundTransaction $record
     */
    public function setRecordForReAttempt(ReadyToRefundTransaction $record)
    {
        $attempts_full = $record->attempt >= (TransactionInit::TOTAL_ATTEMPTS - 1);
        $record->transaction_init->update([
            'attempt' => $record->attempt + 1,
            'lets_process' => $attempts_full ? 0 : $record->lets_process,
            'payment_status' => $attempts_full ? TransactionInit::PAYMENT_STATUS_FAIL : $record->payment_status
        ]);

    }


    /**
     * @param ReadyToRefundTransaction $record
     * @param Transaction $response
     * @param string $against_charge_ref_no
     * @return mixed
     */
    public function refundDetailsEntry(ReadyToRefundTransaction $record, Transaction $response, string $against_charge_ref_no = null)
    {
        TransactionDetail::create([
            'cc_info_id' => 0,
            'charge_ref_no' => $response->token,
            'payment_status' => $response->status,
            'transaction_init_id' => $record->id,
            'name' => $record->booking_info->guest_name,
            'payment_processor_response' => json_encode($response),
            'user_account_id' => $record->user_account_id,
            'order_id' => (int)round(microtime(true) * 1000),
            'payment_gateway_form_id' => $record->user_payment_gateway->payment_gateway_form->id,
            'error_msg' => ($response->exceptionMessage != '' ? $response->exceptionMessage : $response->message),
        ]);


        return RefundDetail::create([
            'user_id' => 0,
            'amount' => $response->amount,
            'charge_ref_no' => $response->token,
            'transaction_init_id' => $record->id,
            'name' => $record->booking_info->guest_name,
            'booking_info_id' => $record->booking_info_id,
            'user_account_id' => $record->user_account_id,
            'payment_processor_response' => json_encode($response),
            'payment_status' => $record->transaction_init->status,
            'user_payment_gateway_id' => $record->user_payment_gateway->id,
            'against_charge_ref_no' => $against_charge_ref_no,
            'order_id' => (int)round(microtime(true) * 1000)
        ]);
    }

}