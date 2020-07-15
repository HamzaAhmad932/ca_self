<?php

use Illuminate\Support\Str;
use Faker\Generator as Faker;
Use App\User;

$factory->define(User::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => '$2y$10$4CXDsWNcS/57v8mPp6toz.3I.hgfyY7ZouuJCI.NBALj3n/S/5Xdm',
        'phone' => mt_rand(1000000000,9999999999),
        'address' => $faker->address,
        'address2' => $faker->address,
        'city' => $faker->city,
        'state' => $faker->state,
        'country' => $faker->country,
        'website' => 'https://testapptor1a.chargeautomation.com',
        'status' => 1,
        'email_verified_at' => now()->subMonth(2),
        'is_activated' => 1,
        'user_image' => 'no_image.png',
        'remember_token' => Str::random(10),
    ];
});
