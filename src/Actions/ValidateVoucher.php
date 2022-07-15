<?php

namespace Motomedialab\Checkout\Actions;

use Motomedialab\Checkout\Contracts\ValidatesVoucher;
use Motomedialab\Checkout\Factories\OrderFactory;
use Motomedialab\Checkout\Models\Voucher;

class ValidateVoucher implements ValidatesVoucher
{
    public function __invoke(OrderFactory $factory, Voucher $voucher): bool
    {
        return true;
    }
}