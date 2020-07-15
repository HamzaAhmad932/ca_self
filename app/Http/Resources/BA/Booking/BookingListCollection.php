<?php

namespace App\Http\Resources\BA\Booking;

use App\Repositories\Settings\ClientGeneralPreferencesSettings;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BookingListCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $clientGeneralPreferencesSettings = new ClientGeneralPreferencesSettings(\auth()->user()->user_account_id);

        $this->collection->map(function ($instance) use ($clientGeneralPreferencesSettings) {
            $instance['clientGeneralPreferencesInstance'] = $clientGeneralPreferencesSettings;
            return $instance;
        });

        return [
            'data' => BookingListResource::collection($this->collection),
            'links' => [],
        ];
    }
}
