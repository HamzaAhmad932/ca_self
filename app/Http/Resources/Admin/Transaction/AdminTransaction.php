<?php

namespace App\Http\Resources\Admin\Transaction;

use App\CreditCardAuthorization;
use App\TransactionInit;
use App\Unit;
use Carbon\Carbon;

trait AdminTransaction{

    protected $symbol;
    protected $property_timezone;

    protected function getPaymentStatus($payment_status)
    {
        $transaction_payment_status = new \stdClass();

        if ($payment_status == TransactionInit::PAYMENT_STATUS_FAIL) {
            $transaction_payment_status->status = 'Fail';
            $transaction_payment_status->status_class = 'badge-danger';
            $transaction_payment_status->box_class = 'danger';
        } elseif ($payment_status == TransactionInit::PAYMENT_STATUS_SUCCESS) {
            $transaction_payment_status->status = 'Success';
            $transaction_payment_status->status_class = 'badge-success';
            $transaction_payment_status->box_class = 'success';
        } elseif ($payment_status == TransactionInit::PAYMENT_STATUS_PENDING) {
            $transaction_payment_status->status = 'Pending';
            $transaction_payment_status->status_class = 'badge-warning';
            $transaction_payment_status->box_class = 'warning';
        } elseif ($payment_status == TransactionInit::PAYMENT_STATUS_VOID) {
            $transaction_payment_status->status = 'Void';
            $transaction_payment_status->status_class = 'badge-warning';
            $transaction_payment_status->box_class = 'warning';
        } elseif ($payment_status == TransactionInit::PAYMENT_STATUS_REATTEMPT) {
            $transaction_payment_status->status = 'Reattempt';
            $transaction_payment_status->status_class = 'badge-warning';
            $transaction_payment_status->box_class = 'warning';
        } elseif ($payment_status == TransactionInit::PAYMENT_STATUS_WAITING_APPROVAL) {
            $transaction_payment_status->status = 'Waiting Approval';
            $transaction_payment_status->status_class = 'badge-warning';
            $transaction_payment_status->box_class = 'warning';
        } elseif ($payment_status == TransactionInit::PAYMENT_STATUS_PAUSED) {
            $transaction_payment_status->status = 'Paused';
            $transaction_payment_status->status_class = 'badge-success';
            $transaction_payment_status->box_class = 'success';
        } elseif ($payment_status == TransactionInit::PAYMENT_STATUS_MANUALLY_VOID) {
            $transaction_payment_status->status = 'Manually Void';
            $transaction_payment_status->status_class = 'badge-warning';
            $transaction_payment_status->box_class = 'warning';
        } elseif ($payment_status == TransactionInit::PAYMENT_MARKED_AS_PAID) {
            $transaction_payment_status->status = 'Marked As Paid';
            $transaction_payment_status->status_class = 'badge-success';
            $transaction_payment_status->box_class = 'success';
        }
    }

    protected function getLetsProcess($lets_process)
    {
        if ($lets_process == 0) {
            return 'No';
        } elseif ($lets_process == 1) {
            return 'Yes';
        }
    }

    protected function getFinalTick($final_tick)
    {
        if ($final_tick == 0) {
            return 'No';
        } elseif ($final_tick == 1) {
            return 'Yes';
        }
    }

    protected function getType($type)
    {
        if ($type == TransactionInit::TRANSACTION_TYPE_SECURITY_DAMAGE_DEPOSIT_REFUND) {
            return 'Security Damage Deposit Refund';
        } elseif ($type == TransactionInit::TRANSACTION_TYPE_SECURITY_DAMAGE_DEPOSIT_CAPTURE) {
            return 'Security Damage Capture';
        } elseif ($type == TransactionInit::TRANSACTION_TYPE_ADDITIONAL_SECURITY_DAMAGE_CHARGE) {
            return 'Security Damage Deposit';
        } elseif ($type == TransactionInit::TRANSACTION_TYPE_ADDITIONAL_CHARGE) {
            return 'Additional Charge (Charge more)';
        } elseif ($type == TransactionInit::TRANSACTION_TYPE_CHARGE) {
            return 'Charge';
        } elseif ($type == TransactionInit::TRANSACTION_TYPE_REFUND) {
            return 'Refund';
        }
    }

