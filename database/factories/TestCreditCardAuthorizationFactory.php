<?php

use Faker\Generator as Faker;
use Illuminate\Support\Str;
use App\CreditCardAuthorization;

$factory->define(CreditCardAuthorization::class, function (Faker $faker) {
    return [
        'booking_info_id' => mt_rand(1000,9999),
        'cc_info_id' => mt_rand(1000,9999),
        'user_account_id' => mt_rand(1000,9999),
        'attempts' => 1,
        'attempts_for_500' => 0,
        'hold_amount' => 1000,
        'token' => 'ch_1GH6UVDRRAarpUctMceJJhXI',
        'transaction_obj' => '{"token":"ch_1GH6UVDRRAarpUctMceJJhXI","type":"Authorize","message":"Payment complete.","order_id":"","state":"requires_capture","status":true,"currency_code":"USD","amount":150,"description":"Gilbert Harber   Booking ID 17468888","created_at":1582887287,"updated_at":"","isPartial":false,"fullResponse":"{\"id\":\"pi_1GH6UVDRRAarpUct3MiAMOf4\",\"object\":\"payment_intent\",\"allowed_source_types\":[\"card\"],\"amount\":15000,\"amount_capturable\":15000,\"amount_received\":0,\"application\":\"ca_BtIVHIsOxoLrNHD5EhRs1h3mljC0AsBk\",\"application_fee_amount\":null,\"canceled_at\":null,\"cancellation_reason\":null,\"capture_method\":\"manual\",\"charges\":{\"object\":\"list\",\"data\":[{\"id\":\"ch_1GH6UVDRRAarpUctMceJJhXI\",\"object\":\"charge\",\"amount\":15000,\"amount_refunded\":0,\"application\":\"ca_BtIVHIsOxoLrNHD5EhRs1h3mljC0AsBk\",\"application_fee\":null,\"application_fee_amount\":null,\"balance_transaction\":null,\"billing_details\":{\"address\":{\"city\":\"North Art\",\"country\":\"PG\",\"line1\":\"594 Hintz Parks Apt. 604East Jadenside, MS 80454-4032\",\"line2\":null,\"postal_code\":\"00342\",\"state\":null},\"email\":\"rsb94510@gmail.com\",\"name\":\"Gilbert Harber\",\"phone\":\"(757)285-5243x8578\"},\"captured\":false,\"created\":1582887287,\"currency\":\"usd\",\"customer\":\"cus_GojytJYescASpS\",\"description\":\"Gilbert Harber   Booking ID 17468888\",\"destination\":null,\"dispute\":null,\"disputed\":false,\"failure_code\":null,\"failure_message\":null,\"fraud_details\":[],\"invoice\":null,\"livemode\":false,\"metadata\":[],\"on_behalf_of\":null,\"order\":null,\"outcome\":{\"network_status\":\"approved_by_network\",\"reason\":null,\"risk_level\":\"normal\",\"risk_score\":50,\"seller_message\":\"Payment complete.\",\"type\":\"authorized\"},\"paid\":true,\"payment_intent\":\"pi_1GH6UVDRRAarpUct3MiAMOf4\",\"payment_method\":\"pm_1GH6UTDRRAarpUctx4ZTmq8C\",\"payment_method_details\":{\"card\":{\"brand\":\"visa\",\"checks\":{\"address_line1_check\":\"pass\",\"address_postal_code_check\":\"pass\",\"cvc_check\":null},\"country\":\"US\",\"exp_month\":12,\"exp_year\":2022,\"fingerprint\":\"60VaunTffrJ8yhAM\",\"funding\":\"credit\",\"installments\":null,\"last4\":\"4242\",\"network\":\"visa\",\"three_d_secure\":null,\"wallet\":null},\"type\":\"card\"},\"receipt_email\":null,\"receipt_number\":null,\"receipt_url\":\"https:\\\/\\\/pay.stripe.com\\\/receipts\\\/acct_1GEHeTDRRAarpUct\\\/ch_1GH6UVDRRAarpUctMceJJhXI\\\/rcpt_GojyVoq3vUifo9efq9ZVz7QyIhBRJj9\",\"refunded\":false,\"refunds\":{\"object\":\"list\",\"data\":[],\"has_more\":false,\"total_count\":0,\"url\":\"\\\/v1\\\/charges\\\/ch_1GH6UVDRRAarpUctMceJJhXI\\\/refunds\"},\"review\":null,\"shipping\":null,\"source\":null,\"source_transfer\":null,\"statement_descriptor\":\"B ID 17468888\",\"statement_descriptor_suffix\":null,\"status\":\"succeeded\",\"transfer_data\":null,\"transfer_group\":null}],\"has_more\":false,\"total_count\":1,\"url\":\"\\\/v1\\\/charges?payment_intent=pi_1GH6UVDRRAarpUct3MiAMOf4\"},\"client_secret\":\"pi_1GH6UVDRRAarpUct3MiAMOf4_secret_VmjCBUbC2582IU0ZBXldtG7ix\",\"confirmation_method\":\"automatic\",\"created\":1582887287,\"currency\":\"usd\",\"customer\":\"cus_GojytJYescASpS\",\"description\":\"Gilbert Harber   Booking ID 17468888\",\"invoice\":null,\"last_payment_error\":null,\"livemode\":false,\"metadata\":[],\"next_action\":null,\"next_source_action\":null,\"on_behalf_of\":null,\"payment_method\":\"pm_1GH6UTDRRAarpUctx4ZTmq8C\",\"payment_method_options\":{\"card\":{\"installments\":null,\"request_three_d_secure\":\"automatic\"}},\"payment_method_types\":[\"card\"],\"receipt_email\":null,\"review\":null,\"setup_future_usage\":null,\"shipping\":null,\"source\":null,\"statement_descriptor\":\"B ID 17468888\",\"statement_descriptor_suffix\":null,\"status\":\"requires_capture\",\"transfer_data\":null,\"transfer_group\":null}","exceptionMessage":"","paymentIntentId":"pi_1GH6UVDRRAarpUct3MiAMOf4","payment_intent_client_secret":"pi_1GH6UVDRRAarpUct3MiAMOf4_secret_VmjCBUbC2582IU0ZBXldtG7ix","authenticationUrl":null,"checkout_form":null}',
        'is_auto_re_auth' => 0,
        'type' => 3,
        'due_date' => now()->subMonth(2),
        'next_due_date' => now()->subMonth(2),
        'status' => 1,
        'captured' => 1,
        'decline_email_sent' => 0,
        'remarks' => Str::random(10),
        'in_processing' => 0,
        'manually_released' => 0
    ];
});
