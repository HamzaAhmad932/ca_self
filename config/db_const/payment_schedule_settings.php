<?php 
return [
    'settings' => [

        "status" => [

            'in-active' => [
                'value' => 0,
                'label' => 'in-active',
                'desc' => 'Auto Payments Collection in-active'
            ],

            'active' => [
                'value' => 1,
                'label' => 'active',
                'desc' => 'Auto Payments Collection active'
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
            'value' => null, //User defines in integer value
            'label' => 'Price | Percentage Value',
            'desc' => 'Price | Percentage Value as type stored in amountType.'
        ],

        "dayType" => [
            'after_booking' => [
                'value' => 1,
                'label' => 'after Booking Days Selected',
                'desc' => 'selected afterBookingDays will be entertain to calculating 1st split or full amount charge 
                            due date (BookingDateTime + afterBookingDays)'
                ],

            'before_check_in' => [
                'value' => 2,
                'label' => 'before Check-in Days Selected',
                'desc' => 'selected beforeCheckInDays will be entertain to calculating 1st split or full amount charge 
                           due date (checkin date - beforeCheckInDays)'
            ]
        ],

        "afterBookingDays" => [
            'value' => null, // user defines in seconds
            'label' => 'Days after Booking in seconds',
            'desc' => 'Days after Booking in seconds to calculate due date for 1/2 split or full charge 
                       (BookingDatTime + afterBookingDays)'
        ],

        "beforeCheckInDays" => [
            'value' => null, // user defines in seconds
            'label' => 'Days before Check-in in seconds',
            'desc' => 'Days before Check-in in seconds to calculate due date for 1/2 split or full charge 
                       (Check-in DateTime - beforeCheckInDays)'
        ],

        "remainingBeforeCheckInDays" => [
            'value' => null, // user defines in seconds
            'label' => 'Days before Check-in in seconds',
            'desc' => 'Days before Check-in in seconds to calculate due date for 2 of 2 split or remaining dues 
                       (Check-in DateTime - remainingBeforeCheckInDays).'
        ],
    ],
];