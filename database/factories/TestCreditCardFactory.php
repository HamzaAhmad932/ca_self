<?php

use Illuminate\Support\Str;
use Faker\Generator as Faker;
use App\CreditCardInfo;

$factory->define(CreditCardInfo::class, function (Faker $faker) {
    return [
        'booking_info_id' => mt_rand(1000,9999),
        'user_account_id' => mt_rand(1000,9999),
        'is_vc' => 0,
        'card_name' => $faker->name,
        'f_name' => $faker->name,
        'l_name' => $faker->name,
        'cc_last_4_digit' => 4242,
        'cc_exp_month' => 12,
        'cc_exp_year' => 25,
        'system_usage' => '',
        'customer_object' => '{"token":"cus_GojytJYescASpS","created_at":1582887285,"updated_at":"","email":"rsb94510@gmail.com","data":"","last_four_digits":"4242","first_six_digits":"","card_type":"visa","first_name":"Gilbert","last_name":"Harber","month":"12","year":"22","default_source":null,"three_d_secure_usage":true,"status":"succeeded","payment_method":"pm_1GH6UTDRRAarpUctx4ZTmq8C","succeeded":true,"state":"Success","message":"","fullResponse":"{\"id\":\"cus_GojytJYescASpS\",\"object\":\"customer\",\"account_balance\":0,\"address\":{\"city\":\"North Art\",\"country\":\"PG\",\"line1\":\"594 Hintz Parks Apt. 604East Jadenside, MS 80454-4032\",\"line2\":null,\"postal_code\":\"00342\",\"state\":null},\"balance\":0,\"created\":1582887285,\"currency\":null,\"default_source\":null,\"delinquent\":false,\"description\":\"Gilbert Harber   Booking ID 17468888\",\"discount\":null,\"email\":\"rsb94510@gmail.com\",\"invoice_prefix\":\"18150D07\",\"invoice_settings\":{\"custom_fields\":null,\"default_payment_method\":null,\"footer\":null},\"livemode\":false,\"metadata\":{\"Booking ID\":\"17468888\",\"First Name\":\"Gilbert\",\"Last Name\":\"Harber\"},\"name\":\"Gilbert Harber\",\"phone\":\"(757)285-5243x8578\",\"preferred_locales\":[],\"shipping\":null,\"sources\":{\"object\":\"list\",\"data\":[],\"has_more\":false,\"total_count\":0,\"url\":\"\\\/v1\\\/customers\\\/cus_GojytJYescASpS\\\/sources\"},\"subscriptions\":{\"object\":\"list\",\"data\":[],\"has_more\":false,\"total_count\":0,\"url\":\"\\\/v1\\\/customers\\\/cus_GojytJYescASpS\\\/subscriptions\"},\"tax_exempt\":\"none\",\"tax_ids\":{\"object\":\"list\",\"data\":[],\"has_more\":false,\"total_count\":0,\"url\":\"\\\/v1\\\/customers\\\/cus_GojytJYescASpS\\\/tax_ids\"},\"tax_info\":null,\"tax_info_verification\":null}"}',
        'auth_token' => 'cus_GojytJYescASpS',
        'status' => 1,
        'attempts' => 1,
        'error_message' => Str::random(10),
        'due_date' => now()->subMonth(2),
        'country' => $faker->country,
        'is_3ds' => 0,
        'type' => 1,
        'is_default' => 0,
        'decline_email_sent' => 0
    ];
});
