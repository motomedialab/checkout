<?php

namespace Motomedialab\Checkout\Tests;

use Illuminate\Support\Facades\Config;
use Motomedialab\Checkout\CheckoutServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->artisan('migrate', [
            '--database' => 'sqlite',
            '--realpath' => realpath(__DIR__ .'/../database/migrations'),
        ]);

        Config::set('auth.guards.api', [
            'driver' => 'session',
            'provider' => 'users',
        ]);
    }
    
    protected function getPackageProviders($app): array
    {
        return [
            CheckoutServiceProvider::class,
        ];
    }
}