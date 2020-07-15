<?php

namespace App\Http\Resources\General\BookingSource;

use App\Repositories\Properties\Properties;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientActiveBookingSourcesWithDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'userBookingSourceId' => $this->id,
            'propertyInfoId' => $this->property_info_id,
            'bookingSourceFormId' => $this->booking_source_form_id,
            'name' => $this->booking_source_form->name,
            'logo' => strlen($this->booking_source_form->logo) > 2
                ? asset('storage/uploads/booking_souce_logo/').'/'.$this->booking_source_form->logo
                : $this->booking_source_form->logo,
            'settings' => Properties::getBookingSourceSettingsDetailsOfClientProperty($this),
        ];
    }
}
