<?php

namespace Motomedialab\Checkout\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Motomedialab\Checkout\Models\Order;

/**
 * @property-read Order $resource
 */
class OrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->resource->uuid,
            'currency' => $this->resource->currency,
            'vat_rate' => $this->resource->vat_rate,
            'voucher' => $this->resource->voucher?->code,
            'products' => ProductResource::collection($this->resource->products()->get())->toArray($request),
            'totals' => [
                'amount_in_pence' => $this->resource->amount->toPence(),
                'shipping_in_pence' => $this->resource->shipping->toPence(),
                'discount_in_pence' => $this->resource->discount->toPence(),
                'total_in_pence' => $this->resource->total->toPence()
            ],
        ];
    }
}