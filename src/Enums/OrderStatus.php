<?php

namespace Motomedialab\Checkout\Enums;

enum OrderStatus: string
{
    case SHIPPED = 'shipped';
    case PAID = 'paid';
    case PENDING = 'pending';
    case AWAITING_PAYMENT = 'awaiting_payment';
    case CANCELLED = 'cancelled';
}