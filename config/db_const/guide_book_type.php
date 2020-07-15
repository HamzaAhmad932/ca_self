<?php

return [

    'is_user_defined' => [
        'user_defined' => [
            'label' => 'Custom Defined',
            'value' => 1,
        ],
        'system_defined' => [
            'label' => 'System Defined',
            'value' => 0,
        ]
    ],
    'priority'=>[
        "0"=>'Low',// By Default
        "1" =>'Medium',
        "2" =>'High',

    ],
    'guide_book_type_meta' => [
        'is_user_defined' => 'if Guide Book Type is default and system defined then is_user_defined = 0 else 1',
        'icon' => 'font awsome Icon class names to show on guest portal',
    ]
];
