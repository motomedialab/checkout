<?php

namespace Motomedialab\Checkout\Helpers;

use Illuminate\Contracts\Support\Arrayable;

class PriceHelper implements Arrayable
{
    protected array $data;
    
    public function __construct(array $data)
    {
        $this->data = array_change_key_case($data, CASE_UPPER);
    }
    
    public function get(string $currency): Money
    {
        $currency = strtoupper($currency);
        return Money::make($this->data[$currency] ?? 0, $currency);
    }
    
    public function toArray(): array
    {
        return $this->data;
    }
}