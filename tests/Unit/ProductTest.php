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
        $p = Product::factory()->create();
        
        $this->assertInstanceOf(Product::class, $p->fresh());
    }
    
    /**
     * @test
     **/
    function a_product_can_be_owned_by_another_product()
    {
        $parent = Product::factory()->create();
        $child = Product::factory()->create([
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
        $product = Product::factory()->create([
            'pricing_in_pence' => [
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
        $product = Product::factory()->make([
            'pricing_in_pence' => [
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
        $parent = Product::factory()->create(['pricing_in_pence' => ['gbp' => 12410]]);
        
        /** @var Product $child */
        $child = Product::factory()->create([
            'parent_product_id' => $parent->getKey(),
            'pricing_in_pence' => ['gbp' => 200]
        ]);
        
        $this->assertEquals(126.10, $child->price('gbp')->toFloat());
    }
    
    /**
     * @test
     **/
    function a_variant_with_no_price_adopts_parent_price()
    {
        $parent = Product::factory()->create(['pricing_in_pence' => ['gbp' => 12410]]);
        /** @var Product $child */
        $child = Product::factory()->create([
            'parent_product_id' => $parent->getKey(),
            'pricing_in_pence' => []
        ]);
        
        $this->assertEquals(124.10, $child->price('gbp')->toFloat());
    }
    
    /**
     * @test
     **/
    function a_variant_with_no_parent_price_adopts_variant_price()
    {
        $parent = Product::factory()->create(['pricing_in_pence' => []]);
        /** @var Product $child */
        $child = Product::factory()->create([
            'parent_product_id' => $parent->getKey(),
            'pricing_in_pence' => ['gbp' => 12410]
        ]);
    
        $this->assertEquals(124.10, $child->price('gbp')->toFloat());
    }
}