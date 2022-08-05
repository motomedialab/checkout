<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    
    /**
     * Domain name
     *
     * Restrict API to a certain domain / domains
     */
    'domain' => null,
    
    /**
     * Table names
     *
     * The names of the tables that should be used.
     */
    'tables' => [
        'orders' => 'checkout_orders',
        'products' => 'checkout_products',
        'vouchers' => 'checkout_vouchers',
        'order_product' => 'checkout_order_product',
        'product_voucher' => 'checkout_product_voucher',
    ],
    
    /**
     * Models
     *
     * The models to use for various functionality.
     */
    'models' => [
        'order' => Motomedialab\Checkout\Models\Order::class,
    ],
    
    /**
     * Currencies
     *
     * The currencies available to the system
     * and their corresponding symbol
     */
    'currencies' => [
        'GBP' => '£',
        'EUR' => '€',
    ],
    
    /**
     * Default currency
     *
     * The default currency that should be used
     */
    'default_currency' => 'GBP',
    
    /**
     * Default VAT rate
     *
     * Percentage value, the default VAT rate to inherit
     */
    'default_vat_rate' => 20,
];