<?php

namespace App\Http\Resources\General\Precheckin;

use App\BookingInfo;
use App\Traits\Resources\General\Precheckin;
use App\Repositories\Upsells\UpsellRepository;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Config;

class AddOnServiceCollection extends ResourceCollection
{
    use Precheckin;
    private $booking_id;

    public function __construct($booking_id, $resource)
    {
        parent::__construct($resource);
        $this->booking_id = $booking_id;
    }

    public function toArray($request)
    {

        $booking_info = BookingInfo::find($this->booking_id);
        $this->symbol = get_currency_symbol($booking_info->property_info->currency_code);
        $this->in_cart_due_amount = $this->collection['available']['upsell']->where('in_cart', '=', true)->sum('total_price');

        AddOnServiceResource::withAdditional(['booking_info_id'=>$booking_info->id]);
        UpsellCalculationResource::withAdditional(['booking_info_id'=>$booking_info->id,'days'=>$this->collection['available']['night_count'], 'guest_count'=>$this->collection['available']['guest_count'], 'symbol'=> $this->symbol]);

        return [
            'purchased'=> resolve(UpsellRepository::class)->upsellOrderList($this->booking_id), //AddOnServiceResource::collection($this->collection['purchased']),
            'available'=> UpsellCalculationResource::collection($this->collection['available']['upsell']),
        ];
    }

    public function with($request)
    {

        $meta = $this->getNextPageData(Config::get('db_const.pre_checkin.step_4'), $this->booking_id);

        return [
            'symbol'=> $this->symbol,
            'in_cart_due_amount' => $this->in_cart_due_amount,
            'status'        => true,
            'status_code'   => 200,
            'meta'          => $meta
        ];
    }
}
