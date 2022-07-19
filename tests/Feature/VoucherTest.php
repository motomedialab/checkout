<?php

namespace Motomedialab\Checkout\Tests\Feature;

use Motomedialab\Checkout\Factories\OrderFactory;
use Motomedialab\Checkout\Models\Product;
use Motomedialab\Checkout\Models\Voucher;
use Motomedialab\Checkout\Tests\TestCase;

class VoucherTest extends TestCase
{
    
    /**
     * @test
     **/
    function a_voucher_with_percentage_is_applied_to_basket()
    {
        $voucher = factory(Voucher::class)->create([
            'value' => 5,
            'percentage' => true,
            'on_basket' => true,
        ]);
        $product = factory(Product::class)->create([
            'pricing' => ['gbp' => 10000],
        ]);
        
        $order = OrderFactory::make('gbp')
            ->add($product)
            ->applyVoucher($voucher)
            ->save();
        
        $this->assertInstanceOf(Voucher::class, $order->voucher);
        $this->assertEquals(500, $order->discount_in_pence);
    }
    
    /**
     * @test
     **/
    function a_voucher_with_value_is_applied_to_basket()
    {
        $voucher = factory(Voucher::class)->create([
            'value' => 5,
            'percentage' => false,
            'on_basket' => true,
        ]);
        $product = factory(Product::class, 2)->create([
            'pricing' => ['gbp' => 10000],
        ]);
        
        $order = OrderFactory::make('gbp')
            ->add($product->first())
            ->add($product->last())
            ->applyVoucher($voucher)
            ->save();
        
        $this->assertInstanceOf(Voucher::class, $order->voucher);
        $this->assertEquals(500, $order->discount_in_pence);
    }
    
    /**
     * @test
     **/
    function a_voucher_with_percentage_unit_price_is_applied_to_products()
    {
        $products = factory(Product::class, 3)->create([
            'pricing' => ['gbp' => 10000],
        ]);
        $voucher = factory(Voucher::class)->create([
            'value' => 5,
            'percentage' => true,
            'on_basket' => false,
            'quantity_price' => false,
        ]);
        
        $voucher->products()->attach($products->first());
        
        $factory = OrderFactory::make('gbp');
        $products->each(fn($product) => $factory->add($product));
        $factory->add($products->first(), 2);
        
        $order = $factory->applyVoucher($voucher->fresh())->save();
        
        // we'd expect the price to be 40000 minus 5% of one product
        $this->assertEquals(500, $order->discount_in_pence);
    }
    
    /**
     * @test
     **/
    function a_voucher_with_percentage_quantity_price_is_applied_to_products()
    {
        $products = factory(Product::class, 3)->create([
            'pricing' => ['gbp' => 10000],
        ]);
        $voucher = factory(Voucher::class)->create([
            'value' => 5,
            'percentage' => true,
            'on_basket' => false,
            'quantity_price' => true,
        ]);
        
        $voucher->products()->attach($products->first());
        
        $factory = OrderFactory::make('gbp');
        $products->each(fn($product) => $factory->add($product));
        $factory->add($products->first(), 2);
        
        $order = $factory->applyVoucher($voucher->fresh())->save();
        
        // we'd expect the price to be 40000 minus 1000 (5% of 2 x product)
        $this->assertEquals(1000, $order->discount_in_pence);
    }
    
}