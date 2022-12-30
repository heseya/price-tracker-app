<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ApiAuthenticationException;
use App\Exceptions\ApiAuthorizationException;
use App\Exceptions\ApiClientErrorException;
use App\Exceptions\ApiConnectionException;
use App\Exceptions\ApiServerErrorException;
use App\Models\Api;
use App\Services\Contracts\ApiServiceContract;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

final class ApiService implements ApiServiceContract
{
    /**
     * @throws ApiAuthenticationException
     * @throws ApiAuthorizationException
     * @throws ApiClientErrorException
     * @throws ApiConnectionException
     * @throws ApiServerErrorException
     */
    public function get(
        Api $api,
        string $url,
        array $headers = [],
        bool $tryRefreshing = true,
    ): Response {
        return $this->send($api, 'get', $url, null, $headers, $tryRefreshing);
    }

    /**
     * @throws ApiAuthenticationException
     * @throws ApiAuthorizationException
     * @throws ApiClientErrorException
     * @throws ApiConnectionException
     * @throws ApiServerErrorException
     */
    public function post(
        Api $api,
        string $url,
        array $data = [],
        array $headers = [],
        bool $tryRefreshing = true,
    ): Response {
        return $this->send($api, 'post', $url, $data, $headers, $tryRefreshing);
    }

    /**
     * @throws ApiAuthenticationException
     * @throws ApiAuthorizationException
     * @throws ApiClientErrorException
     * @throws ApiConnectionException
     * @throws ApiServerErrorException
     */
    public function patch(
        Api $api,
        string $url,
        array $data = [],
        array $headers = [],
        bool $tryRefreshing = true,
    ): Response {
        return $this->send($api, 'patch', $url, $data, $headers, $tryRefreshing);
    }

    /**
     * @throws ApiAuthenticationException
     * @throws ApiAuthorizationException
     * @throws ApiClientErrorException
     * @throws ApiConnectionException
     * @throws ApiServerErrorException
     */
    public function delete(
        Api $api,
        string $url,
        array $data = [],
        array $headers = [],
        bool $tryRefreshing = true,
    ): Response {
        return $this->send($api, 'delete', $url, $data, $headers, $tryRefreshing);
    }

    /**
     * @throws ApiAuthenticationException
     * @throws ApiAuthorizationException
     * @throws ApiClientErrorException
     * @throws ApiConnectionException
     * @throws ApiServerErrorException
     */
    private function refreshToken(Api $api): Api
    {
        try {
            $response = $this->send($api, 'post', '/auth/refresh', [
                'refresh_token' => $api->refresh_token,
            ], [], false, false);
        } catch (ApiAuthenticationException) {
            throw new ApiAuthenticationException('Failed refreshing integration token');
        }

        $api->update([
            'integration_token' => $response->json('data.token'),
            'refresh_token' => $response->json('data.refresh_token'),
        ]);

        return $api;
    }

    /**
     * @throws ApiAuthenticationException
     * @throws ApiAuthorizationException
     * @throws ApiClientErrorException
     * @throws ApiConnectionException
     * @throws ApiServerErrorException
     */
    public function send(
        Api $api,
        string $method,
        string $url,
        ?array $data = [],
        array $headers = [],
        bool $tryRefreshing = true,
        bool $withToken = true,
    ): Response {
        $request = Http::acceptJson()->asJson()->withHeaders($headers);

        if ($withToken) {
            $request = $request->withToken($api->integration_token);
        }

        $fullUrl = rtrim($api->url.$url, '/');

        $response = match ($method) {
            'post' => $request->post($fullUrl, $data),
            'patch' => $request->patch($fullUrl, $data),
            'delete' => $request->delete($fullUrl, $data),
            default => $request->get($fullUrl, $data),
        };

        if ($response->failed()) {
            if ($response->serverError()) {
                throw new ApiServerErrorException('API responded with an Error');
            }

            if (403 === $response->status()) {
                throw new ApiAuthorizationException('This action is unauthorized by API');
            }

            if (401 !== $response->status()) {
                throw new ApiClientErrorException('API responded with an Error');
            }

            if (false === $tryRefreshing) {
                throw new ApiAuthenticationException('Integration token was rejected by API');
            }

            $api = $this->refreshToken($api);
            $response = $this->send($api, $method, $url, $data, $headers, false);
        }

        return $response;
    }
}
