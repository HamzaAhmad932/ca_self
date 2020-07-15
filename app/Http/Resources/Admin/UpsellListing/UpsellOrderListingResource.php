<?php

namespace App\Http\Resources\Admin\UpsellListing;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class UpsellOrderListingResource extends JsonResource
{

    static $config_file = 'upsell_order';

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id , // sprintf("%'.04d", $this->id)
            'pms_booking_id' => $this->bookingInfo->pms_booking_id,
            'booking_info_id' => $this->booking_info_id,
            'final_amount' => $this->final_amount,
            'commission_fee' => $this->commission_fee,
            'currency_code' => $this->bookingInfo->property_info->currency_code,
            'currency_symbol' => get_currency_symbol($this->bookingInfo->property_info->currency_code),
            'due_date' => Carbon::parse($this->created_at, 'GMT')->timezone($this->bookingInfo->property_info->time_zone)->toDayDateTimeString(),
            'charge_ref_no' => $this->charge_ref_no,
            'payment_status' =>  get_config_column_values(self::$config_file, 'status', $this->status),
        ];
    }
}
