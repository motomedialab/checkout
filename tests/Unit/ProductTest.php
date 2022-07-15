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
        
        // easy way of retrieving a price
        
        $this->assertEquals(124.10, $product->price('gbp')?->toFloat());
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
}