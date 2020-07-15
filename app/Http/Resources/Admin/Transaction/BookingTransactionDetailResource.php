<?php

namespace App\Http\Resources\Admin\Transaction;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingTransactionDetailResource extends JsonResource
{
    use AdminTransaction;
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
//        dd($this->transactions_detail->payment_gateway_form);
        return [

            "id" => $this->id,
            "booking_info_id" => $this->booking_info_id,
            "pms_id" => $this->pms_id,
            "pms_name" => $this->pmsForm->name,
            "due_date" => Carbon::parse($this->due_date)->format('M d Y H:i:s'),
            "next_attempt_time" => Carbon::parse($this->next_attempt_time)->format('M d Y H:i:s'),
            "update_attempt_time" => Carbon::parse($this->update_attempt_time)->format('M d Y H:i:s'),
            "price" => $this->price,
            "is_modified" => $this->is_modified,
            "payment_status" => $this->getPaymentStatus($this->payment_status),
            "user_account_id" => $this->user_account_id,
            "user_account_name" => $this->user_account->name,
            "charge_ref_no" => $this->charge_ref_no,
            "lets_process" => $this->getLetsProcess($this->lets_process),
            "final_tick" => $this->getFinalTick($this->final_tick),
            "system_remarks" => $this->system_remarks,
            "split" => $this->split,
            "against_charge_ref_no" => $this->against_charge_ref_no,
            "type" => $this->getType($this->type),
            "status" => $this->getStatus($this->status),
            "transaction_type" => $this->getTransactionType($this->transaction_type),
            "client_remarks" => $this->client_remarks,
            "auth_token" => $this->auth_token,
            "error_code_id" => $this->error_code_id,
            "attempt" => $this->attempt,
            "attempts_for_500" => $this->attempts_for_500,
            "decline_email_sent" => $this->decline_email_sent,
            "remarks" => $this->remarks,
            "created_at" => Carbon::parse($this->created_at)->format('M d Y H:i:s'),
            "updated_at" => Carbon::parse($this->updated_at)->format('M d Y H:i:s'),
            "payment_intent_id" => $this->payment_intent_id,
            "in_processing" => $this->getTransactionProcessStatus($this->in_processing),
            "transactions_detail" => $this->transactionsDetail($this->transactions_detail),
        ];
    }
}
