<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\ProductPriceResource;
use App\Services\Contracts\ProductServiceContract;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductServiceContract $productService,
    ) {
    }

    public function show(string $product_id): JsonResource
    {
        $price = $this->productService->findCheapestPrice(
            $product_id,
        );

        return ProductPriceResource::make($price);
    }
}
