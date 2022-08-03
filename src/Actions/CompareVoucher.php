<?php

namespace Motomedialab\Checkout\Actions;

use Illuminate\Support\Collection;
use Motomedialab\Checkout\Contracts\CalculatesDiscountValue;
use Motomedialab\Checkout\Contracts\ComparesVoucher;
use Motomedialab\Checkout\Models\Voucher;

class CompareVoucher implements ComparesVoucher
{
    
    public function __invoke(Collection $products, string $currency, Voucher $oldVoucher, Voucher $newVoucher)
    {
        // if both vouchers are on the basket, which offers the better discount...
        
        $oldValue = app(CalculatesDiscountValue::class)($products, $oldVoucher, $currency);
        $newValue = app(CalculatesDiscountValue::class)($products, $newVoucher, $currency);
        
        return $newValue - $oldValue;
    }
}