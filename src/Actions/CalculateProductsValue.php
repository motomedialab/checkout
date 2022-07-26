<?php
/**
 * @author MotoMediaLab <hello@motomedialab.com>
 * Created at: 26/07/2022
 */

namespace Motomedialab\Checkout\Actions;

use Illuminate\Support\Collection;
use Motomedialab\Checkout\Contracts\CalculatesProductsValue;
use Motomedialab\Checkout\Models\Product;

class CalculateProductsValue implements CalculatesProductsValue
{
    
    public function __invoke(Collection $products, string $currency)
    {
        return $products->map(function (Product $product) use ($currency) {
            return $product->price($currency)->toPence() * ($product->quantity ?? 1);
        })->sum();
    }
}