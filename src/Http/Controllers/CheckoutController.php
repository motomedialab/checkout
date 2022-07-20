<?php

namespace Motomedialab\Checkout\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Motomedialab\Checkout\Factories\OrderFactory;
use Motomedialab\Checkout\Http\Resources\OrderResource;
use Motomedialab\Checkout\Models\Order;
use Motomedialab\Checkout\Models\Product;

class CheckoutController
{
    public function show(Order $order): OrderResource
    {
        return OrderResource::make($order);
    }
    
    public function store(Request $request, Order $order): OrderResource
    {
        $request->validate([
            'currency' => ['required', Rule::in(array_keys(config('checkout.currencies')))],
            'products' => ['required', 'array'],
            'products.*.id' => ['required', Rule::exists(config('checkout.tables.products'))],
            'products.*.quantity' => ['required', 'numeric', 'min:1', 'max:50'],
        ]);
        
        $factory = OrderFactory::make($request->get('currency'));
        $products = Product::query()->whereIn('id', $request->get('products.*.id'));
        
        /** @var array{id: number, quantity: number} $product */
        foreach ($request->get('products') as $product) {
            // ToDo
//            $factory->add(Product::find($))
        }
        
        
    }
}