<?php

namespace App\Http\Resources\General\Properties\PropertiesBridges;

use App\Traits\Resources\General\PropertiesBridgesHelperTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertiesBridgesResource extends JsonResource
{
    use PropertiesBridgesHelperTrait;

    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $this->room_info = $this->room_info->where('available_on_pms', 1);
        return [
            'id' => $this->id,
            'pms_property_id' => $this->pms_property_id,
            'name' => $this->name,
            'attached_rooms' => $this->attachedRooms(),
            'attach_status' => $this->propertyBridgeAttachStatus(),
            'all_rooms' => $this->allRooms(),
            'all_rooms_available' => $this->allRentalsAvailable,
        ];
    }
}
