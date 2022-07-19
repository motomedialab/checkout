<?php

use Faker\Generator as Faker;
use Motomedialab\Checkout\Models\Voucher;

$factory->define(Voucher::class, function (Faker $faker) {
    return [
        'code' => mt_rand(1000, 9999),
        'max_uses' => mt_rand(0, 10),
        'total_uses' => 0,
        
        'valid_from' => null,
        'valid_until' => null,
        
        'value' => 10.5,
        
        'percentage' => true,
        'on_basket' => false,
        'quantity_price' => false,
    ];
});