<?php

namespace App\Http\Resources\General\Properties\PropertiesBridges;

use Illuminate\Http\Resources\Json\JsonResource;

class RoomInfosResource extends JsonResource
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
            'name' => $this->name,
            'code' => $this->id,
        ];
    }
}
