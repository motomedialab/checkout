<?php

namespace Motomedialab\Checkout\Contracts;

use Illuminate\Support\Collection;
use Motomedialab\Checkout\Exceptions\InvalidVoucherException;
use Motomedialab\Checkout\Models\Voucher;

interface ValidatesVoucher
{
    
    /**
     * @param  Voucher  $voucher
     * @param  Collection|null  $products
     *
     * @return bool
     * @throws InvalidVoucherException
     */
    public function __invoke(Voucher $voucher, ?Collection $products = null): bool;
}