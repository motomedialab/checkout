<?php

namespace Motomedialab\Checkout\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Motomedialab\Checkout\Casts\PricingCast;
use Motomedialab\Checkout\Enums\ProductStatus;
use Motomedialab\Checkout\Helpers\Money;
use Motomedialab\Checkout\Helpers\PriceHelper;

/**
 * @property Product $parent
 * @property PriceHelper $pricing
 * @property PriceHelper $shipping
 * @property string $name
 * @property float $vat_rate
 * @property ProductStatus $status
 */
class Product extends Model
{
    use SoftDeletes;
    
    protected $guarded = [];
    
    protected $casts = [
        'pricing' => PricingCast::class,
        'shipping' => PricingCast::class,
        'status' => ProductStatus::class,
    ];
    
    protected $with = ['parent'];
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        $this->setTable(config('checkout.tables.products'));
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
    
    public function children(): HasMany
    {
        return $this->hasMany(
            Product::class,
            'parent_product_id',
        );
    }
    
    /**
     * Determine if the product is available in the given currency
     *
     * @param  string  $currency
     *
     * @return bool
     */
    public function availableInCurrency(string $currency): bool
    {
        return $this->pricing->has($currency);
    }
    
    /**
     * Retrieve our pricing value.
     *
     * @param  string  $currency
     *
     * @return Money|null
     */
    public function price(string $currency): Money|null
    {
        if ($this->parent) {
            return $this->parent->price($currency)?->add(
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
     * @return Money|null
     */
    public function shipping(string $currency): Money|null
    {
        return $this->shipping->get($currency);
    }
}