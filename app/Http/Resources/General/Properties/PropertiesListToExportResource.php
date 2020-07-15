<?php

namespace App\Http\Resources\General\Properties;

use Illuminate\Http\Resources\Json\JsonResource;

class PropertiesListToExportResource extends JsonResource
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
            'Pms id'             => $this->pms_property_id,
            'Name'               => $this->name,
            'Currency'           => $this->currency_code,
            'Property Key'       => $this->property_key,
            'Timezone'           => $this->time_zone,
            'Payment Gateway'    => ($this->use_bs_settings == 0 ? 'Global' : 'Local'),
            'Payment Rules'      => ($this->use_pg_settings == 0 ? 'Global' : 'Local'),
            'Address'            => $this->address,
            'Country'            => $this->country,
            'city'               => $this->city,
            'Connection Status'  => ($this->status == 1 ? 'Connected' : 'Disconnected'),
        ];
    }
}
