<?php

namespace Motomedialab\Checkout\Actions;

use Illuminate\Support\Collection;
use Motomedialab\Checkout\Contracts\CalculatesProductsVat;
use Motomedialab\Checkout\Contracts\Product;
use Motomedialab\Checkout\Helpers\Money;

class CalculateProductsVat implements CalculatesProductsVat
{
    public function __invoke(Collection $products, string $currency): ?Money
    {
        return Money::make(
            $products
                ->map(fn (Product $product): array => [
                    'pence' => $product->pricing_in_pence->get($currency)->toPence(),
                    'vat_rate' => $product->vat_rate
                ])
                ->map(
                    fn (array $values): int => ceil(
                        $values['pence'] - ($values['pence'] / (1 + ($values['vat_rate'] / 100)))
                    )
                )
                ->sum(),
            $currency
        );
    }
}