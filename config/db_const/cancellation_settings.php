<?php
[    'settings' => [
    "status" => [
        'in-active' => [
            'value' => 0,
            'label' => 'in-active',
            'desc' => 'Cancellation Settings in-active'
        ],
        'active' => [
            'value' => 1,
            'label' => 'active',
            'desc' => 'Cancellation Settings active'
        ],
    ],

    "afterBooking" => [
        'value' => null, // user defines in seconds
        'label' => 'Days after Booking in seconds',
        'desc' => 'Days after Booking in seconds (BookingDatTime + afterBookingDays) && "afterBookingStatus" also 
                   active then if booking cancellation datetime < or equal to (BookingDatTime + afterBookingDays) DateTime 
                   whole charged amount will be refunded no cancellation fee will be charged'
    ],

    "afterBookingStatus" => [
        true => [
            'value' => true, // 1 | true
            'label' => 'Active afterBooking Date Cancellation Rule',
            'desc' => 'System will compare Cancellation date time with "afterBooking" to calculate cancellation 
                       adjustment entries'
        ],
        false => [
            'value' => false, // 0 | false
            'label' => 'In-active afterBooking Date Cancellation Rule',
            'desc' => 'System will not compare Cancellation date time with "afterBooking" to calculate cancellation 
                       adjustment entries'
        ],
    ],


    "beforeCheckIn" => [
        'value' => null, // user defines in seconds
        'label' => 'Days before Check-in in seconds',
        'desc' => 'Days Before Check-in in seconds (Check-in DateTime - beforeCheckIn) && "beforeCheckInStatus" also 
                   active then if booking cancellation datetime < or equal to (Check-in DateTime - beforeCheckIn) DateTime 
                   whole charged amount will be refunded no cancellation fee will be charged'
    ],

    "beforeCheckInStatus" => [
        true => [
            'value' => true, // 1 | true
            'label' => 'Active beforeCheckIn Date Cancellation Rule',
            'desc' => 'System will compare Cancellation date time with "beforeCheckIn" to calculate cancellation
                       adjustment entries'
        ],
        false => [
            'value' => false, // 0 | false
            'label' => 'In-active beforeCheckIn Date Cancellation Rule',
            'desc' => 'System will not compare Cancellation date time with "beforeCheckIn" to calculate cancellation 
                       adjustment entries'
        ],
    ],

    "rules" => [
        "canFee" => [
            'value' => null, // user defines Percentage
            'label' => 'Percentage of Booking Amount',
            'desc' => 'How much Percentage amount will be charged if booking cancellation DateTime is 
                       greater then or equal to (check-in dateTime - is_cancelled)'
        ],

        "is_cancelled" => [
            'value' => null, // user defines days in seconds
            'label' => 'if cancelled within days in seconds before check-in',
            'desc' => 'If Booking Cancellation DateTime is greater then or equal to (check-in dateTime - is_cancelled) 
                       then this rule will be applied to generate cancellation adjustment entries'
        ],

        "is_cancelled_value" => [
            'value' => null, // user defines flat amount
            'label' => 'Flat Fee',
            'desc' => 'Percentage "canFee" amount + Flat fee to charge'
        ]
    ],

    "isNonRefundable" => false // RunTime CA defines when new booking received By Checking Booking Refundable Status.
]
];