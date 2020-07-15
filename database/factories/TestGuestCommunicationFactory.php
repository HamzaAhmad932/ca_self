<?php

use Illuminate\Support\Str;
use Faker\Generator as Faker;
use App\GuestCommunication;

$factory->define(GuestCommunication::class, function (Faker $faker) {
    return [
        'user_id' => mt_rand(1000,9999),
        'user_account_id' => mt_rand(1000,9999),
        'booking_info_id' => mt_rand(1000,9999),
        'pms_booking_id' => mt_rand(1000,9999),
        'is_guest' => 1,
        'alert_type' => 'passport_uploaded',
        'message' => Str::random(20),
        'action_performed_by' => mt_rand(1000,9999),
        'action_performed' => 1,
        'action_required' => 1,
        'message_read_by_guest' => 1,
        'message_read_by_user' => 1,
    ];
});
