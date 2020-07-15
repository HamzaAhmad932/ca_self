<?php


namespace App\Http\Resources\General\Precheckin;


use App\Traits\Resources\General\Precheckin;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Config;

class StepThreeCollection extends ResourceCollection
{
    use Precheckin;
    private $booking_id;

    public function __construct($booking_id, $collection)
    {
        parent::__construct($collection);
        $this->booking_id = $booking_id;
    }


    public function toArray($request)
    {
        $meta = $this->getNextPageData(Config::get('db_const.pre_checkin.step_3'), $this->booking_id);

        return [
            'step_3' => StepThreeResource::collection($this->collection),
            'meta' => $meta,
            'guest_images_status' => $this->guestImagesStatus($this->collection)
        ];
    }
}
