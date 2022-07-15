<?php

namespace Motomedialab\Checkout\Casts;

use Illuminate\Database\Eloquent\Model;
use Motomedialab\Checkout\Helpers\PriceHelper;

class PricingCast
{
    public function get(Model $model, $key, $value, $attributes)
    {
        return new PriceHelper(
            json_decode($attributes[$key], true)
        );
    }
    
    public function set(Model $model, $key, $value, $attributes)
    {
        if (!$value instanceof PriceHelper) {
            return [$key => json_encode($value)];
        }
        
        return [
            $key => array_change_key_case($value->toArray(), CASE_UPPER)
        ];
    }
    
    
    
}