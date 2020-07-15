<?php

namespace App\Http\Resources\General\BookingDetail;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;
use App\Traits\Resources\General\BookingDetail;
class GuestExperienceTabResource extends JsonResource
{
    use BookingDetail;
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $time_zone = get_property_time_zone($this->property_info);

        return [

            'arrival_time' => !empty($this->guest_data->arrivaltime) ? $this->guest_data->arrivaltime : '',
            'arriving_by' => !empty($this->guest_data->arriving_by) ? $this->guest_data->arriving_by : '',
            'plane_number' => !empty($this->guest_data->plane_number) ? $this->guest_data->plane_number : '',
            'other_detail' => !empty($this->guest_data->other_detail) ? $this->guest_data->other_detail : '',
            'is_precheckin_completed' => $this->pre_checkin_status == 0 ? false : true,
            'is_confirmation_sent' => $this->pre_checkin_email_status == 0 ? false : true,
            'route' => [
                'pre_checkin'=> URL::signedRoute('step_0', $this->id),
                'guest_portal'=> URL::signedRoute('guest_portal', $this->id),
            ],
            'scans'=> $this->getGuestImages(),
            'visit' => [
                'guest_portal' => $this->last_seen_of_guest != null ? Carbon::parse($this->last_seen_of_guest)->timezone($time_zone)->format('d M Y h:i a') : 'Never visited',
                'precheckin' => $this->last_seen_of_guest != null ? Carbon::parse($this->last_seen_of_guest)->timezone($time_zone)->format('d M Y h:i a') : 'Never visited',
                'guest_portal_class' => 'text-danger',
                'precheckin_class' => 'text-danger'
            ],
        ];
    }
}
