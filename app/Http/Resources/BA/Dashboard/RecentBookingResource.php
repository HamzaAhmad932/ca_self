<?php

namespace App\Http\Resources\BA\Dashboard;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Traits\Resources\General\Dashboard;

class RecentBookingResource extends JsonResource
{
    use Dashboard;
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $tz = $this->property_info->time_zone;
        $symbol = get_currency_symbol($this->property_info->currency_code);
        $status = $this->getPaymentStatus($this);

        $logo_available = $this->booking_source['logo'] ? 1 : 0;

        $channel_initial = "";
        $channel_name = "";
        if(!$logo_available)
        {
            $channel_name = $this->booking_source->name;
            $channel_name_exploded = explode(" ", $channel_name);
            
            foreach ($channel_name_exploded as $w) {
              $channel_initial .= $w[0];
            }
        }
        

        return [
            'id'=> $this->id,
            'bs_class'=> $status->bs_class,
            'logo_available' => $logo_available,
            'channel_initial' => $channel_initial,
            'channel_name' => $channel_name,
            'bs_img_url'=> '/storage/uploads/booking_souce_logo/'.$this->booking_source['logo'],
            'pms_booking_id'=> $this->pms_booking_id,
            'guest_name'=> $this->guest_name,
            'guest_last_name'=> $this->guest_last_name,
            'symbol'=> $symbol,
            'total_amount'=> $this->total_amount,
            'check_in_date'=> Carbon::parse($this->check_in_date)->timezone($tz)->format('d M'),
            'check_out_date'=> Carbon::parse($this->check_out_date)->timezone($tz)->format('d M'),
            'booking_status'=> $status->booking_status,
            'room_name'=> $this->room_info->name
        ];
    }
}
