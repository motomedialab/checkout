<?php

namespace Motomedialab\Checkout\Actions;

use Illuminate\Support\Collection;
use Motomedialab\Checkout\Contracts\CheckoutUser;
use Motomedialab\Checkout\Contracts\ValidatesVoucher;
use Motomedialab\Checkout\Exceptions\InvalidVoucherException;
use Motomedialab\Checkout\Exceptions\VoucherNotApplicableException;
use Motomedialab\Checkout\Models\Voucher;

class ValidateVoucher implements ValidatesVoucher
{
    public function __invoke(Voucher $voucher, ?Collection $products = null, ?CheckoutUser $owner = null): bool
    {
        if (true === $voucher->valid_from?->gt(now())) {
            throw new InvalidVoucherException('This voucher is not yet valid');
        }
        
        if (true === $voucher->valid_until?->lt(now())) {
            throw new InvalidVoucherException('This voucher has expired');
        }
        
        if ($voucher->max_uses > 0 && $voucher->total_uses >= $voucher->max_uses) {
            throw new InvalidVoucherException('This voucher is no longer valid');
        }
        
        if ($voucher->on_basket || is_null($products) || $products?->isEmpty()) {
            return true;
        }
        
        // check if our voucher contains any of the products submitted
        if (!$voucher->products->keyBy('id')->hasAny($products->keyBy('id')->keys()->toArray())) {
            throw new VoucherNotApplicableException('This voucher is not valid for any items in your basket');
        }
        
        return true;
    }
}