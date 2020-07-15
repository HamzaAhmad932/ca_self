<?php


namespace App\Http\Resources\BA\Precheckin;


use App\BookingInfo;
use App\Traits\Resources\General\Booking;
use App\Traits\Resources\General\Precheckin;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Config;
use App\Http\Resources\General\Precheckin\UpsellCalculationResource;

class CreditCardStepResource extends JsonResource
{
    use Precheckin;
    private $booking_id;
    private $upsells;

    public function __construct($booking_id, $resource, $upsell_orders)
    {
        parent::__construct($resource);
        $this->booking_id = $booking_id;
        $this->upsells = $upsell_orders;
    }

    public function toArray($request)
    {
        $property = $this->property_info;
        $this->timezone = $property->time_zone;
        $this->symbol = get_currency_symbol($property->currency_code);
        $card = $this->getCardInfo($this->resource);
        $meta = $this->getNextPageData(Config::get('db_const.pre_checkin.step_5'), $this->booking_id);
        $booking_info = $this->resource;
        $upsells = [];


        if (!empty($this->upsells['upsell'])) {
            UpsellCalculationResource::withAdditional(['booking_info_id' => $this->booking_id, 'days' => $this->upsells['night_count'], 'guest_count' => $this->upsells['guest_count'], 'symbol' => $this->symbol]);
            $upsells = UpsellCalculationResource::collection($this->upsells['upsell']);
        }

        return [
            'upsells' => $upsells,
            'upsell_total' => $this->upsells['amount_due'],
            'upsell_paid' => 0,
            'upsell_amount_due' => $this->upsells['amount_due'],
            'symbol' => $this->symbol,
            'card' => $card,
            'new_card' => $this->cardInfo($this->resource),
            'status' => true,
            'status_code' => 200,
            'meta' => $meta,
        ];
    }
}
