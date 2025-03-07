<?php

namespace Motomedialab\Checkout\Contracts;

use Illuminate\Support\Collection;
use Motomedialab\Checkout\Models\Voucher;

/**
 * Compare one voucher against another, and work out which
 * provides the better value.
 */
interface ComparesVoucher
{
    public function __invoke(
        Collection $products,
        string $currency,
        Voucher $oldVoucher,
        Voucher $newVoucher,
        ?CheckoutUser $owner
    );
}
