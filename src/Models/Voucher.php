<?php

namespace Motomedialab\Checkout\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $code
 * @property bool $percentage
 * @property bool $quantity_price
 * @property bool $on_basket
 * @property int $value
 * @property int $max_uses
 * @property int $total_uses
 * @property Carbon $valid_from
 * @property Carbon $valid_until
 * @property Collection $products
 */
class Voucher extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $casts = [
        'quantity_price' => 'boolean',
        'on_basket' => 'boolean',
        'percentage' => 'boolean',
        'valid_from' => 'date',
        'valid_until' => 'date',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('checkout.tables.vouchers'));
    }

    public static function findByCode(string $code): static
    {
        return Voucher::query()->where('code', $code)->firstOrFail();
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, config('checkout.tables.product_voucher'));
    }
}
