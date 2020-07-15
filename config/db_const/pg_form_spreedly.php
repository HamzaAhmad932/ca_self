<?php
/*
 * Note do not change order of credentials
 */
return [
    "name" => "CV Gateway",
    "backend_name" => \App\System\PaymentGateway\Spreedly\PG_Spreedly::class,
    "credentials" => [

        "env_key" => [
            "name" => "env_key",
            "label" => "Environment Key",
            "desc" => "Environment Key",
            "rules" => "required|max:100|min:20",
            "type" => \App\System\PaymentGateway\Models\CredentialFormField::TYPE_TEXT,
            "state" => \App\System\PaymentGateway\Models\CredentialFormField::STATE_SHOW
        ],

        "api_secret" => [
            "name" => "api_secret",
            "label" => "API Access Secret",
            "desc" => "API Access Secret",
            "rules" => "required|max:100|min:50",
            "type" => \App\System\PaymentGateway\Models\CredentialFormField::TYPE_TEXT,
            "state" => \App\System\PaymentGateway\Models\CredentialFormField::STATE_SHOW
        ],
    ],
    "status" => [
        'active' => [
            'label' => 'Active',
            'value' => 1,
            'desc' => 'CV Gateway is active'
        ],
        'inactive' => [
            'label' => 'Inactive',
            'value' => 2,
            'desc' => 'CV Gateway is inactive'
        ],
    ]
];