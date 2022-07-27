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
            $voucher = $request->has('voucher') ? Voucher::findByCode($request->get('voucher')) : null;
        } catch (ModelNotFoundException $e) {
            $voucher = new Voucher(['value' => 0]);
        }
        
        return [
            'id' => $this->resource->getKey(),
            'name' => $this->resource->name,
            'currency' => $currency,
            'quantity' => $this->whenLoaded('orderPivot', fn() => $this->resource->orderPivot->quantity),
            'pricing' => VatCalculator::make(
                $this->resource->price($currency)?->toPence(),
                $this->resource->vat_rate
            ),
            'discount_in_pence' => $this->when(
                $voucher instanceof Voucher,
                fn() => app(CalculatesDiscountValue::class)(collect([$this->resource]), $voucher, $currency)
            ),
            'available' => $this->resource->status === ProductStatus::AVAILABLE,
            'children' => static::collection($this->whenLoaded('children')),
            'parent' => static::make($this->whenLoaded('parent')),
        ];
    }
}