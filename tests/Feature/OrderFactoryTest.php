<?php

namespace Motomedialab\Checkout\Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Motomedialab\Checkout\Enums\OrderStatus;
use Motomedialab\Checkout\Enums\ProductStatus;
use Motomedialab\Checkout\Factories\OrderFactory;
use Motomedialab\Checkout\Models\Order;
use Motomedialab\Checkout\Models\Product;
use Motomedialab\Checkout\Tests\TestCase;

class OrderFactoryTest extends TestCase
{
    /**
     * @test
     **/
    function a_new_order_can_be_created()
    {
        $product = Product::factory()->create([
            'pricing_in_pence' => [
                'gbp' => 3000, // £30
            ],
            'shipping_in_pence' => [
                'gbp' => 200,
            ]
        ]);

        $order = OrderFactory::make('gbp')->add($product, 2);

        $order = $order->save();

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals(6000, $order->amount->toPence()); // 2 products @ £30 each
        $this->assertEquals(200, $order->shipping->toPence());
        $this->assertEquals(6200, $order->total->toPence());
    }

    /**
     * @test
     **/
    function an_existing_order_can_be_retrieved()
    {
        // seed our previous order
        $order = OrderFactory::make('gbp')
            ->add(Product::factory()->create(), 3)
            ->add(Product::factory()->create())
            ->save();

        // load our order back into our factory & add one more product
        $factory = OrderFactory::fromExisting(Order::findByUuid($order->uuid))
            ->add(Product::factory()->create(), 1);

        $this->assertInstanceOf(OrderFactory::class, $factory);

        $order = $factory->save()->fresh();

        // we should now have 5 items in our basket...
        $this->assertEquals(5, $order->products->map(fn ($product) => $product->orderPivot->quantity)->sum());
    }

    /**
     * @test
     **/
    function an_unavailable_product_cannot_be_added()
    {
        $this->expectException(\Exception::class);

        $product = Product::factory()->create(['status' => ProductStatus::UNAVAILABLE]);

        OrderFactory::make('gbp')
            ->add($product);
    }

    /**
     * @test
     **/
    function an_address_can_be_set()
    {
        $address = [
            'address_line_1' => 'Just testing',
        ];

        $order = OrderFactory::make('gbp')->setAddress($address)->save();

        $this->assertEquals($address, $order->recipient_address);
        $this->assertEquals(OrderStatus::PENDING, $order->fresh()->status);
    }

    /**
     * @test
     **/
    function products_can_be_incremented()
    {
        $product = Product::factory()->create();

        $factory = OrderFactory::make('gbp')->add($product);

        // add another 3 items of the same product (increment)
        $order = $factory->add($product, 3, true)->save()->fresh();

        $this->assertCount(1, $order->products);
        $this->assertEquals(4, $order->products->first()->orderPivot->quantity);
    }

    /**
     * @test
     */
    function metadata_can_be_added_to_order_products()
    {
        $metadata = [
            'vehicle_details' => 'Triumph Daytona',
            'vehicle_registration' => 'LR14TUU',
            'dealer_id' => 1
        ];

        $product = Product::factory()->create();

        $factory = OrderFactory::make('gbp')->add($product, metadata: $metadata);

        $order = $factory->save()->fresh();

        $this->assertEquals($metadata, $order->products->first()->orderPivot->metadata);
    }
}