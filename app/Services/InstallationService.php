<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Api;
use App\Services\Contracts\InstallationServiceContract;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

final class InstallationService implements InstallationServiceContract
{
    public function install(
        string $storeUrl,
        string $integrationToken,
        string $refreshToken,
        string $apiVersion,
    ): string {
        $storeUrl = rtrim($storeUrl, '/');

        try {
            $response = Http::withToken($integrationToken)->get("{$storeUrl}/auth/profile");
        } catch (Throwable) {
            throw new \Exception('Failed to connect to the API');
        }

        if ($response->failed()) {
            throw new \Exception('Failed to verify assigned permissions');
        }

        if (null === $response->json('data.url')) {
            throw new \Exception('Integration token validation failed');
        }

        $permissions = $response->json('data.permissions');
        $requiredPermissions = Collection::make(Config::get('permissions.required'));

        if ($requiredPermissions->diff($permissions)->isNotEmpty()) {
            throw new \Exception('App doesn\'t have all required permissions');
        }

        do {
            $uninstallToken = Str::random(128);
        } while (Api::query()->where('uninstall_token', $uninstallToken)->exists());

        Api::query()->create([
            'url' => $storeUrl,
            'version' => $apiVersion,
            'integration_token' => $integrationToken,
            'refresh_token' => $refreshToken,
            'uninstall_token' => $uninstallToken,
            'auth_token' => Str::random(64),
            'furgonetka_token' => Str::random(32),
        ]);

        return $uninstallToken;
    }

    public function uninstall(string $uninstallToken): void
    {
        Api::query()
            ->where('uninstall_token', $uninstallToken)
            ->delete();
    }
}
