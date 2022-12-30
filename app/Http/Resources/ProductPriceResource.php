<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class ProductPriceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource?->getKey() ?? null,
            'price' => $this->resource?->price ?? null,
            'changed_at' => $this->resource?->changed_at ?? null,
        ];
    }
}
