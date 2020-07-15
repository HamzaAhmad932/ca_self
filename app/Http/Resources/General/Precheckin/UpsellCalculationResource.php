<?php

namespace App\Http\Resources\General\Precheckin;

use App\BookingInfo;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Resources\Json\JsonResource;

class UpsellCalculationResource extends JsonResource
{
    public static $guest_count;
    public static $days;
    public static $symbol;
    public static $booking_info_id;
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $price_label = Config::get('db_const.upsell_listing.per.'.Config::get('db_const.upsell_listing.per.get_key.'.$this->per));
        $period_label = Config::get('db_const.upsell_listing.period.'.Config::get('db_const.upsell_listing.period.get_key.'.$this->period));
        $guests = $this->guest_count > 0 ? $this->guest_count : self::$guest_count;


        if (($this->meta->from_time == $this->meta->to_time)
            && ($this->meta->to_am_pm == $this->meta->from_am_pm)) {
            $is_time_set = false;
        } else {
            $is_time_set = true;
        }


        $data =  [
            'id'=> $this->id,
            'label'=> $this->upsellType->title,
            'price'=> $this->value,
            'price_label'=> $price_label,
            'period_label'=> $period_label,
            //'price_section'=> $price_section, //Depricated
            'day'=> $this->period == Config::get('db_const.upsell_listing.period.daily.value') ? self::$days : '-',
            'person'=>  $this->per == Config::get('db_const.upsell_listing.per.per_person.value') ? $guests : '-',
            'unit_price'=> $this->value,
            'unit_total'=> $this->total_price,
            'title'=> $this->upsellType->title,
            'description'=> $this->meta->description,
            'period'=> $price_label['label'].' '.$period_label['label'],
            'from_time'=> $this->meta->from_time,
            'from_am_pm'=> $this->meta->from_am_pm,
            'to_time'=> $this->meta->to_time,
            'to_am_pm'=> $this->meta->to_am_pm,
            'is_time_set' => $is_time_set && !empty($this->meta->from_am_pm) && !empty($this->meta->to_am_pm),
            'rules'=> $this->meta->rules,
            'total_price'=> $this->total_price,
            'in_cart'=> $this->in_cart,
            'show_guest_count'=> $this->show_guest_count,
            'guest_count'=> $this->guest_count,
            'days'=> self::$days,
        ];
        return  convertTemplateVariablesToActualData(BookingInfo::class,self::$booking_info_id,$data);
    }

    public static function withAdditional(array $data){
        self::$days = $data['days'];
        self::$guest_count = $data['guest_count'];
        self::$symbol = $data['symbol'];
        self::$booking_info_id = $data['booking_info_id'];
    }
}
