<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\ProductPrice;
use Illuminate\Support\Collection;

interface ProductServiceContract
{
    public const DEFAULT_CURRENCY = 'PLN';

    public function findCheapestPrice(string $productId, string $currency = self::DEFAULT_CURRENCY): ?ProductPrice;
    public function findCheapestPrices(array $productIds, string $currency = self::DEFAULT_CURRENCY): Collection;

    public function update(
        string $productId,
        float $newPriceMin,
        float $newPriceMax,
        string $changedAt,
        ?string $currency = self::DEFAULT_CURRENCY,
    ): void;

    public function updatePrices(string $productId, array $newPricesMin, array $newPricesMax, string $changedAt): void;

    public function checkSignature(string $apiUrl, string $signature, mixed $payload): bool;
}
