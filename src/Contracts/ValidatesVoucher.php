<?php

namespace Motomedialab\Checkout\Contracts;

use Motomedialab\Checkout\Exceptions\InvalidVoucherException;
use Motomedialab\Checkout\Factories\OrderFactory;
use Motomedialab\Checkout\Models\Voucher;

interface ValidatesVoucher
{
    
    /**
     * @param  OrderFactory  $factory
     * @param  Voucher  $voucher
     *
     * @return bool
     * @throws InvalidVoucherException
     */
    public function __invoke(OrderFactory $factory, Voucher $voucher): bool;
}