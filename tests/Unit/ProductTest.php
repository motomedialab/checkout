<?php

namespace Motomedialab\Checkout\Tests\Unit;

use Motomedialab\Checkout\Models\Product;
use Motomedialab\Checkout\Tests\TestCase;

class ProductTest extends TestCase
{
    /**
     * @test
     **/
    function we_can_create_a_product()
    {
        $p = factory(Product::class)->create();
        
        $this->assertInstanceOf(Product::class, $p->fresh());
    }
    
    /**
     * @test
     **/
    function a_product_can_be_owned_by_another_product()
    {
        $parent = factory(Product::class)->create();
        $child = factory(Product::class)->create([
            'parent_product_id' => $parent->getKey(),
        ]);
        
        $this->assertInstanceOf(Product::class, $child->parent);
    }
    
    /**
     * @test
     **/
    function a_product_can_load_price_based_on_currency()
    {
        /** @var Product $product */
        $product = factory(Product::class)->create([
            'pricing' => [
                'gbp' => 12410
            ]
        ]);
        
        $this->assertEquals(124.10, $product->price('gbp')->toFloat());
    }
    
    /**
     * @test
     **/
    function a_product_with_no_currency_does_something()
    {
        $product = factory(Product::class)->create([
            'pricing' => [
                'gbp' => 12410
            ]
        ]);
    
        $this->assertNull($product->price('eur')?->toFloat());
    }
    
    /**
     * @test
     **/
    function a_variant_loads_price_accounting_for_parent()
    {
        $parent = factory(Product::class)->create(['pricing' => ['gbp' => 12410]]);
        
        /** @var Product $child */
        $child = factory(Product::class)->create([
            'parent_product_id' => $parent->getKey(),
            'pricing' => ['gbp' => 200]
        ]);
        
        $this->assertEquals(126.10, $child->price('gbp')->toFloat());
    }
    
    /**
     * @test
     **/
    function a_variant_with_no_price_adopts_parent_price()
    {
        $parent = factory(Product::class)->create(['pricing' => ['gbp' => 12410]]);
        /** @var Product $child */
        $child = factory(Product::class)->create([
            'parent_product_id' => $parent->getKey(),
            'pricing' => []
        ]);
        
        $this->assertEquals(124.10, $child->price('gbp')->toFloat());
    }
    
    /**
     * @test
     **/
    function a_variant_with_no_parent_price_adopts_variant_price()
    {
        $parent = factory(Product::class)->create(['pricing' => []]);
        /** @var Product $child */
        $child = factory(Product::class)->create([
            'parent_product_id' => $parent->getKey(),
            'pricing' => ['gbp' => 12410]
        ]);
    
        $this->assertEquals(124.10, $child->price('gbp')->toFloat());
    }
}