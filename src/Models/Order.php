<?php

namespace Motomedialab\Checkout\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Motomedialab\Checkout\Contracts\ValidatesVoucher;
use Motomedialab\Checkout\Enums\OrderStatus;
use Motomedialab\Checkout\Events\OrderStatusUpdated;
use Motomedialab\Checkout\Helpers\Money;
use Ramsey\Uuid\Lazy\LazyUuidFromString;
use Ramsey\Uuid\Uuid;

/**
 * @property OrderStatus $status
 * @property string $currency
 * @property LazyUuidFromString $uuid
 * @property integer $amount_in_pence
 * @property integer $discount_in_pence
 * @property integer $shipping_in_pence
 * @property Money $amount
 * @property Money $shipping
 * @property Money $total
 * @property Collection $products
 */
class Order extends Model
{
    protected $casts = [
        'status' => OrderStatus::class,
        'recipient_address' => 'array',
        'amount_in_pence' => 'integer',
        'shipping_in_pence' => 'integer',
        'discount_in_pence' => 'integer',
    ];
    
    protected $guarded = [];
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        $this->setTable(config('checkout.tables.orders'));
    }
    
    /**
     * @param  string  $uuid
     *
     * @return Model
     */
    public static function findByUuid(string $uuid): Model
    {
        return static::query()->where('uuid', $uuid)->firstOrFail();
    }
    
    protected static function boot()
    {
        parent::boot();
        
        // enforce a UUID for our order
        static::creating(fn($model) => $model->uuid = Uuid::uuid4());
    }
    
    /**
     * An order has many products.
     *
     * @return BelongsToMany
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, config('checkout.tables.order_product'))
            ->withPivot(['quantity', 'amount_in_pence', 'vat_rate'])
            ->as('basket');
    }
    
    /**
     * An order belongs to a voucher
     *
     * @return BelongsTo
     */
    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }
    
    /**
     * Mark an order as confirmed.
     * This will perform a final validation.
     *
     * @return $this
     */
    public function confirm(): static
    {
        if ($this->voucher) {
            app(ValidatesVoucher::class)($this->voucher);
        }
        
        $this->setStatus(OrderStatus::AWAITING_PAYMENT);
        
        return $this;
    }
    
    /**
     * Set the status of an order.
     *
     * @param  OrderStatus  $status
     *
     * @return void
     */
    public function setStatus(OrderStatus $status): void
    {
        $this->status = $status;
        $this->save();
        
        event(new OrderStatusUpdated($this, $status));
    }
    
    protected function amount(): Attribute
    {
        return new Attribute(
            get: fn() => Money::make($this->amount_in_pence, $this->currency)
        );
    }
    
    protected function shipping(): Attribute
    {
        return new Attribute(
            get: fn() => Money::make($this->shipping_in_pence, $this->currency)
        );
    }
    
    protected function total(): Attribute
    {
        return new Attribute(
            get: fn() => $this->amount->add($this->shipping)
        );
    }
}