<?php

use Faker\Generator as Faker;
use App\GuestImage;

$factory->define(GuestImage::class, function (Faker $faker) {
    return [
        'booking_id' => mt_rand(1000,9999),
        'image' => 'no_image.png',
        'type' => 'passport',
        'status' => 0
    ];
});
