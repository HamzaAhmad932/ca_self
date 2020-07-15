<?php
    return [
        'spreedly' => [
            'env_key' => env('SPREEDLY_ENV_KEY', ''),
            'api_secret' => env('SPREEDLY_API_SECRET', '')
        ],
        'stripe' => [
            'secret_key' => env('STRIPE_SECRET_KEY', ''),
            'publishable_key' => env('STRIPE_PUBLISHABLE_KEY', ''),
            'connect-client-id' => env('STRIPE_CONNECT_CLIENT_ID', ''),
            'redirect-url' => env('STRIPE_REDIRECT_URL', ''),
            'stripe-endpoint-secret' => env('STRIPE_ENDPOINT_SECRET', '')
        ],
        'gateway_verification_card' => [
            'number' => env('GATEWAY_CARD_NUMBER', ''),
            'expiry_month' => env('GATEWAY_CARD_EXPIRY_MONTH', ''),
            'expiry_year' => env('GATEWAY_CARD_EXPIRY_YEAR', ''),
            'cvc' => env('GATEWAY_CARD_CVV', ''),
            'first_name' => env('GATEWAY_CARD_FIRST_NAME', ''),
            'last_name' => env('GATEWAY_CARD_LAST_NAME', ''),
            'currency' => env('GATEWAY_CARD_CURRENCY', ''),
            'amount' => env('GATEWAY_CARD_AMOUNT', '')
        ],
        'intercom_app_id' => env('INTERCOM_APP_ID', 'gz9kbn9t'),
        'front_end_website' => "https://chargeautomation.com",
        'front_end_terms' => "https://chargeautomation.com/terms-of-service"
    ];