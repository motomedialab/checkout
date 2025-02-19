<?php

namespace Motomedialab\Checkout;

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Motomedialab\Checkout\Actions\CalculateDiscountValue;
use Motomedialab\Checkout\Actions\CalculateProductsShipping;
use Motomedialab\Checkout\Actions\CalculateProductsValue;
use Motomedialab\Checkout\Actions\CalculateProductsVat;
use Motomedialab\Checkout\Actions\CompareVoucher;
use Motomedialab\Checkout\Actions\ValidateVoucher;
use Motomedialab\Checkout\Console\PurgeHistoricOrders;
use Motomedialab\Checkout\Contracts\CalculatesDiscountValue;
use Motomedialab\Checkout\Contracts\CalculatesProductsShipping;
use Motomedialab\Checkout\Contracts\CalculatesProductsValue;
use Motomedialab\Checkout\Contracts\CalculatesProductsVat;
use Motomedialab\Checkout\Contracts\CheckoutUser;
use Motomedialab\Checkout\Contracts\ComparesVoucher;
use Motomedialab\Checkout\Contracts\ValidatesVoucher;
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
        
        // initialise commands
        $this->commands(PurgeHistoricOrders::class);
        
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

        $this->app->bind(CheckoutUser::class, function ($app) {
            /** @var Request $request */
            $request = $app->request;

            $user = $request->user(config('checkout.guard', 'api'));

            return $user instanceof CheckoutUser ? $user : null;
        });
        
        // action pattern
        $this->app->bind(ComparesVoucher::class, CompareVoucher::class);
        $this->app->bind(ValidatesVoucher::class, ValidateVoucher::class);
        $this->app->bind(CalculatesProductsVat::class, CalculateProductsVat::class);
        $this->app->bind(CalculatesDiscountValue::class, CalculateDiscountValue::class);
        $this->app->bind(CalculatesProductsValue::class, CalculateProductsValue::class);
        $this->app->bind(CalculatesProductsShipping::class, CalculateProductsShipping::class);
    }
    
    protected function setupRouteModelBindings()
    {
        Route::bind('order', fn(string $uuid) => config('checkout.models.order')::query()
            ->with(['products', 'voucher'])
            ->where('uuid', $uuid)
            ->firstOrFail());
    }
}
