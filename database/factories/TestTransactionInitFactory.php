<?php

use Illuminate\Support\Str;
use Faker\Generator as Faker;
use App\TransactionInit;
use App\System\PaymentGateway\Models\Transaction;

$factory->define(TransactionInit::class, function (Faker $faker) {
    $transaction = new Transaction();
    $transaction->token = "ch_1GH6biDRRAarpUctlPCuq66T";
    $transaction->type = "Charge";
    $transaction->message = "Payment complete.";
    $transaction->order_id = 1582887733795;
    $transaction->state = "succeeded";
    $transaction->status = true;
    $transaction->currency_code = "USD";
    $transaction->amount = 3371;
    $transaction->description = "Isadore Bartoletti Booking ID 17472949";
    $transaction->created_at = 1582887734;
    $transaction->updated_at = "";
    $transaction->isPartial = false;
    $transaction->fullResponse = "";
    $transaction->exceptionMessage = "";
    $transaction->paymentIntentId = "pi_1GH6biDRRAarpUctS2SdBT6C";
    $transaction->payment_intent_client_secret = "pi_1GH6biDRRAarpUctS2SdBT6C_secret_aYEWGkI1urH8r35WG7WYVedBm";
    $transaction->authenticationUrl = null;
    $transaction->checkout_form = null;

    return [
        'booking_info_id' => mt_rand(1000,9999),
        'pms_id' => 1,
        'due_date' => now()->subMonth(2),
        'next_attempt_time' => now()->subMonth(2),
        'update_attempt_time' => now()->subMonth(2),
        'price' => 10000,
        'payment_status' => 1,
        'user_id' => mt_rand(1000,9999),
        'user_account_id' => mt_rand(1000,9999),
        'charge_ref_no' => 're_1EdbmyKh4TiALV2u6wYa46QZ',
        'last_success_trans_obj' => $transaction,
        'lets_process' => 0,
        'final_tick' => 0,
        'system_remarks' => 'Aborting this transaction because its Paid (Partially Paid) on BookingAutomation.',
        'split' => 1,
        'type' => 'C',
        'status' => 1,
        'transaction_type' => 1,
        'client_remarks' => Str::random(15),
        'attempt' => 1,
        'attempts_for_500' => 0,
        'decline_email_sent' => 0,
        'payment_intent_id' => 'pi_1GH6biDRRAarpUctS2SdBT6C',
        'in_processing' => 0
    ];
});
