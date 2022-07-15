<?php

namespace Motomedialab\Checkout\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Motomedialab\Checkout\Casts\PricingCast;
use Motomedialab\Checkout\Enums\OrderStatus;
use Motomedialab\Checkout\Helpers\Money;
use Motomedialab\Checkout\Helpers\PriceHelper;

/**
 * @property Product $parent
 * @property PriceHelper $pricing
 * @property PriceHelper $shipping
 * @property string $name
 * @property float $vat_rate
 * @property OrderStatus $status
 */
class Product extends Model
{
    protected $guarded = [];
    
    protected $casts = [
        'pricing' => PricingCast::class,
        'shipping' => PricingCast::class,
    ];
    
    protected $with = ['parent'];
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        $this->table = config('checkout.tables.products');
    }
    
    /**
     * A product optionally has a parent.
     *
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(
            Product::class,
            'parent_product_id'
        );
    }
    
    /**
     * Retrieve our pricing value.
     *
     * @param  string  $currency
     *
     * @return Money
     */
    public function price(string $currency): Money
    {
        if ($this->parent) {
            return $this->parent->price($currency)->add(
                $this->pricing->get($currency)
            );
        }
        
        return $this->pricing->get($currency);
    }
    
    /**
     * Retrieve our shipping value.
     *
     * @param  string  $currency
     *
     * @return Money
     */
    public function shipping(string $currency): Money
    {
        return $this->shipping->get($currency);
    }
}