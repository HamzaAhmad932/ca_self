<?php

namespace App\Repositories\Bookings;

use Carbon\Carbon;
use App\BookingInfo;
use App\RefundDetail;
use App\TransactionInit;
use App\TransactionDetail;
use App\UserPaymentGateway;
use App\Exceptions\RefundException;
use App\System\PaymentGateway\PaymentGateway;
use App\Repositories\Settings\PaymentTypeMeta;
use App\System\PaymentGateway\Models\Transaction;
use App\System\PaymentGateway\Exceptions\GatewayException;
use Illuminate\Support\Facades\Log;

class BaRefund
{
    private $refundedTransaction;
    private $sddFlag;
    private $autoFlag;
    private $somethingRefunded;

    public function __construct($auto=false){
        $this->refundedTransaction = null;
        $this->sddFlag = false;
        $this->autoFlag = $auto;
        $this->somethingRefunded = false; //By default somethingRefunded = false
    }

    public function refundAmount(BookingInfo $bookingInfo, $amountToRefund, UserPaymentGateway $userPaymentGateway, $description = '', $transaction_id = 0){

            $amountToRefund = abs($amountToRefund);
            $transaction_init = TransactionInit::where('booking_info_id', $bookingInfo->id)
            ->whereIn('type',TransactionInit::$charge_type_transactions)
            ->where('payment_status', '1')
            ->get();

            if ($transaction_id != 0) {
                $transaction_type = $transaction_init->where('id', $transaction_id)->pluck('type')->first();
                $this->sddFlag = $transaction_type == 'CS' ? true : false;
            }

            $alreadyRefundedAmount = TransactionInit::where('booking_info_id', $bookingInfo->id)
            ->whereIn('type', TransactionInit::$refund_type_transactions)->where('payment_status', '1')->get();

            $totalPaidAmount =$transaction_init->sum('price');
            $refunded = $alreadyRefundedAmount->sum('price');
            Log::notice("Total Paid => $totalPaidAmount | Total Refund => $refunded  | Amount to Refund $amountToRefund");

            /*_________ Already somethingRefunded which will help in auto cancellation refund Entries _____________*/
            if($refunded > 0){
                $this->somethingRefunded = true; //For auto Bookingcancellation Refund  
            }

            if($amountToRefund <= ($totalPaidAmount - $refunded)) {
                $splitResp = $this->refundFromMultipleTransactions($amountToRefund, $transaction_init, $userPaymentGateway, $bookingInfo, $description);
                    return $splitResp;
            } else {
                throw new RefundException('Refund amount is greater than Charged Amount.'); /**422 unprocessable entity */
            }
    }

    public function refundAmountSDD(BookingInfo $bookingInfo, $amountToRefund, UserPaymentGateway $userPaymentGateway) {

        $amountToRefund = abs($amountToRefund);
        $totalPaidAmount = 0;
        $transaction_init = TransactionInit::where('booking_info_id', $bookingInfo->id)
        ->whereIn('type', ['S', 'CS'])
        ->where('payment_status', '1')
        // ->orderBy('price', 'DSC')
        ->get();
        $alreadyRefundedAmount = TransactionInit::where('booking_info_id', $bookingInfo->id)
        ->where('type', 'SR')
        ->where('payment_status', '1')
        ->get();

        $totalPaidAmount =$transaction_init->sum('price');
        $refunded = $alreadyRefundedAmount->sum('price');

        if($amountToRefund <= $totalPaidAmount - $refunded){
            $this->sddFlag = true;
            $splitResp = $this->refundFromMultipleTransactions($amountToRefund, $transaction_init, $userPaymentGateway, $bookingInfo);
            return $splitResp;
        } else {
            throw new RefundException('Refund amount is greater than Booking Amount.'); /**422 unprocessable entity */
        }
    }

    public function refundFromSingleTransaction($amountToRefund, $transaction_init, $userPaymentGateway, $bookingInfo) {
        
        try {
                $trans = new Transaction();
                try{

                    $transToProcess = new Transaction();
                    $transToProcess->token = $transaction_init->charge_ref_no;
                    $transToProcess->amount = $amountToRefund;
                    $transToProcess->currency_code = $transaction_init->last_success_trans_obj->currency_code;
                    $transToProcess->order_id = $transaction_init->last_success_trans_obj->order_id;
                    $transToProcess->isPartial = true;

                    $pg = new PaymentGateway();
                    $trans = $pg->refund($transToProcess, $userPaymentGateway);
    
                } catch (GatewayException $e) {
                    report($e);
                    return $e;
                }
                if($trans->status){
                    $paymentTypeMeta = new PaymentTypeMeta();
                    $manualRefundFullTransId = $paymentTypeMeta->getBookingPaymentManualRefundFull();
                    if($this->refundedTransaction == null) {
                        $this->refundedTransaction = $this->transactionInitTransactionDetailEntry($transaction_init, $manualRefundFullTransId, $trans, $userPaymentGateway, $bookingInfo, $amountToRefund);
                    }
                    $this->refundDetailsEntry($transaction_init, $manualRefundFullTransId, $trans, $userPaymentGateway, $bookingInfo);  
                }else{
                    throw new RefundException($trans->message, 422); /** 406 not accepted */
                }
    
                /**
                 * Response is to be decided
                 */
                
                return $trans;

        } catch (Exception $e) {
            return $e;
        }
    }

