<?php

namespace Motomedialab\Checkout\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Motomedialab\Checkout\Enums\OrderStatus;
use Motomedialab\Checkout\Events\OrderStatusUpdated;

/**
 * @property OrderStatus $status
 */
class Order extends Model
{
    protected $casts = [
        'status' => OrderStatus::class,
    ];
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        $this->table = config('checkout.tables.orders');
    }
    
    /**
     * An order has many products.
     *
     * @return BelongsToMany
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, config('checkout.tables.order_product'));
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
}