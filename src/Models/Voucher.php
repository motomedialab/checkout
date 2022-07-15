<?php

namespace Motomedialab\Checkout\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends Model
{
    use SoftDeletes;
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        $this->setTable(config('checkout.tables.vouchers'));
    }
    
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, config('checkout.tables.products'));
    }
}