<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\ProductPrice;

/** @var ProductPrice $resource */
final class ProductPriceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource?->getKey() ?? null,
            'price_min' => $this->resource?->price_min ?? null,
            'price_max' => $this->resource?->price_max ?? null,
            'changed_at' => $this->resource?->changed_at ?? null,
            'currency' => $this->resource?->currency ?? null,
        ];
    }
}
