<?php

namespace Motomedialab\Checkout\Helpers;

use Illuminate\Contracts\Support\Arrayable;

class VatCalculator implements Arrayable
{
    
    private function __construct(protected int $amount_in_pence, protected float $vatRate)
    {
        //
    }
    
    public static function make(int $amount_in_pence, float $vatRate = 0): array
    {
        return (new self($amount_in_pence, $vatRate))->toArray();
    }
    
    public function toArray(): array
    {
        $excVatAmount = round($this->amount_in_pence / (1 + $this->vatRate / 100));
        
        return [
            'exc_vat_in_pence' => (int)$excVatAmount,
            'inc_vat_in_pence' => $this->amount_in_pence,
            'vat_in_pence' => (int)($this->amount_in_pence - $excVatAmount),
        ];
    }
}