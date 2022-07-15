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
            $this->add($product, $product->pivot->quantity);
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
        $product->setAttribute('quantity', $quantity);
        $this->basket->push($product);
        
        return $this;
    }
    
    public function remove(Product $product): static
    {
        $this->basket->where('id', $product->getKey())?->pop();
        
        return $this;
    }
    
    public function setAddress(array $address): static
    {
        $this->order->setAttribute('recipient_address', $address);
        
        return $this;
    }
    
    public function applyVoucher(Voucher $voucher): static
    {
        if (app(ValidatesVoucher::class)($this, $voucher)) {
            $this->voucher = $voucher;
        }
        
        return $this;
    }
    
    public function save()
    {
        $this->order->save();
        
        $this->order->products()->sync(
            $this->basket->mapWithKeys(function (Product $product, int $key) {
                return [$product->getKey() => [
                    'quantity' => $product->quantity,
                    'amount_in_pence' => $product->price($this->order->currency)->toFloat(),
                    'vat_rate' => $product->vat_rate,
                ]];
            })->toArray()
        );
        
        $this->order->voucher()->associate($this->voucher);
    
        $this->order->amount_in_pence = $this->basket
            ->map(fn(Product $product) => $product->price($this->order->currency)->toPence() * $product->quantity)->sum() ?? 0;
        
        $this->order->shipping_in_pence = $this->basket
            ->map(fn(Product $product) => $product->shipping($this->order->currency)?->toPence() ?? 0)->max() ?? 0;
        
        return tap($this->order)->save();
    }
    
}