<?php

use Illuminate\Support\Facades\Route;
use Motomedialab\Checkout\Http\Controllers\CheckoutController;

Route::resource('checkout', CheckoutController::class)
    ->only('show', 'store', 'update', 'destroy');