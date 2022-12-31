<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\ApiAuthenticationException;
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
    ) {
    }

    public function show(string $product_id): JsonResource
    {
        $price = $this->productService->findCheapestPrice(
            $product_id,
        );

        return ProductPriceResource::make($price);
    }

    public function update(Request $request): JsonResponse
    {
        if (
            'ProductPriceUpdated' !== $request->input('event') ||
            'ProductPrices' !== $request->input('data_type') ||
            $this->productService->checkSignature(
                $request->input('api_url'),
                $request->header('Signature'),
                $request->getContent(),
            )
        ) {
            throw new ApiAuthenticationException();
        }

        $this->productService->update(
            $request->input('data.id'),
            $request->input('data.new_price_min'),
            $request->input('data.new_price_max'),
            $request->input('data.updated_at'),
        );

        return Response::json(null, ResponseAlias::HTTP_NO_CONTENT);
    }
}
