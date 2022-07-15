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
    
    public function get(string $currency): Money|null
    {
        $currency = strtoupper($currency);
        
        if (!array_key_exists($currency, $this->data)) {
            return null;
        }
        
        return Money::make($this->data[$currency] ?? 0, $currency);
    }
    
    public function toArray(): array
    {
        return $this->data;
    }
}