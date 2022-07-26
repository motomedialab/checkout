<?php

namespace Motomedialab\Checkout\Contracts;

use Illuminate\Support\Collection;

interface CalculatesProductsShipping
{
    public function __invoke(Collection $products, string $currency);
}