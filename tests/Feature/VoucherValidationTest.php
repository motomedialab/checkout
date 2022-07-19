<?php

namespace Motomedialab\Checkout\Tests\Feature;

use Motomedialab\Checkout\Contracts\ValidatesVoucher;
use Motomedialab\Checkout\Exceptions\InvalidVoucherException;
use Motomedialab\Checkout\Factories\OrderFactory;
use Motomedialab\Checkout\Models\Voucher;
use Motomedialab\Checkout\Tests\TestCase;

class VoucherValidationTest extends TestCase
{
    /**
     * @test
     **/
    function a_voucher_that_isnt_valid_yet_throws_error()
    {
        $this->expectException(InvalidVoucherException::class);
        
        $voucher = factory(Voucher::class)->create(['valid_from' => now()->addDay()]);
        
        app(ValidatesVoucher::class)($voucher);
    }
    
    /**
     * @test
     **/
    function a_voucher_that_has_expired_throws_error()
    {
        $this->expectException(InvalidVoucherException::class);
        
        $voucher = factory(Voucher::class)->create(['valid_until' => now()->subDay()]);
        
        app(ValidatesVoucher::class)($voucher);
    }
    
    /**
     * @test
     **/
    function a_voucher_that_has_been_used_max_times_throws_error()
    {
        $this->expectException(InvalidVoucherException::class);
        
        $voucher = factory(Voucher::class)->create([
            'max_uses' => 2,
            'total_uses' => 2,
        ]);
        
        app(ValidatesVoucher::class)($voucher);
    }
    
}