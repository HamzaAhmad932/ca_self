<?php

namespace App\Http\Resources\General\BookingDetail;

use App\CaCapability;
use App\Traits\Resources\General\Booking;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
{
    use Booking;
    public static $bookingsChannelsCapabilities = [];
    public static $paymentTypeMetaAuto = [];
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        //return parent::toArray($request);
        $this->symbol = get_currency_symbol($this->property_info->currency_code);
        $this->property_timezone = $this->property_info->time_zone;

        $capabilities = $this->getCapabilities($this->resource);
        /*$payment_status = $this->is_process_able
            ? ((!$capabilities[CaCapability::AUTO_PAYMENTS]
                //&& !$capabilities[CaCapability::MANUAL_PAYMENTS]
                && !$capabilities[CaCapability::SECURITY_DEPOSIT])
                ? $this->getOthersTypeBookingFlag($this->resource)
                : $this->checkPriorityFlagForTransactions($this->resource)
            ) : $this->getOthersTypeBookingFlag($this->resource);*/

        if ($this->is_process_able == 0
            || (!$capabilities[CaCapability::AUTO_PAYMENTS] && !$capabilities[CaCapability::SECURITY_DEPOSIT])) {
            $payment_status = $this->getOthersTypeBookingFlag($this->resource);
        } else {
            $payment_status = $this->checkPriorityFlagForTransactions($this->resource);
        }

        return [
            'activity_log'=> $this->getPaymentActivityLog(),
            'payment_status'=> $payment_status
        ];

    }
}
