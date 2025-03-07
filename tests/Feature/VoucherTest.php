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
    public function a_voucher_with_percentage_is_applied_to_basket()
    {
        // we have a product that costs £100
        $product = Product::factory()->create(['pricing_in_pence' => ['gbp' => 10000]]);

        // and a 5% voucher
        $voucher = Voucher::factory()->create([
            'value' => 5,
            'percentage' => true,
            'on_basket' => true,
            'quantity_price' => false,
        ]);

        // the product is added to the basket 3 times (£300)
        $order = OrderFactory::make('gbp')
            ->add($product, 3)
            ->applyVoucher($voucher)
            ->save();

        // we'd now expect the discount to be 5% of £300 - (£15)
        $this->assertEquals(1500, $order->discount_in_pence);
    }

    /**
     * @test
     **/
    public function a_voucher_with_value_is_applied_to_basket()
    {
        // we have two products that cost £100
        $product = Product::factory(2)->create(['pricing_in_pence' => ['gbp' => 10000]]);

        // and a £5 off voucher
        $voucher = Voucher::factory()->create([
            'value' => 5,
            'percentage' => false,
            'on_basket' => true,
        ]);

        // when we add both products to the basket
        $order = OrderFactory::make('gbp')
            ->add($product->first())
            ->add($product->last(), 2)
            ->applyVoucher($voucher)
            ->save();

        // we'd still only expect a £5 discount
        $this->assertEquals(500, $order->discount_in_pence);
    }

    /**
     * @test
     **/
    public function a_voucher_with_percentage_unit_price_is_applied_to_single_product()
    {
        $products = Product::factory(3)->create([
            'pricing_in_pence' => ['gbp' => 10000],
        ]);
        $voucher = Voucher::factory()->create([
            'value' => 5,
            'percentage' => true,
            'on_basket' => false,
            'quantity_price' => false,
        ]);

        $voucher->products()->attach($products->first());

        $factory = OrderFactory::make('gbp');
        $products->each(fn ($product) => $factory->add($product));
        $factory->add($products->first(), 2);

        $order = $factory->applyVoucher($voucher->fresh())->save();

        // we'd expect the price to be 40000 minus 5% of one product
        $this->assertEquals(500, $order->discount_in_pence);
    }

    /**
     * @test
     **/
    public function a_single_use_percentage_voucher_will_apply_to_all_applicable_products()
    {
        // we have a £10 and £15 product
        $product1 = Product::factory()->create(['pricing_in_pence' => ['gbp' => 1000]]);
        $product2 = Product::factory()->create(['pricing_in_pence' => ['gbp' => 1500]]);

        // we have a voucher that offers 10% off product1 OR product2
        $voucher = tap(Voucher::factory()->create([
            'percentage' => true,
            'value' => 10,
            'on_basket' => false,
            'quantity_price' => false,
        ]), fn (Voucher $voucher) => $voucher->products()->sync([$product1->getKey(), $product2->getKey()]));

        $this->assertCount(2, $voucher->products);

        // we add product 1 & 2 to our order with our voucher
        $order = OrderFactory::make('gbp')
            ->add($product1)
            ->add($product2)
            ->applyVoucher($voucher)
            ->save();

        // the discount should equate to 10% of product 1 + 10% of product 2 (£2.50)
        $this->assertEquals(250, $order->discount_in_pence);
    }

    /**
     * @test
     **/
    public function a_quantity_price_voucher_will_apply_to_multiples_of_single_product()
    {
        $voucher = Voucher::factory()
            ->has(Product::factory(['pricing_in_pence' => ['gbp' => 3000]]))
            ->create([
                'percentage' => false,
                'value' => 10, // £10 voucher
                'on_basket' => false,
                'quantity_price' => true,
            ]);

        $order = OrderFactory::make('gbp')
            ->add($voucher->products->first(), 3)
            ->applyVoucher($voucher)
            ->save();

        // with 3 products, we'd expect a £30 discount...
        $this->assertEquals(9000, $order->amount_in_pence);
        $this->assertEquals(3000, $order->discount_in_pence);
    }

    /**
     * @test
     **/
    public function a_voucher_will_not_exceed_the_value_of_the_basket()
    {
        // create a £10 product
        $product = Product::factory()->create(['pricing_in_pence' => ['gbp' => 1000]]);

        // and a £20 voucher
        $voucher = Voucher::factory()->create([
            'value' => 20,
            'percentage' => false,
            'on_basket' => true,
        ]);

        $order = OrderFactory::make('gbp')
            ->add($product)
            ->applyVoucher($voucher)
            ->save();

        $this->assertEquals(1000, $order->discount_in_pence);
    }

    /**
     * @test
     **/
    public function a_voucher_with_percentage_quantity_price_is_applied_to_products()
    {
        // we have three products that cost £100
        $products = Product::factory(3)->create([
            'pricing_in_pence' => ['gbp' => 10000],
        ]);

        // and a voucher that grants you 5% off the entire quantity
        // of product 1 ONLY. 5% of £100 = £5
        $voucher = tap(Voucher::factory()->create([
            'value' => 5,
            'percentage' => true,
            'on_basket' => false,
            'quantity_price' => true,
        ]), fn ($voucher) => $voucher->products()->attach($products->first()));

        // now create a basket and add each product to it
        $factory = OrderFactory::make('gbp');
        $products->each(fn ($product) => $factory->add($product));

        // add our discounted product a further 2 times (3 times in total)
        $factory->add($products->first(), 2, true);

        // and apply the voucher to our order
        $order = $factory->applyVoucher($voucher->fresh())->save();

        // we'd expect the order value to be £100 x 5 (£500) minus £15 (5% of 3 x first product)
        $this->assertEquals(1500, $order->discount_in_pence);
        $this->assertEquals(50000, $order->amount_in_pence);
    }
}
