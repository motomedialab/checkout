<?php

namespace Motomedialab\Checkout\Actions;

use Illuminate\Support\Collection;
use Motomedialab\Checkout\Contracts\CalculatesDiscountValue;
use Motomedialab\Checkout\Contracts\CheckoutUser;
use Motomedialab\Checkout\Contracts\ComparesVoucher;
use Motomedialab\Checkout\Models\Voucher;

class CompareVoucher implements ComparesVoucher
{

    public function __invoke(
        Collection $products,
        string $currency,
        Voucher $oldVoucher,
        Voucher $newVoucher,
        ?CheckoutUser $owner
    ) {
        /** @var CalculatesDiscountValue $discountService */
        $discountService = app(CalculatesDiscountValue::class);

        return $discountService($products, $newVoucher, $currency, $owner)
            - $discountService($products, $oldVoucher, $currency, $owner);
    }
}