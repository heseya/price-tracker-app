<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Api;

interface ConfigServiceContract
{
    public function getConfigs(Api $api): array;
}
