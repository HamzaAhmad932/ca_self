<?php

use Faker\Generator as Faker;
use App\UserAccount;

$factory->define(UserAccount::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'city' => $faker->city,
        'state' => $faker->state,
        'country' => $faker->country,
        'address' => $faker->address,
        'company_logo' => 'no_image.png',
        'email' => $faker->unique()->safeEmail,
        'contact_number' => mt_rand(1000000000,9999999999),
        'zip_code' => mt_rand(1000,9999),
        'post_code' => mt_rand(1000,9999),
        'area_code' => mt_rand(1000,9999),
        'account_verified_at' => now(),
        'integration_completed_on' => now(),
        'last_booking_sync' => now(),
        'time_zone' => 0,
        'status' => 1,
        'account_type' => 1,
        'user_account_id_at_pms' => null,
        'current_pms' => 'Booking Automation',
        'last_properties_synced' => now()->subMonth(2),
    ];
});
