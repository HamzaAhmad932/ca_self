<?php


namespace App\Http\Resources\Admin\UpsellListing;


use App\UpsellPropertiesBridge;

trait UpsellListingHelperTrait
{

    /**
     * return all attached Properties With related attached Rooms
     * @return mixed
     */
    private function attachedRentals()
    {
        $collection  = $this->upsellPropertiesBridge->where('status', config('db_const.upsell_listing.status.active.value'));
        $collection->transform(function($instance) {
            return [
                'id' => $instance->property_info_id,
                'pms_property_id' => $instance->propertyInfo['pms_property_id'],
                'property_name' => $instance->propertyInfo['name'],
                'rooms' => $this->attachedRooms($instance),
            ];
        });
        return $collection;
    }

    /**
     * return all attached Rooms with bridge Record instance
     * @param UpsellPropertiesBridge $instance
     * @return mixed
     */
    private function attachedRooms(UpsellPropertiesBridge $instance)
    {
        return ! empty($instance->room_info_ids)
            ? $instance->propertyInfo->room_info->whereIn('id',
                $instance->room_info_ids)->pluck('name','pms_room_id')->toArray()
            : ['All Rentals'];
    }

}