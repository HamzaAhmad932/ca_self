<?php

//===============================================================
// This file is used for user Account's status text
// we can change the only values in case of requirments
//                  NOT KEYS OF THE ARRAYS
// it may harmfull for the project...
//===============================================================

return [
    'status' => [
            'active' => [
                'label' => 'Active',
                'value' => 1,
                'desc' => 'Active',
                'email' => [
                    'subject' => 'ChargeAutomation Account Enabled',
                    'email_title' => [ 'text' => 'Account Enabled', 'text_color' => '#1EAF24' ],
                    'top_paragraph' => 'Your ChargeAutomation account has been enabled.',
                    'top_paragraph_second_line' => 'All booking sync, schedule transaction, property sync have resumed.'
                ]
            ],
            'deactive' => [
                'label' => 'Inactive',
                'value' => 2,
                'desc' => 'Inactive with completed profile',
                 'email' => [
                     'subject' => '🔺ChargeAutomation Account Disabled',
                     'email_title' => [ 'text' => 'Account Disabled', 'text_color' => '#dc3545' ],
                    'top_paragraph' => 'Your ChargeAutomation account has been disabled. All booking sync, schedule transactions, property sync have been paused.',
                    'top_paragraph_second_line' => 'You can enable your account again at any time to resume schedule transactions and sync bookings.'
                 ]

            ],
            'suspendedbyadmin' => [
                'label' => 'Suspended',
                'value' => 3,
                'desc' => 'Suspended by Admin(cannot be un-suspended by client)',
                'email' => [
                    'subject' => '🔺Account Suspended By Admin',
                    'email_title' => [ 'text' => 'Account Suspended By Admin', 'text_color' => '#dc3545' ],
                    'top_paragraph' => 'Your ChargeAutomation account has been disabled. All booking sync, schedule transactions, property sync have been paused.',
                    'top_paragraph_second_line' => 'You can enable your account again at any time to resume schedule transactions and sync bookings.'
                ]
            ],
            'pending' => [
                'label' => 'Pending',
                'value' => 4,
                'desc' => 'Profile not complete yet',
                'email' => [
                    'subject' => '🔺ChargeAutomation Account Profile is Pending',
                    'email_title' => [ 'text' => 'Account Profile is Pending', 'text_color' => '#dc3545' ],
                    'top_paragraph' => 'Your ChargeAutomation account has been disabled. All booking sync, schedule transactions, property sync have been paused.',
                    'top_paragraph_second_line' => 'You can enable your account again at any time to resume schedule transactions and sync bookings.'
                ]

            ],
            // 'suspended' => [
            //     'label' => 'Suspended',
            //     'value' => 5,
            //     'desc' => 'Suspended by client (can be unsuspended by client)'
            // ],

            'get_key' => [
                1 => 'active',
                2 => 'deactive',
                3 => 'suspendedbyadmin',
                4 => 'pending',
            ]
    ],

    'email' => [
            'verify' => [
                'label' => 'Verify',
                'value' => 1,
                'attribute' => 'text'
            ],
            'unverified' => [
                'label' => 'Unverified',
                'value' => 2,
                'attribute' => 'text'
            ],
        ],

    'status_button_color' => [
        1 => 'm-badge  m-badge--success m-badge--wide',
        2 => 'm-badge  m-badge--warning m-badge--wide',
        3 => 'm-badge  m-badge--danger m-badge--wide'
    ]  
];
