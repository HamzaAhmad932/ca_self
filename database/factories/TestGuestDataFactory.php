<?php

use Faker\Generator as Faker;
use App\GuestData;

$factory->define(GuestData::class, function (Faker $faker) {
    return [
        'booking_id' => mt_rand(1000,9999),
        'email' => $faker->unique()->safeEmail,
        'phone' => $faker->state,
        'arrivaltime' => '15:00',
        'name' => $faker->name,
        'adults' => 2,
        'childern' => 1,
        'arriving_by' => 'Plane',
        'plane_number' => mt_rand(1000,9999),
        'step_completed' => 0,
        'country_code' => 1,
        'terms_and_conditions_accepted' => 0,
    ];
});
