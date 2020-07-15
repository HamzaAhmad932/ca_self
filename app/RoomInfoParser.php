<?php


namespace App;


trait RoomInfoParser
{
    // Mutator
    public function getRoomInfoIdsAttribute($value) {
        return $this->roomInfosToArray($value);
    }

    // Setter
    public function setRoomInfoIdsAttribute($value) {
        $this->attributes['room_info_ids'] = room_infos_to_string($value);
    }


    /**
     * @param $value
     * @return array|null
     */
    private function roomInfosToArray($value)
    {
        $ids = str_replace('"', '', $value);
        return !empty($ids) ? explode(',', $ids) : null;
    }

}