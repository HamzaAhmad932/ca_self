<?php


namespace App\Traits\Resources\General;



use App\Http\Resources\General\Properties\PropertiesBridges\RoomInfosResource;

trait PropertiesBridgesHelperTrait
{

    private function propertyBridgeAttachStatus()
    {
        $record = $this->{$this->bridge_relation}->first();
        return !empty($record) ? $record->status :  false;
    }

    private function allRooms()
    {
        return RoomInfosResource::collection($this->room_info);
    }

    private function attachedRooms()
    {
        $all = array(['name' => 'All Rentals', 'code' => 0]);
        $record = $this->{$this->bridge_relation}->first();
        if(!empty($record)){
            if(!empty($record->room_info_ids)){
                $rooms = $record->room_info_ids;
            }else if($record->{$this->bridge_column} == $this->serve_id){
                $rooms = $all;
            }else{
                $rooms = [];
            }
        }else{
            $rooms= [];
        }
        if(!empty($rooms) && $all !== $rooms){
            $rooms  = RoomInfosResource::collection($this->room_info->whereIn('id', $rooms));
        }
        return $rooms;
    }
}