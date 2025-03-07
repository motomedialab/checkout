<?php

namespace Motomedialab\Checkout\Helpers;

use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

class Money implements \Stringable
{
    private Stringable $stringable;

    private string $inFormat;

    protected array $symbols = [
        'GBP' => '£',
        'EUR' => '€',
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
     */
    public static function make(int $priceInPence, string $currency): ?Money
    {
        $currency = strtoupper($currency);
        if (! array_key_exists($currency, config('checkout.currencies'))) {
            return null;
        }

        return new self($priceInPence, $currency);
    }

    protected function in(): static
    {
        return $this->{'in'.$this->inFormat}();
    }

    public function newInstance(): static
    {
        return new self($this->toPence(), $this->currency);
    }

    public function inMoney(): static
    {
        $this->inFormat = 'Money';
        $this->stringable = Str::of(number_format($this->priceInPence / 100, 2));

        return $this;
    }

    public function inPence(): static
    {
        $this->inFormat = 'Pence';
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

        return $this->in();
    }

    public function subtract(int|Money $amount): static
    {
        $this->priceInPence -= $amount instanceof Money
            ? $amount->toPence() : $amount;

        return $this->in();
    }

    public function times(int $multiplier)
    {
        $this->priceInPence *= $multiplier;

        return $this->in();
    }

    public function divide(int $multiplier)
    {
        $this->priceInPence /= $multiplier;

        return $this->in();
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