    public function refundFromMultipleTransactions($amountToRefund, $transactions, $userPaymentGateway, $bookingInfo, $description = '') {
        $trans = new Transaction();
        try{

        $helperAmount = $amountToRefund;

        foreach($transactions as $tran) {

            $refunded = RefundDetail::where('against_charge_ref_no', $tran->charge_ref_no)->sum('amount');
            $tran->price =($tran->price  - $refunded);

            if($helperAmount == $tran->price && $helperAmount > 0){
                try {
                    $trans = $this->refundFromSingleTransaction($helperAmount, $tran, $userPaymentGateway, $bookingInfo);
                    break;                   
                    } catch (RefundException $e) {
                        continue;
                    } catch (GatewayException $e) {
                        report($e);
                        return $e;
                    }

            }

            
            if($tran->price < $helperAmount && ($tran->price > 0)) {
                $transToProcess = new Transaction();
                $transToProcess->token = $tran->charge_ref_no;
                $transToProcess->amount = $tran->price;
                $transToProcess->currency_code = $tran->last_success_trans_obj->currency_code;
                $transToProcess->order_id = $tran->last_success_trans_obj->order_id;
                $transToProcess->isPartial = true;

                try{
                    $pg = new PaymentGateway();
                    $trans = $pg->refund($transToProcess, $userPaymentGateway);
        
                } catch (GatewayException $e) {
                    report($e);
                    return $e;
                }

                if($trans->status) {
                    $paymentTypeMeta = new PaymentTypeMeta();
                    $manualRefundFullTransId = $paymentTypeMeta->getBookingPaymentManualRefundFull();
                    if($this->refundedTransaction == null) {
                        $this->refundedTransaction = $this->transactionInitTransactionDetailEntry($tran, $manualRefundFullTransId, $trans, $userPaymentGateway, $bookingInfo, $helperAmount, $description);
                    }
                    $this->refundDetailsEntry($tran, $manualRefundFullTransId, $trans, $userPaymentGateway, $bookingInfo, $description);
                    $helperAmount -= $tran->price;
                } 
                continue;
            }
            if($helperAmount < $tran->price && $helperAmount > 0  ) {
                $transToProcess = new Transaction();

                $transToProcess->token = $tran->charge_ref_no;
                // if($refunded == 0){
                //     $transToProcess->amount = $helperAmount;
                // }else{
                //     $transToProcess->amount = $tran->price - $refunded;
                // }
                $transToProcess->amount = $helperAmount;
                $transToProcess->currency_code = $tran->last_success_trans_obj->currency_code;
                $transToProcess->order_id = $tran->last_success_trans_obj->order_id;
                $transToProcess->isPartial = true;
                try{
                    $pg = new PaymentGateway();
                    $trans = $pg->refund($transToProcess, $userPaymentGateway);
        
                } catch (GatewayException $e) {
                    report($e);
                    return $e;
                }
                
                if($trans->status) {
                    $paymentTypeMeta = new PaymentTypeMeta();
                    $manualRefundFullTransId = $paymentTypeMeta->getBookingPaymentManualRefundFull();
                    if($this->refundedTransaction == null) {
                        $this->refundedTransaction = $this->transactionInitTransactionDetailEntry($tran, $manualRefundFullTransId, $trans, $userPaymentGateway, $bookingInfo, $helperAmount, $description);
                    }
                    $this->refundDetailsEntry($tran, $manualRefundFullTransId, $trans, $userPaymentGateway, $bookingInfo, $description);
                    $helperAmount -= $transToProcess->amount;
                }else{
                    continue;
                }
                
            }
        }
        
        /**
         * Response is to be decided
         */
        if($trans->status){
            $response = [
                'transaction'=> $trans, 
                'status'=> true
            ];    
            return $response;
        }else{
            $response = [
                'transaction'=> $trans, 
                'status'=> false
            ];    
            return $response;
        }

        
        }
        catch(Exception $e) {
            return $e;
        }

    }

