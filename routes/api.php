<?php

use Illuminate\Support\Facades\Route;
use Motomedialab\Checkout\Http\Controllers\CheckoutController;
use Motomedialab\Checkout\Http\Controllers\ProductController;
use Motomedialab\Checkout\Http\Controllers\VoucherController;

$route = Route::middleware(config('checkout.middleware', ['api']));

if (config('checkout.domain')) {
    $route->domain(config('checkout.domain'));
}

$route->group(function () {

    Route::get('products/{product:id}', ProductController::class)->name('checkout.product');

    Route::resource('checkout', CheckoutController::class)
        ->parameter('checkout', 'order')
        ->only('show', 'store', 'update', 'destroy');

    Route::delete('checkout/{order}/voucher', VoucherController::class)
        ->name('checkout.voucher.destroy');
});
