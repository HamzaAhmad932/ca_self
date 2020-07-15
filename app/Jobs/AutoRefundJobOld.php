<?php

namespace App\Jobs;

use App\Events\Emails\EmailEvent;
use App\Events\SendEmailEvent;
use Exception;
use App\BookingInfo;
use App\PropertyInfo;
use App\RefundDetail;
use App\CreditCardInfo;
use App\TransactionInit;
use App\Mail\GenericEmail;
use App\TransactionDetail;
use App\UserPaymentGateway;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Carbon;
use App\Exceptions\RefundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\System\PaymentGateway\PaymentGateway;
use App\Repositories\Settings\PaymentTypeMeta;
use App\System\PaymentGateway\Models\Transaction;
use App\System\PaymentGateway\Exceptions\GatewayException;

class AutoRefundJobOld implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $refund_detail_id = null;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        self::onQueue('auto_refund');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->autoRefund();
    }

    private function autoRefund() {

        $ptm = new PaymentTypeMeta();

        $trans = TransactionInit::where('type', 'R')
            ->where('payment_status', TransactionInit::PAYMENT_STATUS_PENDING)
            ->where('lets_process', '1')
            ->where('final_tick', '0')
            ->where('transaction_type', $ptm->getAutoRefund())
            ->where('attempt', '<=', TransactionInit::TOTAL_ATTEMPTS)
            ->orderBy('id', 'asc')->take(5)->get();

        /**
         * @var $transaction_init TransactionInit
         */
        foreach($trans as $transaction_init) {

            try {

                $propertyInfo = PropertyInfo::where('pms_property_id', $transaction_init->booking_info->property_id)->first();
                $isUserPGGlobal = ($propertyInfo->use_pg_settings == 0) ? true : false;

                if ($isUserPGGlobal) {
                    $userPaymentGateway = UserPaymentGateway::where('user_account_id', $transaction_init->user_account_id)->where('property_info_id', '0')->first();

                } else {
                    $userPaymentGateway = UserPaymentGateway::where('user_account_id', $transaction_init->user_account_id)->where('property_info_id', $propertyInfo->id)->first();
                }

                $resp = $this->refundAmount($transaction_init->booking_info, $transaction_init->price, $userPaymentGateway, $transaction_init);

                if (!empty($resp->status)) {
                    $transaction_init->lets_process = '0';
                    $transaction_init->payment_status = '1';
                    $transaction_init->save();

                    if (!is_null($this->refund_detail_id)) {
                        // To Both client and Guest
                        event(new EmailEvent(config('db_const.emails.heads.refund_successful.type'), $this->refund_detail_id));
                    }
                } else {

                    $transaction_init->attempt = $transaction_init->attempt + 1;

                    $transaction_init->lets_process = $transaction_init->attempt < TransactionInit::TOTAL_ATTEMPTS
                        ? $transaction_init->lets_process : 0;

                    $transaction_init->payment_status = $transaction_init->attempt >= TransactionInit::TOTAL_ATTEMPTS
                        ? TransactionInit::PAYMENT_STATUS_FAIL
                        : $transaction_init->payment_status;

                    $transaction_init->save();

                    Log::notice('Auto Refund failed after total attempts', ['TransactionInit' => $transaction_init->id]);
                }
            } catch (Exception $e) {
                Log::error($e->getMessage(), [
                    'File' => AutoRefundJobOld::class,
                    'Function' => __FUNCTION__,
                    'TransactionInit' => $transaction_init->id,
                    'BookingInfoId' => $transaction_init->booking_info_id
                ]);
            }
        }
    }

    /**
     * @param BookingInfo $bookingInfo
     * @param $amountToRefund
     * @param UserPaymentGateway $userPaymentGateway
     * @param $unprocessedTrans
     * @return Transaction|null
     */
    private function refundAmount(BookingInfo $bookingInfo, $amountToRefund, UserPaymentGateway $userPaymentGateway, $unprocessedTrans) {

            $amountToRefund = abs($amountToRefund);

            $transaction_init = TransactionInit::where('booking_info_id', $bookingInfo->id)->whereIn('type', ['C', 'M'])
            ->where('payment_status', '1')->get();
            // $alreadyRefundedAmount = RefundDetail::where('against_charge_ref_no', $tran->charge_ref_no)->sum('amount');
            $alreadyRefundedAmount = TransactionInit::where('booking_info_id', $bookingInfo->id)->where('type', 'R')
            ->where('payment_status', '1')->get();

            $totalPaidAmount = $transaction_init->sum('price');
            $refunded = $alreadyRefundedAmount->sum('price');

            if($amountToRefund <= $totalPaidAmount - $refunded) {

                $splitResp = $this->refundFromMultipleTransactions($amountToRefund, $transaction_init, $userPaymentGateway, $bookingInfo, $unprocessedTrans);
                return $splitResp;

            } else {

                $unprocessedTrans->attempt += 1;
                $unprocessedTrans->lets_process = 0;
                $unprocessedTrans->save();

                Log::notice("Refund amount is greater than Charged Amount. (Suggestion => Need to Fix and Refactor Business Logic)",
                    [
                        'file'=>'AutoRefundJobOld.php',
                        'BookingInfoId:' => $bookingInfo->id,
                        'Booking PMS ID' => $bookingInfo->pms_booking_id,
                        'AmountToRefund' => $amountToRefund,
                        'TotalPaidAmount - Refunded = ' => $totalPaidAmount . ' - ' . $refunded . ' = ' . ($totalPaidAmount - $refunded)
                    ]);

                return null;
            }
    }

    /**
     * @param $amountToRefund
     * @param $transactions
     * @param $userPaymentGateway
     * @param $bookingInfo
     * @param $unprocessedTrans
     * @return Transaction|null
     */
    private function refundFromMultipleTransactions($amountToRefund, $transactions, $userPaymentGateway, $bookingInfo, $unprocessedTrans) {

        $trans = new Transaction();

        try {

        $helperAmount = $amountToRefund;

        foreach($transactions as $tran) {

            $refunded = RefundDetail::where('against_charge_ref_no', $tran->charge_ref_no)
                ->where('payment_status', '!=', 0)
                ->sum('amount');

            $tran->price = ($tran->price - $refunded);

            if($helperAmount == $tran->price && $helperAmount > 0 && $tran->price > 0) {
                $trans = $this->refundFromSingleTransaction($helperAmount, $tran, $userPaymentGateway, $bookingInfo, $unprocessedTrans);
                break;
            }

            
            if($tran->price < $helperAmount && ($tran->price > 0)) {
                $transToProcess = new Transaction();
                $transToProcess->token = $tran->charge_ref_no;
                $transToProcess->amount = $tran->price;
                $transToProcess->currency_code = $tran->last_success_trans_obj->currency_code;
                $transToProcess->order_id = $tran->last_success_trans_obj->order_id;
                $transToProcess->isPartial = true;
                $transToProcess->description = 'requested_by_customer';

                try {

                    $pg = new PaymentGateway();
                    $trans = $pg->refund($transToProcess, $userPaymentGateway);
        
                } catch (GatewayException $e) {
                    report($e);
                    Log::error($e->getMessage() . ' 2', [
                        'file'=>'AutoRefundJobOld.php',
                        'BookingInfoId:' => $bookingInfo->id,
                        'Booking PMS ID' => $bookingInfo->pms_booking_id,
                        'AmountToRefund' => $amountToRefund
                    ]);

                    event(new EmailEvent(config('db_const.emails.heads.refund_failed.type'), $tran->id, [ 'reason' => $e->getDescription(), 'amount_to_refund' => $tran->price ]));


                }

                if($trans->status) {
                    $paymentTypeMeta = new PaymentTypeMeta();
                    $manualRefundFullTransId = $paymentTypeMeta->getAutoRefund();
                    
                    $this->refundDetailsEntry($tran, $manualRefundFullTransId, $trans, $userPaymentGateway, $bookingInfo, $unprocessedTrans);
                    $helperAmount -= $tran->price;
                } else {
                    event(new EmailEvent(config('db_const.emails.heads.refund_failed.type'), $tran->id, [ 'reason' => $trans->message, 'amount_to_refund' => $amountToRefund ]));
                }
                continue;
            }

            if($helperAmount <= $tran->price && $helperAmount > 0  ) {
                $transToProcess = new Transaction();

                $transToProcess->token = $tran->charge_ref_no;
                $transToProcess->amount = $helperAmount;
                $transToProcess->currency_code = $tran->last_success_trans_obj->currency_code;
                $transToProcess->order_id = $tran->last_success_trans_obj->order_id;
                $transToProcess->isPartial = true;
                $transToProcess->description = 'requested_by_customer';

                try{
                    $pg = new PaymentGateway();
                    $trans = $pg->refund($transToProcess, $userPaymentGateway);
        
                } catch (GatewayException $e) {
                    report($e);
                    Log::error($e->getMessage() . ' 3', [
                        'file'=>'AutoRefundJobOld.php',
                        'BookingInfoId:' => $bookingInfo->id,
                        'Booking PMS ID' => $bookingInfo->pms_booking_id,
                        'AmountToRefund' => $helperAmount
                    ]);

                    event(new EmailEvent(config('db_const.emails.heads.refund_failed.type'), $tran->id, [ 'reason' => $e->getDescription(), 'amount_to_refund' => $helperAmount ] ));
                }

                $unprocessedTrans->attempt += 1;
                $paymentTypeMeta = new PaymentTypeMeta();
                $manualRefundFullTransId = $paymentTypeMeta->getAutoRefund();

                if($trans->status) {
                    $helperAmount -= $transToProcess->amount;
                    $unprocessedTrans->save();
                    $this->refundDetailsEntry($tran, $manualRefundFullTransId, $trans, $userPaymentGateway, $bookingInfo, $unprocessedTrans);

                } else {
                    if($unprocessedTrans->attempt >= 4) {
                        $unprocessedTrans->payment_status = TransactionInit::PAYMENT_STATUS_FAIL;
                        $unprocessedTrans->lets_process = 0;
                    }

                    event(new EmailEvent(config('db_const.emails.heads.refund_failed.type'), $tran->id, [ 'reason' => $e->getDescription(), 'amount_to_refund' => $helperAmount ] ));


                    $unprocessedTrans->save();
                    $this->refundDetailsEntry($tran, $manualRefundFullTransId, $trans, $userPaymentGateway, $bookingInfo, $unprocessedTrans);
                    continue;
                }
                
            }
        }
        
        /**
         * Response is to be decided
         */

        return $trans;

        
        }
        catch(Exception $e) {
            Log::error($e->getMessage(), [
                'File' => AutoRefundJobOld::class,
                'Function' => __FUNCTION__,
                'BookingInfoId' => $bookingInfo->id
            ]);
        }

    }

    private function refundFromSingleTransaction($amountToRefund, $transaction_init, $userPaymentGateway, $bookingInfo, $unprocessedTrans) {

        try {

            $trans = new Transaction();

            try {

                $transToProcess = new Transaction();
                $transToProcess->token = $transaction_init->charge_ref_no;
                $transToProcess->amount = $amountToRefund;
                $transToProcess->currency_code = $transaction_init->last_success_trans_obj->currency_code;
                $transToProcess->order_id = $transaction_init->last_success_trans_obj->order_id;
                $transToProcess->isPartial = true;
                $transToProcess->description = 'requested_by_customer';

                $pg = new PaymentGateway();
                $trans = $pg->refund($transToProcess, $userPaymentGateway);

            } catch (GatewayException $e) {

                report($e);

                Log::error($e->getMessage() . ' 5', [
                    'file'=>'AutoRefundJobOld.php',
                    'BookingInfoId:' => $bookingInfo->id,
                    'Booking PMS ID' => $bookingInfo->pms_booking_id,
                    'AmountToRefund' => $amountToRefund
                ]);

                event(new EmailEvent(config('db_const.emails.heads.refund_failed.type'), $transaction_init->id, [ 'reason' => $e->getDescription(), 'amount_to_refund' => $amountToRefund ]));

                try {
                    if ($e->getCode() == PaymentGateway::CODE_NETWORK_ERROR_RE_TRY_ABLE) {
                        $transaction_init->attempts_for_500 += 1;
                        $transaction_init->save();
                        if ($transaction_init->attempts_for_500 == 4) {
                            sendMailToAppDevelopers('Network Failure', $e->getMessage(), json_encode($transaction_init, JSON_PRETTY_PRINT));
                        }
                    }

                } catch (Exception $e) {
                    Log::error($e->getMessage() . ' 6', [
                        'File' => __FILE__,
                        'function' => __FUNCTION__,
                        'stack' => $e->getTraceAsString()
                    ]);
                }
            }

            $paymentTypeMeta = new PaymentTypeMeta();
            $manualRefundFullTransId = $paymentTypeMeta->getAutoRefund();
            $unprocessedTrans->attempt += 1;

            if(!$trans->status) {

                Log::error($trans->message . ' 7');

                event(new EmailEvent(config('db_const.emails.heads.refund_failed.type'), $transaction_init->id, [ 'reason' => $trans->message, 'amount_to_refund' => $amountToRefund ]));

                if($unprocessedTrans->attempt >= 4) {
                    $unprocessedTrans->payment_status = TransactionInit::PAYMENT_STATUS_FAIL;
                    $unprocessedTrans->lets_process = 0;
                }

            } else {
                $unprocessedTrans->lets_process = 0;
            }

            $unprocessedTrans->save();
            $this->refundDetailsEntry($transaction_init, $manualRefundFullTransId, $trans, $userPaymentGateway, $bookingInfo, $unprocessedTrans);

            /**
             * Response is to be decided
             */

            return $trans;

        } catch (Exception $e) {
            Log::error($e->getMessage(), [
                'File' => AutoRefundJobOld::class,
                'Function' => __FUNCTION__,
                'TransactionInit' => $transaction_init->id,
                'BookingInfoId' => $transaction_init->booking_info_id
            ]);
        }
    }

    private function refundDetailsEntry(TransactionInit $transaction_init, $manualRefundId, $trans, $userPaymentGateway, $bookingInfo, $unprocessedTrans) {

        /*
        |--------------------------------------------------
        |   Refund Details Entry
        |--------------------------------------------------
        |
        */
        $refundDetail = new RefundDetail();
        $refundDetail->transaction_init_id = $unprocessedTrans->id;
        $refundDetail->booking_info_id = $bookingInfo->id;
        $refundDetail->user_id = $transaction_init->user_id;
        $refundDetail->user_account_id = $transaction_init->user_account_id;
        $refundDetail->name = $bookingInfo->guest_name;
        $refundDetail->payment_processor_response = json_encode($trans);
        $refundDetail->user_payment_gateway_id = $userPaymentGateway->id;
        $refundDetail->payment_status = $trans->status;
        $refundDetail->charge_ref_no = $trans->token;
        $refundDetail->against_charge_ref_no = $transaction_init->charge_ref_no;
        $refundDetail->amount = $trans->amount;
        $refundDetail->order_id = (int) round(microtime(true) * 1000);
        $refundDetail->save();

        //save id to pass to email event
        if($refundDetail)
            $this->refund_detail_id = $refundDetail->id;

        /*
        |--------------------------------------------------
        |   Transaction Details Entry
        |--------------------------------------------------
        |
        */

        $transactionDetail = new TransactionDetail();
        $transactionDetail->transaction_init_id = $unprocessedTrans->id;
        // $transactionDetail->cc_info_id = $transaction_init->transactions_detail->where('charge_ref_no', $transaction_init->charge_ref_no)->first()->cc_info_id;
        $transactionDetail->cc_info_id = 0;
        /** User ID not required in case of auto refund*/
        //$transactionDetail->user_id = $transaction_init->user_id;

        $transactionDetail->user_account_id = $transaction_init->user_account_id;
        $transactionDetail->name = $bookingInfo->guest_name;
        $transactionDetail->payment_processor_response = json_encode($trans);
        $transactionDetail->payment_gateway_form_id = $userPaymentGateway->payment_gateway_form->id;
        $transactionDetail->payment_status = $trans->status;
        $transactionDetail->charge_ref_no = $trans->token;
        // $transactionDetail->client_remarks = $request->data['description'];
        $transactionDetail->order_id = (int) round(microtime(true) * 1000);
        $transactionDetail->error_msg = ($trans->exceptionMessage != '' ? $trans->exceptionMessage : $trans->message);
        $transactionDetail->save();
        
        return $refundDetail;
    }

}
