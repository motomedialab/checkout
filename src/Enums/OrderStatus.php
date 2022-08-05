<?php

namespace Motomedialab\Checkout\Enums;

enum OrderStatus: string
{
    case SHIPPED = 'shipped';
    case PAID = 'paid';
    case PENDING = 'pending';
    case AWAITING_PAYMENT = 'awaiting_payment';
    case CANCELLED = 'cancelled';
    
    public function gte(OrderStatus $status): bool
    {
        $priorityMap = [
            'pending' => 0,
            'awaiting_payment' => 5,
            'paid' => 10,
            'shipped' => 15,
            'cancelled' => 20,
        ];
        
        return $priorityMap[$status->value] >= $priorityMap[$this->value];
        
    }
}