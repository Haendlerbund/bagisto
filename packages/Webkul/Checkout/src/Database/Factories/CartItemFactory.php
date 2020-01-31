<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use Webkul\Checkout\Models\CartItem;

$factory->define(CartItem::class, function (Faker $faker) {
    $now = date("Y-m-d H:i:s");

    return [
        'quantity'   => 1,
        'type'       => 'virtual',
        'created_at' => $now,
        'updated_at' => $now,
    ];
});