    protected function getStatus($status)
    {
        if ($status == 0) {
            return 'No';
        } elseif ($status == 1) {
            return 'Yes';
        }
    }

    protected function getTransactionType($transaction_type)
    {
        $transactionType = '';
        if ($transaction_type == 1) {
            $transactionType = 'Payment';
        } elseif ($transaction_type == 2) {
            $transactionType = 'Payment #1';
        } elseif ($transaction_type == 3) {
            $transactionType = 'Payment #2';
        } elseif ($transaction_type == 4) {
            $transactionType = 'Refund';
        } elseif ($transaction_type == 5) {
            $transactionType = 'Refund';
        } elseif ($transaction_type == 6) {
            $transactionType = 'Payment (Manual)';
        } elseif ($transaction_type == 8) {
            $transactionType = 'Payment #1 (Manual)';
        } elseif ($transaction_type == 9) {
            $transactionType = 'Payment #2 (Manual)';
        } elseif ($transaction_type == 10) {
            $transactionType = 'Refund (Manual)';
        } elseif ($transaction_type == 11) {
            $transactionType = 'Refund (Manual)';
        } elseif ($transaction_type == 12) {
            $transactionType = 'Auto Security Deposit Collection Full';
        } elseif ($transaction_type == 13) {
            $transactionType = 'Auto Security Deposit Refund Full';
        } elseif ($transaction_type == 14) {
            $transactionType = 'Manual Security Deposit Collection';
        } elseif ($transaction_type == 15) {
            $transactionType = 'Manual Security Deposit Refund';
        } elseif ($transaction_type == 16) {
            $transactionType = 'Manual Security Deposit Refund Partial';
        } elseif ($transaction_type == 17) {
            $transactionType = 'Additional Charge';
        } elseif ($transaction_type == 18) {
            $transactionType = 'Cancellation Fee';
        } elseif ($transaction_type == 23) {
            $transactionType = 'Refund';
        }

        return $transactionType;
    }

    protected function getTransactionProcessStatus($in_processing)
    {
        $process_status = '';
        $check_process_status = config('db_const.transactions_init.type.in_processing');
        if ($in_processing == $check_process_status) {
            $process_status = 'Available';
        } elseif ($in_processing == $check_process_status) {
            $process_status = 'Processing in Queue';
        } elseif ($in_processing == $check_process_status) {
            $process_status = 'Being Processing Manually';
        }

        return $process_status;
    }

    protected function transactionsDetail($transactions_detail)
    {
        $details = array();
        if (!empty($transactions_detail)) {
            foreach ($transactions_detail as $transaction_detail) {
                $data['id'] = $transaction_detail->id;
                $data['transaction_init_id'] = $transaction_detail->transaction_init_id;
                $data['cc_info_id'] = $transaction_detail->cc_info_id;
                $data['user_account_id'] = $transaction_detail->user_account_id;
                $data['name'] = $transaction_detail->name;
                $data['payment_processor_response'] = $transaction_detail->payment_processor_response;
                $data['payment_gateway_form_id'] = $transaction_detail->payment_gateway_form_id;
                $data['payment_gateway_name'] = $transaction_detail->payment_gateway_form->name;
                $data['payment_status'] = $this->transactionDetailPaymentStatus($transaction_detail->payment_status);
                $data['charge_ref_no'] = $transaction_detail->charge_ref_no;
                $data['client_remarks'] = $transaction_detail->client_remarks;
                $data['client_remarks'] = $transaction_detail->client_remarks;
                $data['error_msg'] = $transaction_detail->error_msg;
                $data['order_id'] = $transaction_detail->order_id;
                $data['created_at'] = Carbon::parse($transaction_detail->created_at)->format('M d Y H:i:s');
                $data['updated_at'] = Carbon::parse($transaction_detail->updated_at)->format('M d Y H:i:s');
                $data['amount'] = $transaction_detail->amount;
                array_push($details, $data);
            }
        }

        return $details;
    }

    protected function transactionDetailPaymentStatus($payment_status)
    {
        if ($payment_status == 0) {
            return "Failed";
        } elseif ($payment_status == 1) {
            return "Success";
        }
    }

}
