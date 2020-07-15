<?php

return [
    'status' => [
        'active' => [
            'label' => 'Activate',
            'value' => 1,
            'desc' => 'Active'
        ],
        'inactive' => [
            'label' => 'Inactivate',
            'value' => 0,
            'desc' => 'Inactivate'
        ],
    ],
    'room_ids'=>'always get and return array of room info ids but mutator will auto convert to ("") and (,) 
                separated ids before saving & null Rooms_info_ids means all rooms can availing this upsell'

];
