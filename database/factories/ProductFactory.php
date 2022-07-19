<?php

use Faker\Generator as Faker;
use Motomedialab\Checkout\Models\Product;

$factory->define(Product::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'vat_rate' => $faker->randomFloat(1, 0, 20),
        'pricing' => [
            'gbp' => $faker->numberBetween(100, 10000),
            'eur' => $faker->numberBetween(100, 10000),
        ],
        'shipping' => [
            'gbp' => $faker->numberBetween(50, 1000),
            'eur' => $faker->numberBetween(50, 1000),
        ]
    ];
});