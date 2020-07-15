<?php

use Faker\Generator as Faker;
use App\RoomInfo;
use App\PropertyInfo;

$factory->define(RoomInfo::class, function (Faker $faker) {
    $property_id = PropertyInfo::all()->random()->id;
    return [
        'name' => $faker->name,
        'property_info_id' => $property_id,
        'pms_room_id' => mt_rand(10000,99999),
        'available_on_pms' => rand(0, 1),
    ];
});
