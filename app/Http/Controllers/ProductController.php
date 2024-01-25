<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\ApiAuthenticationException;
use App\Http\Requests\ProductIndexRequest;
use App\Http\Resources\ProductPriceResource;
use App\Services\Contracts\ProductServiceContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductServiceContract $productService,
    ) {}

    public function show(string $product_id, string $currency = ProductServiceContract::DEFAULT_CURRENCY): JsonResource
    {
        $price = $this->productService->findCheapestPrice($product_id, $currency);

        return ProductPriceResource::make($price);
    }

    public function index(ProductIndexRequest $request): JsonResource
    {
        $prices = $this->productService->findCheapestPrices(
            $request->input('product_ids', []),
            $request->input('currency', ProductServiceContract::DEFAULT_CURRENCY),
        );

        return ProductPriceResource::collection($prices);
    }

    public function update(Request $request): JsonResponse
    {
        if (
            $request->input('event') !== 'ProductPriceUpdated'
            || $request->input('data_type') !== 'ProductPrices'
            || $this->productService->checkSignature(
                $request->input('api_url'),
                $request->header('Signature'),
                $request->getContent(),
            )
        ) {
            throw new ApiAuthenticationException();
        }

        if ($request->input('data.new_price_min') || $request->input('data.new_price_max')) {
            $this->productService->update(
                $request->input('data.id'),
                $request->input('data.new_price_min'),
                $request->input('data.new_price_max'),
                $request->input('data.updated_at'),
                $request->input('data.currency'),
            );
        } else {
            $this->productService->updatePrices(
                $request->input('data.id'),
                $request->input('data.prices_min_new'),
                $request->input('data.prices_max_new'),
                $request->input('data.updated_at'),
            );
        }

        return Response::json(null, ResponseAlias::HTTP_NO_CONTENT);
    }
}
