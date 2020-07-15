<?php

namespace App\Http\Resources\General\BookingDetail;

use App\GuestImage;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Config;
use App\Traits\Resources\General\BookingDetail;

class GuestDocumentCollection extends ResourceCollection
{
    use BookingDetail;
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $images = $this->getClassifiedImages($this->collection);

        return $images;
    }
}
