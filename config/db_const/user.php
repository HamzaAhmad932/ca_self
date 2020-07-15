<?php

//===============================================================
// This file is used for user's status text
// we can change the only values in case of requirments
//                  NOT KEYS OF THE ARRAYS
// it may harmfull for the project...
//===============================================================


return [
    'status' => [
            'active' => [
                'label' => 'Activate',
                'value' => 1,
                'desc' => 'Active'
            ],
            'deactive' => [
                'label' => 'Deactivate',
                'value' => 2,
                'desc' => 'Deactive with completed profile'
            ],
            'suspendedbyadmin' => [
                'label' => 'Suspended',
                'value' => 3,
                'desc' => 'Suspended by Admin(cannot be unsuspended by client)'
            ],
            'pending' => [
                'label' => 'Pending',
                'value' => 4,
                'desc' => 'Profile not complete yet'
            ],
            'suspended' => [
                'label' => 'Suspended',
                'value' => 5,
                'desc' => 'Suspended by client (can be unsuspended by client)'
            ],
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

    'roles' => [
        'ROLE_ADMINISTRATOR'=>'Administrator',
        'ROLE_MANAGER'=>'Manager',
        'ROLE_MEMBER'=>'Member',
    ],




    ];
