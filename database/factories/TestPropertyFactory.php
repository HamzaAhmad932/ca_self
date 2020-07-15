<?php

use Illuminate\Support\Str;
use Faker\Generator as Faker;
use App\PropertyInfo;
use App\UserAccount;
use App\User;

$factory->define(PropertyInfo::class, function (Faker $faker) {
    $currency_code = ['USD', 'AZN', 'GBP', 'THB', 'AED', 'MZN', 'KRW', 'NZD', 'EUR'];
    $time_zone = ['America/Toronto', 'Asia/Tokyo', 'America/Vancouver', 'Asia/Kolkata', 'America/Vancouver', 'Asia/Karachi', 'Australia/Sydney', 'Asia/Kolkata', 'America/Vancouver'];
    $user = getRandomUserAccountAndUser();
    $pms_property_id = PropertyInfo::orderBy('pms_property_id', 'desc')->value('pms_property_id') == null ? 1 : PropertyInfo::orderBy('pms_property_id', 'desc')->value('pms_property_id');

    return [
        'name' => $faker->name,
        'user_account_id' => $user['user_account_id'],
        'user_id' => $user['user_id'],
        'logo' => 'no_image.png',
        'pms_id' => 1,
        'pms_property_id' => $faker->unique()->numberBetween($pms_property_id+1, $pms_property_id+274),
        'property_key' => Str::random(16),
        'longitude' => 0,
        'address' => $faker->address,
        'city' => $faker->city,
        'country' => $faker->country,
        'user_payment_gateway_id' => 0,
        'use_bs_settings' => rand(0,1),
        'use_pg_settings' => rand(0,1),
        'currency_code' => $currency_code[rand(0,8)],
        'time_zone' => $time_zone[rand(0,8)],
        'property_email' => $faker->unique()->safeEmail,
        'last_sync' => now()->addMonths(2),
        'status' => rand(0,1),
        'available_on_pms' => rand(0,1),
    ];
});
