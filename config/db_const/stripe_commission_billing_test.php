<?php

// Only Update this file for test

return [
    'secret_key' => env('STRIPE_SECRET_KEY2', ''),
    'publish_key' => env('STRIPE_PUBLISHABLE_KEY2', ''),
    'commission-billing-endpoint-secret' => env('STRIPE_COMMISSION_BILLING_ENDPOINT_SECRET', ''),

    'account_suspended_coupon' => 'KOG7XbAj', // To avoid further charge after account suspended

    'plans' => [

        'plan_per_rental_charge' => [
            'Tiered_Per_rental_property',
        ],

        'plan_per_booking_charge' => [
            '85_per_booking_received',
        ],

        'transaction_count' => [
            'plan_per_success_transaction_count_charge' => '80_per_sucess_transaction',
            'plan_per_success_transaction_count_charge2' => '80_per_sucess_transaction', //Plan for Transaction Counts * $0.30
            'plan_per_success_transaction_count_charge3' => '80_per_sucess_transaction', //Plan for Transaction Counts
            'plan_per_success_transaction_count_charge4' => '', // TODO REPLACE WITH NEW PLAN ID (make plan for $0.0025 per Transaction unit on stripe metered billing)
        ],

        'transaction_volume' => [
            'plan_per_success_transaction_amount_volume_charge' => 'Tier_granual_percent_of_each_cent_amount_transaction_volume',
            'plan_per_success_transaction_amount_volume_charge2' =>  '', // TODO REPLACE WITH NEW PLAN ID (make plan for $0.0015 per unit on stripe metered billing)
        ],

        'plan_default' => 'Tier_granual_percent_of_each_cent_amount_transaction_volume',
    ],

    'action_type' => [
        'ACTION_NUMBER_OF_PROPERTY_UPDATE' => 'number_of_property_usage_update',
        'ACTION_NUMBER_OF_TRANSACTION_UPDATE' => 'number_of_transaction_usage_update',
        'ACTION_VOLUME_OF_TRANSACTION_UPDATE' => 'volume_of_transaction_usage_update',
        'ACTION_NUMBER_OF_BOOKING_UPDATE' => 'number_of_booking_usage_update',
    ]
];
