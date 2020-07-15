<?php

namespace App\Http\Resources\General\BookingDetail;

use App\BookingInfo;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class SentEmailResource extends JsonResource
{

    protected static $property_timezone = 'UTC';

    public static function extraData($booking_info_id)
    {
        $booking_info = BookingInfo::find($booking_info_id);
        if($booking_info) {
            if(isset($booking_info->property_info->time_zone) && !is_null($booking_info->property_info->time_zone))
                static::$property_timezone = $booking_info->property_info->time_zone;
        }
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [

            'email_subject' => !empty($this->email_subject) ? $this->email_subject:'-',
            'sent_to' => !empty($this->sent_to) ? config('db_const.sent_email.sent_to_label.'.$this->sent_to):'-',
            'sent_date_time' => !empty($this->created_at)? Carbon::parse($this->created_at)->timezone(static::$property_timezone)->format('M d, Y \\- h:i A'):'-'
        ];
    }
}
