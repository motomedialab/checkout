<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    
    'tables' => [
        'orders' => 'checkout_orders',
        'products' => 'checkout_products',
        'vouchers' => 'checkout_vouchers',
        'order_product' => 'checkout_order_product',
        'product_voucher' => 'checkout_product_voucher',
    ],
    
    'currencies' => [
        'GBP' => '£',
        'EUR' => '€',
    ],

];