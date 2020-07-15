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
    'priority'=>[
        "0"=>'Low',// By Default
        "1" =>'Medium',
        "2" =>'High',

    ],
    'is_user_defined' => [
        'system_defined' => [
            'label' => 'System Defined Types',
            'value' => 0,
            'desc' => 'Default Types'
        ],
        'user_defined' => [
            'label' => 'User Defined Types',
            'value' => 1,
            'desc' => 'Custom Types'
        ],
    ],

];
