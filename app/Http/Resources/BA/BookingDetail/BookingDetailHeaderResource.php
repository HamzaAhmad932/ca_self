<?php

namespace App\Http\Resources\BA\BookingDetail;

use Illuminate\Http\Resources\Json\JsonResource;

class BookingDetailHeaderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [

            'booking_info_id' => $this->id,
            'pms_booking_id'=> $this->pms_booking_id,
            'pms_booking_Status'=> array_search ( $this->pms_booking_status, config('db_const.booking_info.pms_booking_status')),
            'previous'=> $this->previous,
            'next'=> $this->next,
            'guest_name'=> $this->guest_name.' '.$this->guest_last_name,
            'id_verification_status'=> '',
            'badge_color'=> config('db_const.booking_info.pms_booking_status_badge_color.'.$this->pms_booking_status),
            'chat_active' => $this->chat_active
        ];
        //return parent::toArray($request);
    }
}
