<?php

/**
 * @author MotoMediaLab <hello@motomedialab.com>
 * Created at: 27/07/2022
 */

namespace Motomedialab\Checkout\Models\Pivots;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Motomedialab\Checkout\Models\Order;

/**
 * @property Order $order
 * @property int $amount_in_pence
 * @property int $shipping_in_pence
 * @property int $quantity
 * @property float $vat_rate
 * @property array $metadata
 */
class OrderPivot extends Pivot
{
    protected $casts = [
        'quantity' => 'integer',
        'metadata' => 'array',
    ];

    protected function order(): Attribute
    {
        return new Attribute(get: fn () => $this->pivotParent);
    }
}
