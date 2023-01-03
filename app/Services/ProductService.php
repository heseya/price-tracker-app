<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Api;
use App\Models\ProductPrice;
use App\Services\Contracts\ProductServiceContract;

readonly final class ProductService implements ProductServiceContract
{
    public function findCheapestPrice(string $productId): ?ProductPrice
    {
        $lastPrice = $this->getLastPrice($productId);

        if (null === $lastPrice) {
            return null;
        }

        /** @var ?ProductPrice $price */
        $price = ProductPrice::query()
            ->where('product_id', $productId)
            ->where('id', '!=', $lastPrice->getKey())
            ->whereDate('changed_at', '>=', $lastPrice->changed_at->subDays(30)->startOfDay())
            ->orderBy('price_min')
            ->first();

        return $price;
    }

    public function update(
        string $productId,
        float $newPriceMin,
        float $newPriceMax,
        string $changedAt,
    ): void {
        $lastPrice = $this->getLastPrice($productId);

        if (
            null === $lastPrice ||
            round($lastPrice->price_min, 2) !== round($newPriceMin, 2) ||
            round($lastPrice->price_max, 2) !== round($newPriceMax, 2)
        ) {
            ProductPrice::query()->create([
                'product_id' => $productId,
                'price_min' => $newPriceMin,
                'price_max' => $newPriceMax,
                'changed_at' => $changedAt,
            ]);
        }
    }

    public function checkSignature(string $apiUrl, string $signature, mixed $payload): bool
    {
        $api = Api::query()->where('url', $apiUrl)->firstOrFail();

        return $signature === hash_hmac('sha256', $payload, $api->webhook_secret);
    }

    private function getLastPrice(string $id): ?ProductPrice
    {
        return ProductPrice::query()
            ->where('product_id', $id)
            ->orderBy('changed_at', 'DESC')
            ->first();
    }
}
