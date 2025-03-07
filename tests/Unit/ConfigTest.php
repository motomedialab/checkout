<?php

namespace Motomedialab\Checkout\Tests\Unit;

use Motomedialab\Checkout\Tests\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @test
     **/
    public function config_can_be_loaded()
    {
        $this->assertEquals('checkout_orders', config('checkout.tables.orders'));
    }
}
