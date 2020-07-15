<?php

 // Only Update this file for Live (releases)

return [
    'secret_key' => env('STRIPE_SECRET_KEY', ''),
    'publish_key' => env('STRIPE_PUBLISHABLE_KEY', ''),
    'commission-billing-endpoint-secret' => env('STRIPE_COMMISSION_BILLING_ENDPOINT_SECRET', ''),

    'account_suspended_coupon' => 'JTs6RpHe', // To avoid further charge after account suspended


    'plans' => [
        'plan_per_rental_charge' => [
            'per_rental_charge1' => 'plan_CdlfuazP4NPyIB',
        ],
        'plan_per_booking_charge' => [

        ],

        'transaction_volume' => [
            'plan_per_success_transaction_amount_volume_charge' => 'transaction_volume_0_2_percent', //Transaction usage volume
            'plan_per_success_transaction_amount_volume_charge2' =>  '015OfTotalSuccessfulAmount',
        ],

        'transaction_count' => [
            'plan_per_success_transaction_count_charge' => 'per_transaction_0_8', //Plan for Transaction Counts * $0.8
            'plan_per_success_transaction_count_charge2' => 'per_transaction_0_30', //Plan for Transaction Counts * $0.30
            'plan_per_success_transaction_count_charge3' => 'tiered_granual_per_transaction', //Plan for Transaction Counts
            'plan_per_success_transaction_count_charge4' => '025FlatFeePerTransaction',
        ],



        'plan_default' => 'transaction_volume_0_2_percent',
    ],

    'action_type' => [
        'ACTION_NUMBER_OF_PROPERTY_UPDATE' => 'number_of_property_usage_update',
        'ACTION_NUMBER_OF_TRANSACTION_UPDATE' => 'number_of_transaction_usage_update',
        'ACTION_VOLUME_OF_TRANSACTION_UPDATE' => 'volume_of_transaction_usage_update',
        'ACTION_NUMBER_OF_BOOKING_UPDATE' => 'number_of_booking_usage_update',
    ]
];
