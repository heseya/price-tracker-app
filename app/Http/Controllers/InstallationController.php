<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\InstallRequest;
use App\Services\Contracts\InstallationServiceContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class InstallationController extends Controller
{
    public function __construct(
        readonly private InstallationServiceContract $installationService,
    ) {
    }

    public function install(InstallRequest $request): JsonResponse
    {
        return Response::json([
            'uninstall_token' => $this->installationService->install(
                $request->input('api_url'),
                $request->input('integration_token'),
                $request->input('refresh_token'),
                $request->input('api_version'),
            ),
        ]);
    }

    public function uninstall(Request $request): JsonResponse
    {
        $this->installationService->uninstall($request->input('uninstall_token'));

        return Response::json(null, ResponseAlias::HTTP_NO_CONTENT);
    }
}
