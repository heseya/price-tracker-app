<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Contracts\ConfigServiceContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ConfigController extends Controller
{
    public function __construct(
        private readonly ConfigServiceContract $configService,
    ) {
    }

    public function show(Request $request): JsonResponse
    {
        return Response::json(
            $this->configService->getConfigs($this->getApi($request)),
        );
    }
}
