<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\ProductPrice;

interface ProductServiceContract
{
    public function findCheapestPrice(string $productId): ?ProductPrice;

    public function update(
        string $productId,
        float $newPriceMin,
        float $newPriceMax,
        string $changedAt,
    ): void;
}
