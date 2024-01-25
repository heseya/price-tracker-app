<?php

declare(strict_types=1);

namespace App\Services\Contracts;

interface InstallationServiceContract
{
    public function install(
        string $storeUrl,
        string $integrationToken,
        string $refreshToken,
        string $apiVersion,
    ): string;

    public function uninstall(string $uninstallToken): void;
}
