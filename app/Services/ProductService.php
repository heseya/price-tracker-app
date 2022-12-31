<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Api;
use App\Models\ProductPrice;
use App\Services\Contracts\ProductServiceContract;
use Illuminate\Support\Carbon;

readonly final class ProductService implements ProductServiceContract
{
    public function findCheapestPrice(string $productId): ?ProductPrice
    {
        /** @var ?ProductPrice $price */
        $price = ProductPrice::query()
            ->where('product_id', $productId)
            ->whereDate('changed_at', '>=', Carbon::now()->subDays(30)->startOfDay())
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
        /** @var ?ProductPrice $lastPrice */
        $lastPrice = ProductPrice::query()
            ->where('product_id', $productId)
            ->orderBy('price_min')
            ->first();

        if (null === $lastPrice || $lastPrice->price_min !== $newPriceMin || $lastPrice->price_max !== $newPriceMax) {
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
}
