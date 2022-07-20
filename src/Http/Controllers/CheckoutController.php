<?php

namespace Motomedialab\Checkout\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Motomedialab\Checkout\Contracts\ValidatesVoucher;
use Motomedialab\Checkout\Exceptions\UnsupportedCurrencyException;
use Motomedialab\Checkout\Factories\OrderFactory;
use Motomedialab\Checkout\Http\Resources\OrderResource;
use Motomedialab\Checkout\Models\Order;
use Motomedialab\Checkout\Models\Product;
use Motomedialab\Checkout\Models\Voucher;

class CheckoutController
{
    public function show(Order $order): OrderResource
    {
        return OrderResource::make($order);
    }
    
    public function store(Request $request): OrderResource
    {
        $this->validate($request, ['currency' => ['required', 'string', 'min:3', 'max:3']]);
        
        try {
            $factory = OrderFactory::make($request->get('currency'));
        } catch (UnsupportedCurrencyException $e) {
            throw ValidationException::withMessages(['currency' => 'Sorry, this currency isn\'t supported yet.']);
        }
        
        $this->products($request)
            ->each(fn(Product $product) => $factory->add(
                $product,
                $product->getAttribute('quantity')
            ));
        
        return OrderResource::make($factory->save());
    }
    
    public function update(Request $request, Order $order)
    {
        $validated = $this->validate($request, [
            'increment' => ['nullable', 'boolean'],
        ]);
        
        $factory = OrderFactory::fromExisting($order);
        
        $this->products($request)
            ->each(fn(Product $product) => $factory->add(
                $product,
                $product->getAttribute('quantity'),
                $validated['increment'] ?? true
            ));
        
        return OrderResource::make($factory->save());
    }
    
    /**
     * Delete an entire basket.
     *
     * @param  Request  $request
     * @param  Order  $order
     *
     * @return JsonResponse
     */
    public function destroy(Request $request, Order $order): JsonResponse
    {
        $order->delete();
        
        return response()->json(['success' => true], 204);
    }
    
    /**
     * Validate the inbound request data.
     *
     * @param  Request  $request
     * @param  array  $rules  Additional rules to be supplied
     *
     * @return array
     */
    protected function validate(Request $request, array $rules = []): array
    {
        return $request->validate([
            ...$rules,
            'products' => ['nullable', 'array'],
            'products.*.id' => ['required', Rule::exists(config('checkout.tables.products'))],
            'products.*.quantity' => ['nullable', 'numeric', 'min:1', 'max:50'],
            'voucher' => [
                'nullable',
                function ($key, $value, $fail) {
                    try {
                        app(ValidatesVoucher::class)(Voucher::findByCode($value));
                    } catch (\Exception $e) {
                        $fail($e->getMessage());
                    }
                }
            ]
        ], [
            'products.*.id.*' => 'Unknown product ID',
        ]);
    }
    
    /**
     * Return a collection of products based on the request.
     *
     * @param  Request  $request
     *
     * @return Collection
     */
    protected function products(Request $request): Collection
    {
        $productCollection = collect($request->get('products'));
        
        if ($productCollection->isEmpty()) {
            return collect();
        }
        
        $products = Product::query()->whereIn(
            'id',
            $productCollection->map(fn($value) => $value['id'])
        )->get();
        
        return $products->map(function (Product $product) use ($productCollection) {
            $product->setAttribute('quantity',
                $productCollection->firstWhere('id', $product->getKey())['quantity'] ?? 1);
            return $product;
        });
    }
}