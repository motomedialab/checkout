<?php

namespace Motomedialab\Checkout\Contracts;

use Motomedialab\Checkout\Enums\ProductStatus;
use Motomedialab\Checkout\Helpers\PriceHelper;
use Motomedialab\Checkout\Models\Pivots\OrderPivot;

/**
 * @property \Motomedialab\Checkout\Models\Product $parent
 * @property PriceHelper $pricing_in_pence
 * @property PriceHelper $shipping_in_pence
 * @property string $name
 * @property float $amount_in_pence
 * @property float $vat_rate
 * @property int $quantity A temporary store for basket quantity
 * @property ProductStatus $status
 * @property ?OrderPivot $orderPivot
 */
interface Product
{
    //
}