<?php

namespace Motomedialab\Checkout\Contracts;

use Illuminate\Support\Collection;

interface CalculatesProductsVat
{
    public function __invoke(Collection $products, string $currency);
}