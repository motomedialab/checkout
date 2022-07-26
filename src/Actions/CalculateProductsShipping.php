<?php
/**
 * @author MotoMediaLab <hello@motomedialab.com>
 * Created at: 26/07/2022
 */

namespace Motomedialab\Checkout\Actions;

use Illuminate\Support\Collection;
use Motomedialab\Checkout\Contracts\CalculatesProductsShipping;
use Motomedialab\Checkout\Models\Product;

class CalculateProductsShipping implements CalculatesProductsShipping
{
    
    public function __invoke(Collection $products, string $currency)
    {
        return $products
                ->map(fn(Product $product) => $product->shipping($currency)?->toPence() ?? 0)
                ->max() ?? 0;
    }
}