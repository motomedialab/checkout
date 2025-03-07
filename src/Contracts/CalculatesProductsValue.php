<?php

/**
 * @author MotoMediaLab <hello@motomedialab.com>
 * Created at: 26/07/2022
 */

namespace Motomedialab\Checkout\Contracts;

use Illuminate\Support\Collection;

interface CalculatesProductsValue
{
    public function __invoke(Collection $products, string $currency);
}
