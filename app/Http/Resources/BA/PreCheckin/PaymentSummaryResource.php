<?php

namespace App\Http\Resources\BA\Precheckin;

use App\BookingInfo;
use App\Traits\Resources\General\Booking;
use App\Traits\Resources\General\Precheckin;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentSummaryResource extends JsonResource
{
    use Precheckin;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $booking_info = $this->resource;
        $payments = [];

        $card = $this->getCardInfo($this->resource);
        if ($booking_info->upsellCarts->count() == 0) {
            $payments = collect(
                Booking::getPayments(
                    $booking_info,
                    get_currency_symbol($booking_info->property_info->currency_code),
                    get_property_time_zone($booking_info->property_info)
                )
            )->whereIn('classification', [2, 3]); // Only Declined & Pending
        }

        return [
            'show_payments' => $booking_info->upsellCarts->count() == 0,
            'card' => $card,
            'payments' => $payments
        ];
    }
}
