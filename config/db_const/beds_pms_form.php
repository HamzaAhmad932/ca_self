<?php
/*
 * Note do not change order of credentials
 */
return [
    "backend_name" => '\App\System\PMS\BookingAutomation\BookingAutomation',
    "name" => "Beds24",
    "db_primary_key" => 6,
    "credentials" => [
        [
            "name" => "api-key",
            "label" => "PMS API Key",
            "desc" => "Find PMS API Key in your beds24.com account under settings > Account Access.",
            "rules" => "required|max:64|min:16",
            'is_unique' => false,
            "type" => \App\System\PaymentGateway\Models\CredentialFormField::TYPE_TEXT,
            "state" => \App\System\PaymentGateway\Models\CredentialFormField::STATE_SHOW
        ],
        [
            "name" => "username",
            "label" => "Beds24 Username",
            "desc" => "Beds24 username.",
            "rules" => "required|max:50|min:5",
            'is_unique' => true,
            "type" => \App\System\PaymentGateway\Models\CredentialFormField::TYPE_TEXT,
            "state" => \App\System\PaymentGateway\Models\CredentialFormField::STATE_SHOW

        ],
    ],

    "status" => [
        'active' => [
            'label' => 'Active',
            'value' => 1,
            'desc' => 'Beds24 PMS is active'
        ],
        'inactive' => [
            'label' => 'Inactive',
            'value' => 2,
            'desc' => 'Beds24 PMS is inactive'
        ],
    ],

    "notify-url" => env('BOOKING_AUTOMATION_NOTIFY_URL_BASE', ''),
    "instructions-route-name" => 'beds24_integration_instructions'
];