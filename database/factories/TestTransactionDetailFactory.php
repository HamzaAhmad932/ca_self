<?php

use Illuminate\Support\Str;
use Faker\Generator as Faker;
use App\TransactionDetail;

$factory->define(TransactionDetail::class, function (Faker $faker) {
    return [
        'transaction_init_id' => mt_rand(1000,9999),
        'cc_info_id' => mt_rand(1000,9999),
        'user_id' => mt_rand(1000,9999),
        'user_account_id' => mt_rand(1000,9999),
        'name' => $faker->name,
        'payment_processor_response' => '{"token":"re_1EdbmyKh4TiALV2u6wYa46QZ","type":"Refund","message":"","order_id":1558698006038,"state":"succeeded","status":true,"currency_code":"GBP","amount":3652,"description":"requested_by_customer","created_at":"","updated_at":"","isPartial":true,"fullResponse":"{\"id\":\"re_1EdbmyKh4TiALV2u6wYa46QZ\",\"object\":\"refund\",\"amount\":365200,\"balance_transaction\":\"txn_1EdbmyKh4TiALV2udtdvQ1By\",\"charge\":\"ch_1EdbkaKh4TiALV2usZYExs7m\",\"created\":1558698140,\"currency\":\"gbp\",\"metadata\":[],\"reason\":\"requested_by_customer\",\"receipt_number\":null,\"source_transfer_reversal\":null,\"status\":\"succeeded\",\"transfer_reversal\":null}","exceptionMessage":""}',
        'payment_gateway_form_id' => 1,
        'payment_status' => 1,
        'charge_ref_no' => 're_1EdbmyKh4TiALV2u6wYa46QZ',
        'client_remarks' => Str::random(15),
        'error_msg' => Str::random(10),
        'order_id' => mt_rand(1000,9999),
        'amount' => 10000
    ];
});
