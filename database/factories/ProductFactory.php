<?php

namespace Database\Factories\Motomedialab\Checkout\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Motomedialab\Checkout\Models\Product;

class ProductFactory extends Factory
{
    protected $model = Product::class;
    
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'vat_rate' => $this->faker->randomFloat(1, 0, 20),
            'pricing_in_pence' => [
                'gbp' => $this->faker->numberBetween(100, 10000),
                'eur' => $this->faker->numberBetween(100, 10000),
            ],
            'shipping_in_pence' => [
                'gbp' => $this->faker->numberBetween(50, 1000),
                'eur' => $this->faker->numberBetween(50, 1000),
            ]
        ];
    }
}

