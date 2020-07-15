<?php

use Faker\Generator as Faker;
use App\UpsellType;

$factory->define(UpsellType::class, function (Faker $faker) {
    return [
        'user_account_id' => mt_rand(1000,9999),
        'user_id' => mt_rand(1000,9999),
        'title' => $faker->name,
        'name' => $faker->name,
        'is_user_defined' => 0,
        'priority' => 0,
        'status' => 1
    ];
});
