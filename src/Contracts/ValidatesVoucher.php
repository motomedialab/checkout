<?php

namespace Motomedialab\Checkout\Contracts;

use Illuminate\Support\Collection;
use Motomedialab\Checkout\Exceptions\InvalidVoucherException;
use Motomedialab\Checkout\Models\Voucher;

interface ValidatesVoucher
{
    /**
     * @throws InvalidVoucherException
     */
    public function __invoke(Voucher $voucher, ?Collection $products = null, ?CheckoutUser $owner = null): bool;
}
