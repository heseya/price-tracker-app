<?php

declare(strict_types=1);

namespace App\Providers;

use App\Exceptions\ApiClientErrorException;
use App\Exceptions\InvalidTokenException;
use App\Models\Api;
use App\Models\StoreUser;
use App\Services\Contracts\ApiServiceContract;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Throwable;

class HeseyaStoreGuard implements Guard
{
    private ?Authenticatable $user = null;

    private ?string $token = null;

    private ?string $apiUrl = null;

    public function __construct(
        private ApiServiceContract $apiService,
        private Request $request,
    ) {
        Gate::before(function ($user, $ability) {
            if ($user instanceof StoreUser && $user->getPermissions()->contains($ability)) {
                return true;
            }
        });
    }

    public function check(): bool
    {
        return null !== $this->user;
    }

    public function guest(): bool
    {
        return false;
    }

    public function user(): ?Authenticatable
    {
        return $this->user ?? $this->fetchUser();
    }

    public function id()
    {
        return $this->user->getAuthIdentifier();
    }

    public function validate(array $credentials = []): void
    {
        // TODO: Implement validate() method.
    }

    public function setUser(Authenticatable $user): void
    {
        $this->user = $user;
    }

    public function fetchUser(): ?Authenticatable
    {
        $headerApiUrl = $this->request->header('X-Core-Url');
        $apiUrl = $this->request->get('api');
        $token = $this->getToken();

        if (null !== $apiUrl && (null !== $headerApiUrl && $headerApiUrl !== $apiUrl)) {
            return null;
        }
        $apiUrl = $apiUrl ?? $headerApiUrl;

        if (($apiUrl === $this->apiUrl && $token === $this->token) || (null !== $token && null === $headerApiUrl)) {
            return null;
        }

        [$this->token, $this->apiUrl] = [$token, $apiUrl];

        try {
            $api = Api::where('url', $apiUrl)->firstOrFail();
        } catch (Throwable) {
            throw new InvalidTokenException('Unregistered API call');
        }

        $payload = $this->getTokenPayload();
        if (null !== $payload && rtrim($apiUrl, '/') !== rtrim($payload['iss'], '/')) {
            throw new InvalidTokenException("Token doesn't match the X-Core-Url API");
        }

        try {
            $response = $this->apiService->get($api, "/auth/check/{$token}");
        } catch (ApiClientErrorException) {
            throw new AuthenticationException('Invalid identity_token');
        }

        $this->user = new StoreUser(
            $response->json('data.id'),
            $response->json('data.name'),
            $response->json('data.avatar'),
            $response->json('data.permissions'),
        );

        return $this->user;
    }

    public function getTokenPayload(): ?array
    {
        $token = $this->getToken();

        if (null === $token) {
            return null;
        }

        $payloadEncoded = Str::between($token, '.', '.');

        return json_decode(base64_decode($payloadEncoded, true), true);
    }

    private function getToken(): ?string
    {
        return $this->request->bearerToken();
    }

    public function hasUser(): bool
    {
        return null !== $this->user;
    }
}
