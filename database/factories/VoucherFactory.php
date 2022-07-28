<?php

namespace Database\Factories\Motomedialab\Checkout\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Motomedialab\Checkout\Models\Voucher;

class VoucherFactory extends Factory
{
    protected $model = Voucher::class;
    
    public function definition(): array
    {
        return [
            'code' => $this->faker->numberBetween(1000, 9999),
            'max_uses' => $this->faker->numberBetween(0, 10),
            'total_uses' => 0,
            
            'valid_from' => null,
            'valid_until' => null,
            
            'value' => 10.5,
            
            'percentage' => true,
            'on_basket' => false,
            'quantity_price' => false,
        ];
    }
}