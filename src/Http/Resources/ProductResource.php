<?php

namespace Motomedialab\Checkout\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Motomedialab\Checkout\Enums\ProductStatus;
use Motomedialab\Checkout\Helpers\VatCalculator;
use Motomedialab\Checkout\Models\Product;

/**
 * @property-read Product $resource
 */
class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        $currency = strtoupper($request->get('currency', config('checkout.default_currency')));
        
        return [
            'id' => $this->resource->getKey(),
            'name' => sprintf(
                $this->resource->parent ? '%2$s [%1$s]' : '%1$s',
                $this->resource->name,
                $this->resource->parent?->name,
            ),
            'currency' => $currency,
            'quantity' => $this->whenLoaded('basket', fn() => $this->basket->quantity),
            'pricing' => VatCalculator::make(
                $this->resource->relationLoaded('basket')
                    ? $this->resource->basket->amount_in_pence
                    : $this->resource->price($currency)->toPence(),
                
                $this->resource->relationLoaded('basket')
                    ? $this->resource->basket->vat_rate
                    : $this->resource->vat_rate
            ),
            'available' => $this->resource->status === ProductStatus::AVAILABLE,
            'children' => static::collection($this->whenLoaded('children')),
        ];
    }
}