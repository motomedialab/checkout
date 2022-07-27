<?php

namespace Motomedialab\Checkout\Tests\Feature;

use Illuminate\Support\Arr;
use Motomedialab\Checkout\Factories\OrderFactory;
use Motomedialab\Checkout\Http\Resources\OrderResource;
use Motomedialab\Checkout\Http\Resources\ProductResource;
use Motomedialab\Checkout\Models\Product;
use Motomedialab\Checkout\Models\Voucher;
use Motomedialab\Checkout\Tests\TestCase;

class OrderResourceTest extends TestCase
{
    /**
     * @test
     **/
    function an_order_is_formatted_as_resource()
    {
        $product = factory(Product::class)->create([
            'pricing_in_pence' => ['eur' => 10000],
            'shipping_in_pence' => ['eur' => 400],
            'vat_rate' => 20,
        ]);
        
        $order = OrderFactory::make('eur')
            ->add($product)
            ->save();
        
        $resource = (new OrderResource($order->fresh()))->toArray(request());
        
        $this->assertEquals('EUR', Arr::get($resource, 'currency'));
        $this->assertEquals(8333, Arr::get($resource, 'products.0.pricing.exc_vat_in_pence'));
        $this->assertEquals(10000, Arr::get($resource, 'products.0.pricing.inc_vat_in_pence'));
        $this->assertEquals(1667, Arr::get($resource, 'products.0.pricing.vat_in_pence'));
        $this->assertEquals($product->name, Arr::get($resource, 'products.0.name'));
    }
    
    /**
     * @test
     **/
    function an_order_resource_outputs_voucher()
    {
        $product = factory(Product::class)->create([
            'pricing_in_pence' => ['gbp' => 5536],
            'shipping_in_pence' => [],
        ]);
        $voucher = factory(Voucher::class)->create([
            'code' => 'BOB3',
            'percentage' => true,
            'value' => 10,
            'on_basket' => true,
        ]);
        
        $order = OrderFactory::make('gbp')
            ->add($product)->applyVoucher($voucher)->save();
        
        $resource = OrderResource::make($order)->toArray(request());
        
        $this->assertEquals(0, Arr::get($resource, 'totals.shipping_in_pence'));
        $this->assertEquals(554, Arr::get($resource, 'totals.discount_in_pence'));
        $this->assertEquals('BOB3', Arr::get($resource, 'voucher'));
    }
}