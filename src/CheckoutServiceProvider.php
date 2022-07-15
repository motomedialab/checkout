<?php

namespace Motomedialab\Checkout;

use Illuminate\Support\ServiceProvider;

class CheckoutServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'checkout');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'checkout');
        
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        
        // $this->loadRoutesFrom(__DIR__.'/routes.php');
        
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('checkout.php'),
            ], 'config');
        }
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
    }
}
