<?php

namespace Motomedialab\Checkout\Actions;

use Exception;
use Illuminate\Support\Collection;
use Motomedialab\Checkout\Contracts\CalculatesDiscountValue;
use Motomedialab\Checkout\Contracts\CalculatesProductsValue;
use Motomedialab\Checkout\Contracts\CheckoutUser;
use Motomedialab\Checkout\Contracts\ValidatesVoucher;
use Motomedialab\Checkout\Models\Product;
use Motomedialab\Checkout\Models\Voucher;

class CalculateDiscountValue implements CalculatesDiscountValue
{

    public function __invoke(
        Collection $products,
        ?Voucher $voucher,
        string $currency,
        ?CheckoutUser $owner = null
    ): int {

        if (is_null($voucher)) {
            return 0;
        }

        try {
            app(ValidatesVoucher::class)($voucher, $products, $owner);
        } catch (Exception) {
            return 0;
        }

        // determine our discount multiplier
        $multiplier = $voucher->percentage ? $voucher->value / 100 : $voucher->value * 100;
        $productsValue = app(CalculatesProductsValue::class)($products, $currency);

        // discount on basket
        if ($voucher->on_basket) {
            return min(ceil(($multiplier <= 1 ? $productsValue : 1) * $multiplier), $productsValue);
        }

        // discount against products
        $discountValue = ceil($products
            // if we're not accounting for quantity, we only need distinct products
            ->when(!$voucher->quantity_price, fn(Collection $collection) => $collection->unique('id'))
            // and our voucher must be applicable for the given product
            ->filter(fn(Product $product) => $voucher->products->contains($product))
            // do the math to work out our discount value
            ->map(fn(Product $product) => [
                'quantity' => $product->quantity,
                'value' => $product->price($currency)->toPence() * ($voucher->quantity_price ? $product->quantity : 1)
            ])
            ->map(fn($result) => ($multiplier <= 1 ? $result['value'] : $result['quantity']) * $multiplier)
            ->sum());

        return min($discountValue, $productsValue);
    }
}