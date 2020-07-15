<?php

namespace App\Http\Resources\Admin\Booking;

use App\Repositories\BookingSources\BookingSources;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AdminBookingCollection extends ResourceCollection
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
            'data' => AdminBookingResource::collection($this->collection),
            'links' => [],
        ];
    }
}
