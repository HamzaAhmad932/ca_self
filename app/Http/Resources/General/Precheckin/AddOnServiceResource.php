<?php

namespace App\Http\Resources\General\Precheckin;

use App\BookingInfo;
use App\PropertyInfo;
use App\Repositories\DynamicVariableInContent;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Config;

class AddOnServiceResource extends JsonResource
{
    public static $booking_info_id;

    public function __construct($resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        $price_desc = Config::get('db_const.upsell_listing.per.'.Config::get('db_const.upsell_listing.per.get_key.'.$this->per));
        $period_desc = Config::get('db_const.upsell_listing.period.'.Config::get('db_const.upsell_listing.period.get_key.'.$this->period));
        $setting_copy = json_decode($this->upsellOrderDetails[0]->upsell_price_settings_copy);
        $data =  [
            'id'=> $this->id,
            'title'=> $this->upsellType->title,
            'description'=> $this->meta->description,
            'price'=> $this->value,
            'value' => $setting_copy->value,
            'period'=> $price_desc['label'].' '.$period_desc['label'],
            'from_time'=> $this->meta->from_time,
            'from_am_pm'=> $this->meta->from_am_pm,
            'to_time'=> $this->meta->to_time,
            'to_am_pm'=> $this->meta->to_am_pm,
            'is_time_set'=> !(empty($this->meta->from_time) || $this->meta->from_time == '00:00'),
            'rules'=> $this->meta->rules,
        ];

        return convertTemplateVariablesToActualData(BookingInfo::class,self::$booking_info_id,$data);
    }
    public static function withAdditional(array $data){
        self::$booking_info_id = $data['booking_info_id'];
    }
}
