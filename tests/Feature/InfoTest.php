<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class InfoTest extends TestCase
{
    public function testInfoIndex(): void
    {
        $this->json('GET', '/')
            ->assertOk()
            ->assertJsonFragment([
                'name' => Config::get('app.name'),
                'author' => Config::get('app.author'),
            ]);
    }
}
