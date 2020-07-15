<?php

return [
    'settings' => [
        "status" => [
            'in-active' => [
                'value' => 0,
                'label' => 'in-active',
                'desc' => 'Security Damage Deposit in-active'
            ],
            'active' => [
                'value' => 1,
                'label' => 'active',
                'desc' => 'Security Damage Deposit active'
            ],
        ],

        "amountType" => [

            'fixed' => [
                'value' => 1,
                'label' => 'flat amount',
                'desc' => 'Flat Amount to Charge'
            ],

            'percentage' => [
                'value' => 2,
                'label' => 'percentage',
                'desc' => 'Charge Percentage of Booking Amount'
            ],

            'first_night' => [
                'value' => 3,
                'label' => 'first night',
                'desc' => 'Charge First night amount of Booking'
            ],
        ],

        "amountTypeValue" => [
            'value' => null, //User Defines in integer
            'label' => 'Price or Percentage Value',
            'desc' => 'Price or Percentage Value as type stored in amountType.'
        ],

        "authorizeAfterDays" => [
            'value' => null, //User Defines in seconds
            'label' => 'Days Before Check-in in seconds',
            'desc' => 'Days Before Check-in in seconds to get due date for auth'
        ],

        "autoReauthorize" => [
            true => [
                'value' => true, // 1 | true
                'label' => 'Should Auto ReaAuth',
                'desc' => 'Should Auto ReaAuth after every 7 Days'
            ],
            false => [
                'value' => false, // 0 | false
                'label' => 'Auto ReaAuth Off',
                'desc' => 'Auth Only one time.'
            ],
        ]
    ]
];