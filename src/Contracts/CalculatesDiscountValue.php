<?php
/**
 * @author MotoMediaLab <hello@motomedialab.com>
 * Created at: 26/07/2022
 */

namespace Motomedialab\Checkout\Contracts;

use Illuminate\Support\Collection;
use Motomedialab\Checkout\Models\Voucher;

interface CalculatesDiscountValue
{
    public function __invoke(Collection $products, Voucher $voucher, string $currency): int;
}