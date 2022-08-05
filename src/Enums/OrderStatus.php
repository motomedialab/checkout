<?php

namespace Motomedialab\Checkout\Enums;

use Motomedialab\Checkout\Models\Order;

enum OrderStatus: string
{
    case SHIPPED = 'shipped';
    case PAID = 'paid';
    case PENDING = 'pending';
    case AWAITING_PAYMENT = 'awaiting_payment';
    case CANCELLED = 'cancelled';
    
    const PRIORITY_MAP = [
        'pending' => 0,
        'awaiting_payment' => 5,
        'paid' => 10,
        'shipped' => 15,
        'cancelled' => 20,
    ];
    
    public function eq(OrderStatus $status): bool
    {
        return $this === $status;
    }
    
    public function gte(OrderStatus $status): bool
    {
        return $this->gt($status) || $this->eq($status);
    }
    
    public function gt(OrderStatus $status): bool
    {
        return self::PRIORITY_MAP[$this->value] > self::PRIORITY_MAP[$status->value];
    }
    
    public function lt(OrderStatus $status): bool
    {
        return self::PRIORITY_MAP[$this->value] < self::PRIORITY_MAP[$status->value];
    }
    
    public function lte(OrderStatus $status): bool
    {
        return $this->lt($status) || $this->eq($status);
    }
    
    
}