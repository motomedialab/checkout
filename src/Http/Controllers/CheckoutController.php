<?php

namespace Motomedialab\Checkout\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
        $validated = $this->validate($request, ['currency' => ['required', 'string', 'min:3', 'max:3']]);
        
        try {
            $factory = OrderFactory::make(
                $validated['currency'] ?? config('checkout.default_currency')
            );
        } catch (UnsupportedCurrencyException $e) {
            throw ValidationException::withMessages(['currency' => 'Sorry, this currency isn\'t supported yet.']);
        }
        
        return $this->createOrUpdate($validated, $factory);
    }
    
    public function update(Request $request, Order $order): OrderResource
    {
        $validated = $this->validate($request, [
            'increment' => ['nullable', 'boolean'],
        ]);
        
        return $this->createOrUpdate($validated, OrderFactory::fromExisting($order));
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
     * Create or update an order based on an order factory instance.
     *
     * @param  array  $validated
     * @param  OrderFactory  $factory
     *
     * @return OrderResource
     * @throws \Exception
     */
    protected function createOrUpdate(array $validated, OrderFactory $factory): OrderResource
    {
        $this->products($validated['products'] ?? [])
            ->each(fn(Product $product) => $factory->add(
                $product,
                $product->getAttribute('quantity'),
                $validated['increment'] ?? true
            ));
        
        if (array_key_exists('voucher', $validated)) {
            $factory->applyVoucher(
                is_null($validated['voucher'])
                    ? null
                    : Voucher::findByCode($validated['voucher'])
            );
        }
        
        return OrderResource::make($factory->save());
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
            'products.*.id' => [
                'required',
                function ($parameter, $value, $fail) {
                    if (null === Product::query()->whereDoesntHave('children')->find($value)) {
                        $fail('The product you were looking for could not be found');
                    }
                }
            ],
            'products.*.quantity' => ['nullable', 'numeric', 'min:1', 'max:50'],
            'voucher' => [
                'nullable',
                function ($key, $value, $fail) {
                    try {
                        app(ValidatesVoucher::class)(Voucher::findByCode($value));
                    } catch (ModelNotFoundException) {
                        $fail('Unknown voucher code');
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
     * @param  array  $products
     *
     * @return Collection
     */
    protected function products(array $products): \Illuminate\Support\Collection
    {
        $productCollection = collect($products);
        
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