<?php

namespace Motomedialab\Checkout\Models;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Motomedialab\Checkout\Enums\ProductStatus;
use Motomedialab\Checkout\Helpers\Money;
use Motomedialab\Checkout\Helpers\PriceHelper;
use Motomedialab\Checkout\Models\Pivots\OrderPivot;

/**
 * @property Product $parent
 * @property PriceHelper $pricing_in_pence
 * @property PriceHelper $shipping_in_pence
 * @property string $name
 * @property float $vat_rate
 * @property int $quantity A temporary store for basket quantity
 * @property ProductStatus $status
 * @property ?OrderPivot $orderPivot
 * @property array $metadata a temporary store for metadata
 */
class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    /**
     * A temporary store for our quantity
     * when manipulating within the basket.
     */
    protected int $quantityHolder = 0;

    protected array $metadataHolder = [];

    protected $casts = [
        'status' => ProductStatus::class,
    ];

    protected $attribute = [
        'pricing' => '[]',
        'shipping' => '[]',
    ];

    protected $with = ['parent'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('checkout.tables.products'));
    }

    /**
     * A product optionally has a parent.
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
     */
    public function availableInCurrency(string $currency): bool
    {
        return $this->pricing_in_pence->has($currency)
            || $this->parent?->pricing_in_pence->has($currency);
    }

    /**
     * Retrieve our pricing value.
     */
    public function price(string $currency): ?Money
    {
        if ($this->orderPivot?->order->hasBeenSubmitted()) {
            return Money::make($this->orderPivot->amount_in_pence, $this->orderPivot->order->currency);
        }

        if ($this->parent && $this->parent->availableInCurrency($currency)) {
            return $this->parent->price($currency)
                ->add($this->pricing_in_pence->get($currency) ?? Money::make(0, $currency));
        }

        return $this->pricing_in_pence->get($currency);
    }

    /**
     * Retrieve our shipping value.
     */
    public function shipping(string $currency): ?Money
    {
        return max(
            $this->parent?->shipping_in_pence->get($currency),
            $this->shipping_in_pence->get($currency)
        );
    }

    /**
     * Determine the product pricing based
     * on whether the order has been submitted or not.
     */
    protected function pricingInPence(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->orderPivot?->order->hasBeenSubmitted()
                ? new PriceHelper([$this->orderPivot->order->currency => $this->orderPivot->amount_in_pence])
                : new PriceHelper(json_decode($value, true) ?? []),

            set: fn ($value) => json_encode($value instanceof Arrayable ? $value->toArray() : $value)
        );
    }

    /**
     * Determine the product shipping based
     * on whether the order has been submitted or not.
     */
    protected function shippingInPence(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->orderPivot?->order->hasBeenSubmitted()
                ? new PriceHelper([$this->orderPivot->order->currency => $this->orderPivot->shipping_in_pence])
                : new PriceHelper(json_decode($value, true) ?? []),
            set: fn ($value) => json_encode($value instanceof Arrayable ? $value->toArray() : $value)
        );
    }

    protected function quantity(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->orderPivot?->quantity ?? $this->quantityHolder,
            set: fn ($value) => $this->quantityHolder = (int) $value
        );
    }

    protected function metadata(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->orderPivot?->metadata ?? $this->metadataHolder,
            set: fn ($value) => $this->metadataHolder = (array) $value
        );
    }

    protected function vatRate(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->orderPivot?->order->hasBeenSubmitted()
                ? $this->orderPivot->vat_rate
                : (int) max($this->parent?->vat_rate, $value)
        );
    }
}
