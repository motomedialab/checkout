<?php

use Faker\Generator as Faker;
use Motomedialab\Checkout\Models\Product;

$factory->define(Product::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'vat_rate' => $faker->randomFloat(1, 0, 20),
        'pricing' => [
            'gbp' => $faker->randomFloat(2, 0, 100),
            'eur' => $faker->randomFloat(2, 0, 100),
        ],
        'shipping' => [
            'gbp' => $faker->randomFloat(2, 0, 5),
            'eur' => $faker->randomFloat(2, 0, 5),
        ]
    ];
});