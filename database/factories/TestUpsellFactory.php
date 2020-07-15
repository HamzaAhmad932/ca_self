<?php

use Faker\Generator as Faker;
use App\Upsell;

$factory->define(Upsell::class, function (Faker $faker) {
    return [
        'user_account_id' => mt_rand(1000,9999),
        'user_id' => mt_rand(1000,9999),
        'upsell_type_id' => mt_rand(1000,9999),
        'internal_name' => $faker->name,
        'meta' => '{"description":"not any description yet","from_time":"00:00","from_am_pm":"am","to_time":"00:00","to_am_pm":"am","rules":[{"title":null,"icon":"fas fa-info","description":null,"isHighlighted":false}]}',
        'value_type' => 1,
        'value' => 200,
        'per' => 1,
        'period' => 2,
        'notify_guest' => 1,
        'status' => 1
    ];
});
