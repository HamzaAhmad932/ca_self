<?php
/*
 * Note do not change order of credentials
 */
return [
    "name" => "Stripe Keys",
    "backend_name" => \App\System\PaymentGateway\Stripe\PG_Stripe::class,
    "credentials" => [
        [
            "name" => "publishable_key",
            "label" => "Publishable key",
            "desc" => "Publishable key",
            "rules" => "required|max:100|min:20",
            "type" => \App\System\PaymentGateway\Models\CredentialFormField::TYPE_TEXT,
            "state" => \App\System\PaymentGateway\Models\CredentialFormField::STATE_SHOW
        ],
        [
            "name" => "secret_key",
            "label" => "Secret key",
            "desc" => "Secret key",
            "rules" => "required|max:100|min:20",
            "type" => \App\System\PaymentGateway\Models\CredentialFormField::TYPE_TEXT,
            "state" => \App\System\PaymentGateway\Models\CredentialFormField::STATE_SHOW
        ],
    ],
    "status" => [
        'active' => [
            'label' => 'Active',
            'value' => 1,
            'desc' => 'Stripe CA is active'
        ],
        'inactive' => [
            'label' => 'Inactive',
            'value' => 2,
            'desc' => 'Stripe CA is inactive'
        ],
    ]
];