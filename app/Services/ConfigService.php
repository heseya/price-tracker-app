<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Api;
use App\Services\Contracts\ConfigServiceContract;

final class ConfigService implements ConfigServiceContract
{
    public function getConfigs(Api $api): array
    {
        return [];
    }
}
