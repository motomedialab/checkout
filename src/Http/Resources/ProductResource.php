<?php

namespace Motomedialab\Checkout\Http\Resources;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Resources\Json\JsonResource;
use Motomedialab\Checkout\Contracts\CalculatesDiscountValue;
use Motomedialab\Checkout\Enums\ProductStatus;
use Motomedialab\Checkout\Helpers\VatCalculator;
use Motomedialab\Checkout\Models\Product;
use Motomedialab\Checkout\Models\Voucher;

/**
 * @property-read Product $resource
 */
class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        $currency = $this->resource->orderPivot?->order->currency
            ?? strtoupper($request->get('currency', config('checkout.default_currency')));
        
        try {
            $voucher = $this->resource->orderPivot?->order->voucher
                ?? ($request->has('voucher') ? Voucher::findByCode($request->get('voucher')) : null);
        } catch (ModelNotFoundException $e) {
            $voucher = new Voucher(['value' => 0]);
        }
        
        return array_filter([
            'id' => $this->resource->getKey(),
            'name' => $this->resource->name,
            'currency' => $currency,
            'quantity' => $this->resource->orderPivot?->quantity,
            'vat_rate' => $this->resource->vat_rate,
            'available' => $this->resource->status === ProductStatus::AVAILABLE,
            'discount_in_pence' => $this->when(
                $voucher instanceof Voucher,
                fn() => app(CalculatesDiscountValue::class)(collect([$this->resource]), $voucher, $currency)
            ),
            'pricing' => VatCalculator::make(
                $this->resource->price($currency)?->toPence(),
                $this->resource->vat_rate
            ),
            'children' => static::collection($this->whenLoaded('children')),
            'parent' => $this->whenLoaded('parent', fn() => static::make($this->resource->parent)),
        ]);
    }
}