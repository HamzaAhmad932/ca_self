<?php
return [
    //All Client Related Messages
    'client' => [
        'chat' => [
            'error' => [
                'chat_not_active' =>  'Failed to send message, because chat is de-activated from guest experience.',
                'checkout_date_passed' => 'CA cannot send message, because checkout date has passed.',
                'booking_cancelled' => 'CA cannot send message for an already cancelled booking.',
                'empty_message' => 'Error: Failed to send empty message.',
                'failed' => 'Error: Failed to send  message.',
            ],
            'success' => [
                'sent' => 'Message Sent to Guest Successfully.',

            ]
        ],
        'booking' => [
            'balance_amount_less_than_charge' => 'Due Balance is less than charging amount on PMS.'
            ]

    ],

    //All Guest Related Messages
    'guest' => [
        'chat' => [
            'error' => [
                'chat_not_active' =>  'Failed to send message, because chat is de-activated by Host',
                'checkout_date_passed' => 'CA cannot send message, because checkout date has passed.',
                'booking_cancelled' => 'CA cannot send message for an already cancelled booking.',
                'empty_message' => 'Error: Failed to send empty message.',
                'failed' => 'Error: Failed to send  message.',
            ],
            'success' => [
                'sent' => 'Message Sent to Host Successfully.',
            ]
        ]
    ],

    //All Admin Related Messages
    'admin' => [],
];