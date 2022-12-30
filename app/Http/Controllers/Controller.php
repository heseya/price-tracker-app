<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\ApiAuthenticationException;
use App\Models\Api;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    protected function getApi(Request $request): Api
    {
        $payload = Auth::getTokenPayload();

        /** @var Api $api */
        $api = Api::query()
            ->where('url', $payload ? $payload['iss'] : $request->header('X-Core-Url'))
            ->first();

        if (null === $api) {
            throw new ApiAuthenticationException('Api not authorized');
        }

        return $api;
    }
}
