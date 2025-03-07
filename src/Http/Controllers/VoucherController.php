<?php

namespace Motomedialab\Checkout\Http\Controllers;

use Motomedialab\Checkout\Factories\OrderFactory;
use Motomedialab\Checkout\Http\Resources\OrderResource;
use Motomedialab\Checkout\Models\Order;

class VoucherController
{
    public function __invoke(Order $order): OrderResource
    {
        $factory = OrderFactory::fromExisting($order);

        $factory->removeVoucher();

        return OrderResource::make($factory->save());
    }
}
