<?php

namespace Motomedialab\Checkout\Tests\Unit;

use Motomedialab\Checkout\Contracts\CalculatesProductsVat;
use Motomedialab\Checkout\Helpers\Money;
use Motomedialab\Checkout\Models\Product;
use Motomedialab\Checkout\Tests\TestCase;

class CalculateProductsVatTest extends TestCase
{
    public function testItCanCalculateProductsVat()
    {
        $product = Product::factory()->make([
            'pricing_in_pence' => ['gbp' => 22900],
            'vat_rate' => 20,
        ]);

        $response = app(CalculatesProductsVat::class)(collect([$product]), 'gbp');

        $this->assertInstanceOf(Money::class, $response);
        $this->assertEquals(3817, $response->toPence());
    }
}
