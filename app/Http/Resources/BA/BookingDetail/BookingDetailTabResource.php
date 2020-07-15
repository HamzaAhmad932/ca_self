<?php

namespace App\Http\Resources\BA\BookingDetail;

use App\Traits\Resources\General\Booking;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingDetailTabResource extends JsonResource
{
    use Booking;
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        $room_info = $this->getRoomInfo($this->resource);
        $time_zone = $this->property_info->time_zone;

        $check_property_image = checkImageExists( $this->property_info->logo, $this->property_info->name, config('db_const.logos_directory.property.value') );
        $property_logo = asset(config('db_const.logos_directory.property.img_path').$check_property_image['property_image']);

        return [
            'pms_booking_id'=>  $this->pms_booking_id,
            'property_heading'=>  $this->property_info->name.', '.$this->property_info->country,
            'property_logo'=> $property_logo,
            'property_initial'=> $check_property_image['property_initial'],
            'property_sub_heading'=> $room_info->sub_heading,
            'source_link'=> '#',
            'source_heading'=> 'Listing Page on '.$this->bookingSourceForm->name,
            'booking_date'=> Carbon::parse($this->booking_time)->timezone($time_zone)->format('M d Y'),
            'channel_reference'=> $this->channel_reference,
            'source'=> $this->bookingSourceForm->name,
            'left_days'=> '4 days left',
            'checkin_date'=> Carbon::parse($this->check_in_date)->timezone($time_zone)->format('M d Y'),
            'checkout_date'=> Carbon::parse($this->check_out_date)->timezone($time_zone)->format('M d Y'),
            'arrival_time'=> !empty($this->guest_data->arrivaltime) ? $this->guest_data->arrivaltime : '',
            'adults'=> !empty($this->guest_data->adults) ? $this->guest_data->adults : '',
            'children'=> !empty($this->guest_data->childern) ? $this->guest_data->childern : '',
            'guest_comments'=> $this->guestComments,
            'internal_notes'=> $this->internal_notes,
            'first_name'=> $this->guest_name,
            'last_name'=> $this->guest_last_name,
            'email'=> $this->guest_email,
            'phone'=> $this->guest_phone
        ];
//        return parent::toArray($request);
    }
}
