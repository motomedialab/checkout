<?php

namespace Motomedialab\Checkout\Factories;

use Illuminate\Support\Collection;
use Motomedialab\Checkout\Contracts\ValidatesVoucher;
use Motomedialab\Checkout\Models\Order;
use Motomedialab\Checkout\Models\Product;
use Motomedialab\Checkout\Models\Voucher;

class OrderFactory
{
    protected Collection $basket;
    protected Voucher|null $voucher = null;
    
    public function __construct(protected Order $order)
    {
        $this->basket = collect();
        
        $this->order->products->each(function (Product $product) {
            $this->add($product, $product->basket->quantity);
        });
    }
    
    public static function make(string $currency): OrderFactory
    {
        return new self(new Order([
            'currency' => strtoupper($currency)
        ]));
    }
    
    public static function fromExisting(Order $order): OrderFactory
    {
        return new self ($order);
    }
    
    public function add(Product $product, int $quantity = 1): static
    {
        if (!$product->pricing->has($this->order->currency)) {
            throw new \Exception($product->name.' is not available in the requested currency');
        }
        
        $product->setAttribute('quantity', $quantity);
        
        if ($this->basket->contains($product)) {
            $this->remove($product);
        }
        
        $this->basket->push($product);
        
        return $this;
    }
    
    public function remove(Product $product): static
    {
        $this->basket = $this->basket->reject(fn(Product $item) => $item->is($product));
        
        return $this;
    }
    
    public function setAddress(array $address): static
    {
        $this->order->setAttribute('recipient_address', $address);
        
        return $this;
    }
    
    public function applyVoucher(Voucher $voucher): static
    {
        if (app(ValidatesVoucher::class)($voucher)) {
            $this->voucher = $voucher;
        }
        
        return $this;
    }
    
    public function save()
    {
        $this->order->save();
        
        $this->order->products()->sync(
            $this->basket->mapWithKeys(function (Product $product, int $key) {
                return [
                    $product->getKey() => [
                        'quantity' => $product->quantity,
                        'amount_in_pence' => $product->price($this->order->currency)->toPence(),
                        'vat_rate' => $product->vat_rate,
                    ]
                ];
            })->toArray()
        );
        
        $this->order->voucher()->associate($this->voucher);
        
        $this->calculateTotals();
        
        return tap($this->order)->save();
    }
    
    protected function calculateTotals()
    {
        // determine the total amount in pence.
        $this->order->amount_in_pence = $this->basket
                ->map(fn(Product $product
                ) => $product->price($this->order->currency)->toPence() * $product->quantity)->sum() ?? 0;
        
        $this->order->shipping_in_pence = $this->basket
                ->map(fn(Product $product) => $product->shipping($this->order->currency)?->toPence() ?? 0)->max() ?? 0;
        
        // determine the discount...
        if (!$this->voucher) {
            return $this->order->amount_in_pence;
        }
    
        // determine our discount multiplier
        $multiplier = $this->voucher->percentage ? $this->voucher->value / 100 : $this->voucher->value * 100;
        
        // discount on basket
        if ($this->voucher->on_basket) {
            return $this->order->discount_in_pence = ceil(($multiplier <= 1 ? $this->order->amount_in_pence : 1) * $multiplier);
        }
        
        // discount against products
        return $this->order->discount_in_pence = ceil($this->basket
            ->when(!$this->voucher->quantity_price, fn(Collection $collection) => $collection->unique('id'))
            ->filter(fn(Product $product) => $this->voucher->products->contains($product))
            ->map(fn(Product $product) => $product->price($this->order->currency)->toPence() * ($this->voucher->quantity_price ? $product->quantity : 1))
            ->map(fn(int $value) => ($multiplier <= 1 ? $value : 1) * $multiplier)
            ->sum());
    }
    
}