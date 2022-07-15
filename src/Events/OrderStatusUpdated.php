<?php

namespace Motomedialab\Checkout\Events;

use Motomedialab\Checkout\Enums\OrderStatus;
use Motomedialab\Checkout\Models\Order;

class OrderStatusUpdated
{
    public function __construct(public Order $order, public OrderStatus $status)
    {
        //
    }
}