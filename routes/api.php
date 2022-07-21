<?php

use Illuminate\Support\Facades\Route;
use Motomedialab\Checkout\Http\Controllers\CheckoutController;

$route = Route::middleware('api');

if (config('checkout.domain')) {
    $route->domain(config('checkout.domain'));
}

$route->group(function () {
    
    Route::resource('checkout', CheckoutController::class)
        ->parameter('checkout', 'order')
        ->only('show', 'store', 'update', 'destroy');
});