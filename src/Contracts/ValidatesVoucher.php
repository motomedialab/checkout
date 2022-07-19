<?php

namespace Motomedialab\Checkout\Contracts;

use Motomedialab\Checkout\Exceptions\InvalidVoucherException;
use Motomedialab\Checkout\Models\Voucher;

interface ValidatesVoucher
{
    
    /**
     * @param  Voucher  $voucher
     *
     * @return bool
     * @throws InvalidVoucherException
     */
    public function __invoke(Voucher $voucher): bool;
}