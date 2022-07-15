<?php

namespace Motomedialab\Checkout\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    
        $this->table = config('checkout.tables.vouchers');
    }
}