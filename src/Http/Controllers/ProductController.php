<?php

namespace Motomedialab\Checkout\Http\Controllers;

use Illuminate\Http\Request;
use Motomedialab\Checkout\Http\Resources\ProductResource;
use Motomedialab\Checkout\Models\Product;

class ProductController
{
    public function __invoke(Request $request, Product $product)
    {
        $request->validate([
            'currency' => [
                'required', function ($key, $value, $fail) use ($product) {
                    if (!$product->availableInCurrency($value)) {
                        $fail('Sorry, this product is not available in this currency');
                    }
                }
            ],
        ]);
        
        return ProductResource::make($product->load('children'));
    }
}