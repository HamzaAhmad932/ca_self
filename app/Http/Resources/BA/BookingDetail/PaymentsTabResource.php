<?php

namespace App\Http\Resources\BA\BookingDetail;

use App\Traits\Resources\General\Booking;
use App\Services\CapabilityService;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Traits\Resources\General\BookingDetail;

class PaymentsTabResource extends JsonResource
{
    use Booking;
    use BookingDetail;
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->symbol = get_currency_symbol($this->property_info->currency_code);
        $this->property_timezone = $this->property_info->time_zone;

        $payment_summary = $this->getPaymentSummary($this->transaction_init);
        $capabilities = CapabilityService::allCapabilities($this->resource);
        $payments = collect($this->getPaymentStatus($this->resource));

        return [
            'capabilities'=> $capabilities,
            'payment_summary'=> $payment_summary,
            'is_payment_gateway_found'=> $this->getPaymentGateway(),
            'is_credit_card_available'=> !empty($this->cc_Infos->last()),
            'total_charged'  => $this->getAllChargedAmountSum($this->resource),
            'total_refunded' => $this->getAllRefundedAmountSum($this->resource),
            'total_marked_as_paid' => $this->getAllMarkedAsPaidAmountSum($this),
            'pending_payments'=> $payments->where('classification', 2),
            'declined_payments'=> $payments->where('classification', 3),
            'accepted_payments'=> $payments->where('classification', 1),
            'other_payments'=> $payments->where('classification', 4),
            'cc_infos'=> $this->cc_infos->where('cc_last_4_digit', '!=', '')
        ];
    }
}
