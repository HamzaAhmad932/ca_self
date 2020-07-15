<?php


namespace App\Http\Resources\General\Precheckin;


use App\Traits\Resources\General\Precheckin;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Config;

class StepOneResource extends JsonResource
{
    use Precheckin;
    public $booking_id;

    public function __construct($booking_id, $resource)
    {
        parent::__construct($resource);
        $this->booking_id = $booking_id;
    }

    public function toArray($request)
    {

        if (!empty($this->resource)) {
            return $this->successReturn();
        } else {
            return $this->failReturn();
        }

    }

    public function successReturn()
    {
        $meta = $this->getNextPageData(Config::get('db_const.pre_checkin.step_1'), $this->booking_id);

        return [
            'email' => $this->email,
            'phone' => $this->phone,
            'guests' => $this->adults + $this->childern,
            'adults' => $this->adults,
            'childern' => $this->childern,
            'arriving_by' => $this->arriving_by,
            'plane_number' => $this->plane_number,
            'arrival_time' => $this->arrivaltime,
            'status' => true,
            'meta' => $meta
        ];
    }

    public function failReturn()
    {
        $meta = $this->getNextPageData(Config::get('db_const.pre_checkin.step_1'), $this->booking_id);

        return [
            'message' => 'Data not found.',
            'status' => false,
            'meta' => $meta
        ];

    }
}
