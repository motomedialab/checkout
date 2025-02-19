<?php

namespace Motomedialab\Checkout\Tests\Unit;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Motomedialab\Checkout\Contracts\CalculatesProductsValue;
use Motomedialab\Checkout\Models\Product;
use Motomedialab\Checkout\Tests\TestCase;

class CalculatesProductValueTest extends TestCase
{
    use LazilyRefreshDatabase;
    
    /**
     * @test
     **/
    function a_products_price_can_be_calculated()
    {
        $product = Product::factory()->make(['pricing_in_pence' => ['gbp' => 999]]);
        
        $this->assertEquals(
            999,
            app(CalculatesProductsValue::class)(collect([$product]), 'gbp')
        );
    }
    
    /**
     * @test
     **/
    function multiple_products_are_summed()
    {
        $products = Product::factory(3)->create(['pricing_in_pence' => ['gbp' => 999]]);
        
        $this->assertEquals(
            999 * 3,
            app(CalculatesProductsValue::class)($products, 'gbp')
        );
    }
    
    /**
     * @test
     **/
    function a_child_adopts_its_parent_price()
    {
        $parent = Product::factory()->create(['pricing_in_pence' => ['gbp' => 999]]);
        $child = Product::factory()->create(['pricing_in_pence' => [], 'parent_product_id' => $parent->getKey()]);
    
        $this->assertEquals(
            999,
            app(CalculatesProductsValue::class)(collect([$child]), 'gbp')
        );
    }
    
    /**
     * @test
     **/
    function a_child_will_be_added_to_its_parent_price()
    {
        $parent = Product::factory()->create(['pricing_in_pence' => ['gbp' => 999]]);
        $child = Product::factory()->create(['pricing_in_pence' => ['gbp' => 100], 'parent_product_id' => $parent->getKey()]);
    
        $this->assertEquals(
            1099,
            app(CalculatesProductsValue::class)(collect([$child]), 'gbp')
        );
    }
}