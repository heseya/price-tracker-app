<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Api;
use App\Services\Contracts\InstallationServiceContract;
use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

final class InstallationService implements InstallationServiceContract
{
    /**
     * @throws RequestException
     * @throws Exception
     */
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
            throw new Exception('Failed to connect to the API');
        }

        if ($response->failed()) {
            throw new Exception('Failed to verify assigned permissions');
        }

        if ($response->json('data.url') === null) {
            throw new Exception('Integration token validation failed');
        }

        $permissions = $response->json('data.permissions');
        $requiredPermissions = Collection::make(Config::get('permissions.required'));

        if ($requiredPermissions->diff($permissions)->isNotEmpty()) {
            throw new Exception('App doesn\'t have all required permissions');
        }

        /** @var Api $api */
        $api = Api::query()->create([
            'url' => $storeUrl,
            'version' => $apiVersion,
            'integration_token' => $integrationToken,
            'refresh_token' => $refreshToken,
            'uninstall_token' => $uninstallToken = Str::random(128),
            'webhook_secret' => Str::random(32),
        ]);

        $this->createWebhook($api);

        return $uninstallToken;
    }

    public function uninstall(string $uninstallToken): void
    {
        Api::query()
            ->where('uninstall_token', $uninstallToken)
            ->delete();
    }

    /**
     * @throws RequestException
     */
    private function createWebhook(Api $api): void
    {
        $response = Http::withToken($api->integration_token)
            ->post("{$api->url}/webhooks", [
                'name' => 'Price Tracker Webhook',
                'url' => Config::get('app.url') . '/webhooks',
                'secret' => $api->webhook_secret,
                'with_issuer' => false,
                'with_hidden' => true,
                'events' => ['ProductPriceUpdated'],
            ]);

        if ($response->failed()) {
            $this->uninstall($api->uninstall_token);
            $response->throw();
        }
    }
}
