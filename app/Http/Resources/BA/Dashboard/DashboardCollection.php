<?php

namespace App\Http\Resources\BA\Dashboard;

use App\Http\Resources\BA\Booking\BookingListResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DashboardCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data'=> RecentBookingResource::collection($this->collection)
        ];
//        return parent::toArray($request);
    }
}