    public function refundDetailsEntry(TransactionInit $transaction_init, $manualRefundId, $trans, $userPaymentGateway, $bookingInfo, $description = '') {
        $refundDetail = new RefundDetail();
        $refundDetail->transaction_init_id = $this->refundedTransaction->id;
        $refundDetail->booking_info_id = $bookingInfo->id;
        $refundDetail->user_id = $this->refundedTransaction->user_id;
        $refundDetail->user_account_id = $this->refundedTransaction->user_account_id;
        $refundDetail->name = $bookingInfo->guest_name;
        $refundDetail->payment_processor_response = json_encode($trans);
        $refundDetail->user_payment_gateway_id = $userPaymentGateway->id;
        $refundDetail->payment_status = $trans->status;
        $refundDetail->charge_ref_no = $trans->token;
        $refundDetail->against_charge_ref_no = $transaction_init->charge_ref_no;
        $refundDetail->amount = $trans->amount;
        $refundDetail->client_remarks = $description;
        $refundDetail->order_id = (int) round(microtime(true) * 1000);
        $refundDetail->save();
        
        return $refundDetail;
    }

    public function transactionInitTransactionDetailEntry(TransactionInit $transaction_init, $manualRefundId, $trans, $userPaymentGateway, $bookingInfo, $amount, $description = '') {
    try{
        $transactionInit = new TransactionInit();
        $transactionInit->booking_info_id = $bookingInfo->id;
        $transactionInit->pms_id = $bookingInfo->pms_id;
        $transactionInit->due_date = Carbon::now()->toDateTimeString(); //Carbon::now()->toDateString();
        $transactionInit->price = $amount;
        if($trans->status){
            $transactionInit->payment_status = TransactionInit::PAYMENT_STATUS_SUCCESS;  
        }else{
            $transactionInit->payment_status = TransactionInit::PAYMENT_STATUS_FAIL;
        }  
        $transactionInit->user_id = $transaction_init->user_id;
        $transactionInit->user_account_id = $transaction_init->user_account_id;
        // $transactionInit->charge_ref_no = $trans->token;
        // $transactionInit->last_success_trans_obj = $trans;
        $transactionInit->lets_process = 0;
        $transactionInit->final_tick = 1;
        $transactionInit->split = 1;
        // $transactionInit->against_charge_ref_no = 
        if($this->sddFlag){
            $paymentTypeMeta = new PaymentTypeMeta();
            $manualRefundSDDId = $paymentTypeMeta->getSecurityDepositManualRefundFull();

            $transactionInit->type = 'SR';
            $transactionInit->transaction_type = $manualRefundSDDId;
        }
        else if($this->autoFlag){
            $paymentTypeMeta = new PaymentTypeMeta();
            $autoBookingRefundid = $paymentTypeMeta->getBookingPaymentAutoRefundFull();

            $transactionInit->type = 'R';
            $transactionInit->transaction_type = $autoBookingRefundid;
        }
        else{
            $transactionInit->type = 'R';
            $transactionInit->transaction_type = $manualRefundId;
        }   
        $transactionInit->status = 1;
        // $transactionInit->client_remarks = $request->data['description'];//
        // $transactionInit->against_charge_ref_no = $transaction_init->charge_ref_no;
        $transactionInit->next_attempt_time = Carbon::now()->toDateTimeString();//Carbon::now()->toDateString();
        $transactionInit->attempt = 1; //
        $transactionInit->client_remarks = $description; //

        $transaction = $transactionInit->save();

        /**
         * Transaction Details Entry
         */

        $transactionDetail = new TransactionDetail();
        $transactionDetail->transaction_init_id = $transactionInit->id;
        // $transactionDetail->cc_info_id = $transaction_init->transactions_detail->where('charge_ref_no', $transaction_init->charge_ref_no)->first()->cc_info_id;
        $transactionDetail->cc_info_id = 0;
        $transactionDetail->user_id = $transactionInit->user_id;
        $transactionDetail->user_account_id = $transactionInit->user_account_id;
        $transactionDetail->name = $bookingInfo->guest_name;
        $transactionDetail->payment_processor_response = json_encode($trans);
        $transactionDetail->payment_gateway_form_id = $userPaymentGateway->payment_gateway_form->id;
        $transactionDetail->payment_status = $trans->status;
        $transactionDetail->charge_ref_no = $trans->token;
         $transactionDetail->client_remarks = $description;
        $transactionDetail->order_id = (int) round(microtime(true) * 1000);
        $transactionDetail->error_msg = ($trans->exceptionMessage != '' ? $trans->exceptionMessage : $trans->message); 
        $transactionDetail->save();

        return $transactionInit;

    }catch(Exception $e){
        return $e;
    }
    }
}