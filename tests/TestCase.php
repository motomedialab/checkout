<?php

namespace Motomedialab\Checkout\Tests;

use Motomedialab\Checkout\CheckoutServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->withFactories(__DIR__ .'/../database/factories');
        
        $this->artisan('migrate', [
            '--database' => 'sqlite',
            '--realpath' => realpath(__DIR__ .'/../database/migrations'),
        ]);
    }
    
    protected function getPackageProviders($app): array
    {
        return [
            CheckoutServiceProvider::class,
        ];
    }
}