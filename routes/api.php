<?php

use Illuminate\Support\Facades\Route;
use Motomedialab\Checkout\Http\Controllers\CheckoutController;

Route::middleware('api')->group(function () {
    
    Route::resource('checkout', CheckoutController::class)
        ->parameter('checkout', 'order')
        ->only('show', 'store', 'update', 'destroy');
});