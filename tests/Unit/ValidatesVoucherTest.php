<?php

namespace Motomedialab\Checkout\Tests\Unit;

use Motomedialab\Checkout\Contracts\ValidatesVoucher;
use Motomedialab\Checkout\Exceptions\InvalidVoucherException;
use Motomedialab\Checkout\Exceptions\VoucherNotApplicableException;
use Motomedialab\Checkout\Models\Product;
use Motomedialab\Checkout\Models\Voucher;
use Motomedialab\Checkout\Tests\TestCase;

class ValidatesVoucherTest extends TestCase
{
    /**
     * @test
     **/
    public function a_voucher_that_isnt_valid_yet_throws_error()
    {
        $this->expectException(InvalidVoucherException::class);

        $voucher = Voucher::factory()->create(['valid_from' => now()->addDay()]);

        app(ValidatesVoucher::class)($voucher);
    }

    /**
     * @test
     **/
    public function a_voucher_that_has_expired_throws_error()
    {
        $this->expectException(InvalidVoucherException::class);

        $voucher = Voucher::factory()->create(['valid_until' => now()->subDay()]);

        app(ValidatesVoucher::class)($voucher);
    }

    /**
     * @test
     **/
    public function a_voucher_that_has_been_used_max_times_throws_error()
    {
        $this->expectException(InvalidVoucherException::class);

        $voucher = Voucher::factory()->create([
            'max_uses' => 2,
            'total_uses' => 2,
        ]);

        app(ValidatesVoucher::class)($voucher);
    }

    /**
     * @test
     **/
    public function a_voucher_that_is_given_products_validates_against_product_array()
    {
        $this->expectException(VoucherNotApplicableException::class);

        $voucher = Voucher::factory()
            ->has(Product::factory(2), 'products')
            ->create([
                'on_basket' => false,
            ]);

        // create a product that isn't applicable for this voucher
        $product = Product::factory()->create();

        // when we apply a valid product, it should succeed
        $this->assertTrue(
            app(ValidatesVoucher::class)($voucher, collect([$voucher->products->first()]))
        );

        // otherwise it should throw an exception
        app(ValidatesVoucher::class)($voucher, collect([$product]));
    }
}
