<?php

namespace Motomedialab\Checkout\Helpers;

use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

class Money implements \Stringable
{
    public Stringable $stringable;
    
    protected array $symbols = [
        'GBP' => '£',
        'EUR' => '€'
    ];
    
    private function __construct(protected int $priceInPence, private readonly string $currency)
    {
        // default to inMoney format.
        $this->inMoney();
    }
    
    public function __call(string $name, array $arguments)
    {
        $this->stringable = $this->stringable->{$name}(...$arguments);
        return $this;
    }
    
    /**
     * Generate a new Money instance.
     *
     * @param  int  $priceInPence
     * @param  string  $currency
     *
     * @return Money|null
     */
    public static function make(int $priceInPence, string $currency): Money|null
    {
        $currency = strtoupper($currency);
        if (!array_key_exists($currency, config('checkout.currencies'))) {
            return null;
        }
        
        return new self($priceInPence, $currency);
    }
    
    public function inMoney(): static
    {
        $this->stringable = Str::of(number_format($this->priceInPence / 100, 2));
        return $this;
    }
    
    public function inPence(): static
    {
        $this->stringable = Str::of($this->priceInPence);
        return $this;
    }
    
    public function withSymbol(): static
    {
        $this->stringable = $this->stringable->prepend(
            config('checkout.currencies.'.$this->currency)
        );
        return $this;
    }
    
    public function add(int|Money $amount): static
    {
        $this->priceInPence += $amount instanceof Money
            ? $amount->toPence() : $amount;
        
        return $this;
    }
    
    public function subtract(int|Money $amount): static
    {
        $this->priceInPence -= $amount instanceof Money
            ? $amount->toPence() : $amount;
        
        return $this;
    }
    
    public function toPence(): int
    {
        return $this->priceInPence;
    }
    
    public function toFloat(): float
    {
        return $this->priceInPence / 100;
    }
    
    public function toFriendly(): string
    {
        return (string) $this->inMoney()->withSymbol();
    }
    
    public function __toString(): string
    {
        return $this->stringable->toString();
    }
}