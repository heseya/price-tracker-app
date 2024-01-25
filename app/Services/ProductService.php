<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Api;
use App\Models\ProductPrice;
use App\Services\Contracts\ProductServiceContract;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final readonly class ProductService implements ProductServiceContract
{
    public function findCheapestPrice(string $productId, string $currency = ProductServiceContract::DEFAULT_CURRENCY): ?ProductPrice
    {
        $lastPrice = $this->getLastPrice($productId, $currency);

        if ($lastPrice === null) {
            return null;
        }

        /** @var ?ProductPrice $price */
        return ProductPrice::query()
            ->where('product_id', $productId)
            ->where('id', '!=', $lastPrice->getKey())
            ->where('currency', $currency)
            ->whereDate('changed_at', '>=', $lastPrice->changed_at->subDays(30)->startOfDay())
            ->orderBy('price_min')
            ->first();
    }

    public function findCheapestPrices(array $productIds, string $currency = self::DEFAULT_CURRENCY): Collection
    {
        $prices = collect([]);

        foreach ($productIds as $productId) {
            $price = $this->findCheapestPrice($productId, $currency);
            if ($price) {
                $prices->push($this->findCheapestPrice($productId, $currency));
            }
        }

        return $prices;
    }

    public function update(
        string $productId,
        float $newPriceMin,
        float $newPriceMax,
        string $changedAt,
        ?string $currency = ProductServiceContract::DEFAULT_CURRENCY,
    ): void {
        $currency ??= ProductServiceContract::DEFAULT_CURRENCY;

        $lastPrice = $this->getLastPrice($productId, $currency);

        if (
            $lastPrice === null
            || round($lastPrice->price_min, 2) !== round($newPriceMin, 2)
            || round($lastPrice->price_max, 2) !== round($newPriceMax, 2)
        ) {
            ProductPrice::query()->create([
                'product_id' => $productId,
                'price_min' => $newPriceMin,
                'price_max' => $newPriceMax,
                'changed_at' => $changedAt,
                'currency' => $currency,
            ]);
        }
    }

    public function updatePrices(string $productId, array $newPricesMin, array $newPricesMax, string $changedAt): void
    {
        foreach ($newPricesMin as $newPriceMin) {
            $newPriceMax = Arr::first($newPricesMax, fn (array $priceMax) => $priceMax['currency'] === $newPriceMin['currency']);
            $this->update($productId, (float) $newPriceMin['gross'], (float) $newPriceMax['gross'], $changedAt, $newPriceMin['currency']);
        }
    }

    public function checkSignature(string $apiUrl, string $signature, mixed $payload): bool
    {
        $api = Api::query()->where('url', $apiUrl)->firstOrFail();

        return $signature === hash_hmac('sha256', $payload, $api->webhook_secret);
    }

    private function getLastPrice(string $id, string $currency = ProductServiceContract::DEFAULT_CURRENCY): ?ProductPrice
    {
        return ProductPrice::query()
            ->where('product_id', $id)
            ->where('currency', $currency)
            ->orderBy('changed_at', 'DESC')
            ->first();
    }
}
