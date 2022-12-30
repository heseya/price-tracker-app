<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ProductPrice;
use App\Services\Contracts\ProductServiceContract;
use Illuminate\Support\Carbon;

readonly final class ProductService implements ProductServiceContract
{
    public function findCheapestPrice(string $product_id): ?ProductPrice
    {
        /** @var ?ProductPrice $price */
        $price = ProductPrice::query()
            ->where('product_id', $product_id)
            ->whereDate('changed_at', '>=', Carbon::now()->subDays(30)->startOfDay())
            ->orderBy('price')
            ->first();

        return $price;
    }
}
