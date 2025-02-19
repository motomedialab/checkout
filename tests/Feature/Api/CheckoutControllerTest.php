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
            ],
        ])->assertJsonValidationErrorFor('products.0.id');
    }

    /**
     * @test
     **/
    function an_order_can_be_created()
    {
        $product = Product::factory()->create();

        $metadata = [
            'vehicle_details' => 'Triumph Daytona',
            'vehicle_registration' => 'LR14TUU',
            'dealer_id' => 1
        ];

        $response = $this->postJson(route('checkout.store'), [
            'currency' => 'gbp',
            'products' => [
                ['id' => $product->getKey(), 'quantity' => 3, 'metadata' => $metadata],
            ],
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'products',
                    'totals',
                    'voucher',
                ],
            ]);

        $order = Order::findByUuid($response->json('data.id'));
        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals(OrderStatus::PENDING, $order->status);
        $this->assertEquals(3, $response->json('data.products.0.quantity'));
        $this->assertEquals($product->getKey(), $response->json('data.products.0.id'));
        $this->assertEquals($metadata, $response->json('data.products.0.metadata'));
    }

    /**
     * @test
     **/
    function a_product_with_children_cannot_be_ordered()
    {
        // create a parent product that has one child
        $product = Product::factory()->create();
        Product::factory()->create(['parent_product_id' => $product]);

        $response = $this->postJson(route('checkout.store'), [
            'currency' => 'gbp',
            'products' => [
                [
                    'id' => $product->getKey(),
                    'quantity' => 1,
                ],
            ],
        ])->assertJsonValidationErrorFor('products.0.id');
    }

    /**
     * @test
     **/
    function setting_a_quantity_to_zero_without_increment_removes_it()
    {
        $product = Product::factory()->create();
        $order = OrderFactory::make('gbp')->add($product, 1)->save();

        $this->putJson(route('checkout.update', $order), [
            'increment' => false,
            'products' => [
                [
                    'id' => $product->getKey(),
                    'quantity' => 0,
                ],
            ],
        ])->assertStatus(200);

        $this->assertCount(0, $order->fresh()->products);
    }

    /**
     * @test
     **/
    function a_voucher_can_be_applied_to_an_existing_order()
    {
        $product = Product::factory()->create();
        $order = OrderFactory::make('gbp')->add($product, 1)->save();
        $voucher = Voucher::factory()->create([
            'on_basket' => true,
            'percentage' => false,
            'value' => 10,
        ]); // 10 GBP voucher.

        $this->putJson(route('checkout.update', $order), [
            'voucher' => $voucher->code,
        ])->assertStatus(200)
            ->assertJson(['data' => ['voucher' => $voucher->code]]);
    }

    /**
     * @test
     **/
    function a_voucher_value_is_persisted_after_purchase()
    {
        $voucher = Voucher::factory()
            ->has(Product::factory(['pricing_in_pence' => ['gbp' => 4000]]))
            ->create([
                'on_basket' => false,
                'percentage' => false,
                'value' => 10,
            ]); // 10 GBP voucher.


        $order = OrderFactory::make('gbp')
            ->applyVoucher($voucher)
            ->add($voucher->products->first())->save();

        $this->assertEquals(1000, $order->discount_in_pence);

        $order = OrderFactory::fromExisting($order)->save(OrderStatus::AWAITING_PAYMENT);

        $this->assertEquals(1000, $order->discount_in_pence);
    }

    /**
     * @test
     **/
    function a_voucher_of_a_lesser_value_is_not_applied()
    {
        // create an order
        $product = Product::factory()->create();
        $order = OrderFactory::make('gbp')->add($product, 1)
            ->applyVoucher(Voucher::factory()->create(['on_basket' => true, 'percentage' => false, 'value' => 10,]))
            ->save();

        // create a lesser valued voucher
        $voucher = Voucher::factory()->create(['on_basket' => true, 'percentage' => false, 'value' => 3]);

        // try to apply it and check it wasn't applied.
        $this
            ->putJson(route('checkout.update', $order), ['voucher' => $voucher->code])
            ->assertStatus(200)
            ->assertJson(['data' => ['totals' => ['discount_in_pence' => 1000]]]);
    }

    /**
     * @test
     **/
    function a_pending_order_can_be_updated()
    {
        $product = Product::factory()->create();
        $order = OrderFactory::make('gbp')->add($product)->save();

        $response = $this->putJson(route('checkout.update', ['order' => $order->uuid]), [
            'increment' => true,
            'products' => [
                [
                    'id' => $product->getKey(),
                    'quantity' => 2,
                ],
            ],
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'products',
                    'totals',
                ],
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

        $product = Product::factory()->create([
            'pricing_in_pence' => ['gbp' => 10000], // make the product £10
            'shipping_in_pence' => [],
        ]);

        $voucher = Voucher::factory()->create([
            'code' => 'ADVANTAGE',
            'on_basket' => true,
            'percentage' => true,
            'value' => 10, // 10% discount voucher
        ]);

        $response = $this->postJson(route('checkout.store'), [
            'currency' => 'gbp',
            'voucher' => $voucher->code,
            'products' => [
                ['id' => $product->getKey(), 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(201);

        $this->assertEquals(1000, $response->json('data.totals.discount_in_pence'));
        $this->assertEquals('ADVANTAGE', $response->json('data.voucher'));
    }

    /**
     * @test
     **/
    function a_voucher_can_be_removed_from_order()
    {
        // mock up an order
        $order = OrderFactory::make('gbp')
            ->add(Product::factory()->create(['pricing_in_pence' => ['gbp' => 10000]]))
            ->applyVoucher(Voucher::factory()->create(['value' => 5, 'percentage' => false, 'on_basket' => true]))
            ->save();

        $this->assertInstanceOf(Voucher::class, $order->fresh()->voucher);

        $response = $this->deleteJson(route('checkout.voucher.destroy', $order));

        $response->assertOk();
        $this->assertNull($order->fresh()->voucher);

    }

    /**
     * @test
     **/
    function a_voucher_can_be_removed_from_an_order()
    {
        $voucher = Voucher::factory()->create();
        $order = OrderFactory::make('gbp')->applyVoucher($voucher)->save();

        $response = $this->putJson(route('checkout.update', $order), ['voucher' => null]);

        $response->assertStatus(200);
        $this->assertNull($response->json('data.voucher'));
    }

    /**
     * @test
     **/
    function an_active_order_cannot_be_updated()
    {
        $product = Product::factory()->create();
        $order = OrderFactory::make('gbp')->add($product, 1)->save();
        $order->setStatus(OrderStatus::AWAITING_PAYMENT)->save();

        $this->putJson(route('checkout.update', ['order' => $order->uuid]))
            ->assertStatus(403);
    }

    /**
     * @test
     **/
    function a_voucher_exception_returns_to_browser()
    {
        $response = $this->postJson(route('checkout.store'), [
            'currency' => 'gbp',
            'voucher' => 'test',
        ]);

        $response->assertStatus(422);
        $this->assertEquals('Unknown voucher code', $response->json('errors.voucher.0'));
    }

}