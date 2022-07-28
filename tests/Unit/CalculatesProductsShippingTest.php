<?php

namespace Motomedialab\Checkout\Tests\Unit;

use Motomedialab\Checkout\Contracts\CalculatesProductsShipping;
use Motomedialab\Checkout\Models\Product;
use Motomedialab\Checkout\Tests\TestCase;

class CalculatesProductsShippingTest extends TestCase
{
    
    /**
     * @test
     **/
    function shipping_can_be_calculated()
    {
        $product = Product::factory()->make(['shipping_in_pence' => ['gbp' => 999]]);
        
        $this->assertEquals(
            999,
            app(CalculatesProductsShipping::class)(collect([$product]), 'gbp')
        );
    }
    
    /**
     * @test
     **/
    function no_shipping_returns_zero()
    {
        $product = Product::factory()->make(['shipping_in_pence' => []]);
        
        $this->assertEquals(
            0,
            app(CalculatesProductsShipping::class)(collect([$product]), 'gbp')
        );
    }
    
    /**
     * @test
     **/
    function shipping_respects_parent_products()
    {
        $parent = Product::factory()->create(['shipping_in_pence' => ['gbp' => 999]]);
        $child = Product::factory()->create(['shipping_in_pence' => [], 'parent_product_id' => $parent->getKey()]);
        
        $this->assertEquals(
            999,
            app(CalculatesProductsShipping::class)(collect([$child]), 'gbp')
        );
    }
    
    /**
     * @test
     **/
    function child_shipping_price_takes_priority()
    {
        $parent = Product::factory()->create(['shipping_in_pence' => ['gbp' => 999]]);
        $child = Product::factory()->create([
            'shipping_in_pence' => ['gbp' => 1299],
            'parent_product_id' => $parent->getKey()
        ]);
        
        $this->assertEquals(
            1299,
            app(CalculatesProductsShipping::class)(collect([$child]), 'gbp')
        );
    }
    
    /**
     * @test
     **/
    function multiple_products_returns_the_higher_shipping_value()
    {
        $product1 = Product::factory()->make(['shipping_in_pence' => ['gbp' => 999]]);
        $product2 = Product::factory()->make(['shipping_in_pence' => ['gbp' => 1299]]);
        
        $this->assertEquals(
            1299,
            app(CalculatesProductsShipping::class)(collect([$product1, $product2]), 'gbp')
        );
    }
}