<?php
/*
 * Note do not change order of credentials
 */
return [
    "name" => "Stripe",
    "backend_name" => \App\System\PaymentGateway\Stripe\PG_Stripe::class,
    "credentials" => [
        [
            "name" => "stripe_user_id",
            "label" => "Stripe Connect",
            "desc" => "Stripe Standard Connect",
            "rules" => "",
            "value" => "",
            "url" => "https://connect.stripe.com/oauth/authorize?response_type=code&client_id=".config('db_const.auth_keys.stripe.connect-client-id')."&scope=read_write&redirect_uri=".config('db_const.auth_keys.stripe.redirect-url'),
            "type" => \App\System\PaymentGateway\Models\CredentialFormField::TYPE_BUTTON,
            "state" => \App\System\PaymentGateway\Models\CredentialFormField::STATE_SHOW
        ],
        [
            "name" => "publishable_key",
            "label" => "Publishable key",
            "desc" => "Publishable key",
            "rules" => "required|max:100|min:20",
            "type" => \App\System\PaymentGateway\Models\CredentialFormField::TYPE_TEXT,
            "state" => \App\System\PaymentGateway\Models\CredentialFormField::STATE_HIDDEN
        ]
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