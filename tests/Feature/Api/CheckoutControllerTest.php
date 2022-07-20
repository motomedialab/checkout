<?php
/**
 * @author MotoMediaLab <hello@motomedialab.com>
 * Created at: 20/07/2022
 */

namespace Motomedialab\Checkout\Tests\Feature\Api;

use Motomedialab\Checkout\Enums\OrderStatus;
use Motomedialab\Checkout\Factories\OrderFactory;
use Motomedialab\Checkout\Models\Order;
use Motomedialab\Checkout\Models\Product;
use Motomedialab\Checkout\Models\Voucher;
use Motomedialab\Checkout\Tests\TestCase;

class CheckoutControllerTest extends TestCase
{
    
    /**
     * @test
     **/
    function checkout_store_requires_existing_product()
    {
        $this->postJson(route('checkout.store'), [
            'currency' => 'gbp',
            'products' => [
                ['id' => 1, 'quantity' => 1],
            ]
        ])->assertJsonValidationErrorFor('products.0.id');
    }
    
    /**
     * @test
     **/
    function an_order_can_be_created()
    {
        $product = factory(Product::class)->create();
        
        $response = $this->postJson(route('checkout.store'), [
            'currency' => 'gbp',
            'products' => [
                ['id' => $product->getKey(), 'quantity' => 3],
            ]
        ]);
        
        $response
            ->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'products',
                    'totals',
                    'voucher'
                ]
            ]);
        
        $order = Order::findByUuid($response->json('data.id'));
        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals(OrderStatus::PENDING, $order->status);
        $this->assertEquals(3, $response->json('data.products.0.quantity'));
        $this->assertEquals($product->getKey(), $response->json('data.products.0.id'));
    }
    
    /**
     * @test
     **/
    function a_pending_order_can_be_updated()
    {
        $product = factory(Product::class)->create();
        $order = OrderFactory::make('gbp')->add($product, 1)->save();
        
        $response = $this->putJson(route('checkout.update', ['order' => $order->uuid]), [
            'increment' => true,
            'products' => [
                [
                    'id' => $product->getKey(),
                    'quantity' => 2,
                ],
            ]
        ]);
        
        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'products',
                    'totals',
                ]
            ]);
    
        $this->assertEquals(3, $response->json('data.products.0.quantity'));
    }
    
    /**
     * @test
     **/
    function a_pending_order_can_be_deleted()
    {
        $order = OrderFactory::make('gbp')->save();
        
        $this->deleteJson(route('checkout.destroy', $order))
            ->assertStatus(204);
        
        $this->assertNull($order->fresh());
    }
    
    /**
     * @test
     **/
    function a_voucher_can_be_added_to_order()
    {
        $product = factory(Product::class)->create();
        $voucher = factory(Voucher::class)->create();
        
        $response = $this->postJson(route('checkout.store'), [
            'currency' => 'gbp',
            'voucher' => $voucher->code,
            'products' => [
                ['id' => $product->getKey(), 'quantity' => 1]
            ]
        ]);
        
        dd($response->getStatusCode(), $response->json());
    }
    
    /**
     * @test
     **/
    function an_active_order_cannot_be_updated()
    {
        $product = factory(Product::class)->create();
        $order = OrderFactory::make('gbp')->add($product, 1)->save();
        $order->setStatus(OrderStatus::AWAITING_PAYMENT)->save();
        
        $this->putJson(route('checkout.update', ['order' => $order->uuid]))
            ->assertStatus(404);
    }
    
}