<?php


namespace App\Http\Resources\BA\Precheckin;


use App\Traits\Resources\General\Precheckin;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Config;

class StepZeroResource extends JsonResource
{
    use Precheckin;

    public function toArray($request)
    {
        $property = $this->property_info;
        $guest_data = $this->guest_data;
        $symbol = get_currency_symbol($property->currency_code);

        return [
            'guest_name' => $this->guest_name,
            'amount' => $symbol . number_format($this->total_amount, 2),
            'checkin_date' => Carbon::parse($this->check_in_date)->timezone($property->time_zone)->format('M j, Y'),
            'checkout_date' => Carbon::parse($this->check_out_date)->timezone($property->time_zone)->format('M j, Y'),
            'reference' => $this->pms_booking_id,
            'arrival_time' => !empty($guest_data->arrivaltime) ? $guest_data->arrivaltime : '--',
            'guest' => !empty($guest_data->adults) ? $guest_data->adults . '(' . $guest_data->childern . ' child)' : '--',
            'pre_step' => route('step_0', ['id' => $this->id]),
            'next_step' => route('step_1', ['id' => $this->id]),
            'next_link' => '',
            'is_completed' => false
        ];
    }

    public function with($request)
    {

        $property = $this->property_info;
        $meta = $this->getNextPageData(Config::get('db_const.pre_checkin.step_0'), $this->id);

        $check_property_image = checkImageExists($property->logo, $property->name, config('db_const.logos_directory.property.value'));
        $property_logo = asset(config('db_const.logos_directory.property.img_path') . $check_property_image['property_image']);

        $check_booking_source_logo = checkImageExists($this->bookingSourceForm->logo, $this->bookingSourceForm->name, config('db_const.logos_directory.booking_source.value'));
        $booking_source_logo = asset(config('db_const.logos_directory.booking_source.img_path') . $check_booking_source_logo['booking_source_image']);

        return [
            'header' => [
                'property_name' => $property->name,//.' #'.$property->pms_property_id
                'property_logo' => $property_logo,
                'property_initial' => $check_property_image['property_initial'],
                'booking_source' => 'Booked through ' . $this->bookingSourceForm->name,
                'booking_source_logo' => $booking_source_logo,
                'booking_source_initial' => $check_booking_source_logo['booking_source_initial'],
                'external_link' => ''
            ],
            'meta' => $meta
        ];
    }
}
