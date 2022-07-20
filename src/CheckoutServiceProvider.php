<?php

namespace Motomedialab\Checkout;

use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Motomedialab\Checkout\Actions\ValidateVoucher;
use Motomedialab\Checkout\Contracts\ValidatesVoucher;
use Motomedialab\Checkout\Enums\OrderStatus;
use Motomedialab\Checkout\Models\Order;

class CheckoutServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('checkout.php'),
            ], 'config');
        }
        
        $this->setupRouteModelBindings();
    }
    
    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'checkout');
        
        // Register the main class to use with the facade
        $this->app->singleton('checkout', function () {
            return new Checkout;
        });
        
        // action pattern
        $this->app->bind(ValidatesVoucher::class, ValidateVoucher::class);
    }
    
    protected function setupRouteModelBindings()
    {
        Route::bind('order', fn(string $uuid) => Order::query()
            ->with(['products', 'voucher'])
            ->where('uuid', $uuid)
            ->where('status', OrderStatus::PENDING->value)
            ->firstOrFail());
    }
}
