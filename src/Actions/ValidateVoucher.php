<?php

namespace Motomedialab\Checkout\Actions;

use Motomedialab\Checkout\Contracts\ValidatesVoucher;
use Motomedialab\Checkout\Exceptions\InvalidVoucherException;
use Motomedialab\Checkout\Models\Voucher;

class ValidateVoucher implements ValidatesVoucher
{
    public function __invoke(Voucher $voucher): bool
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
        
        
        return true;
    }
}