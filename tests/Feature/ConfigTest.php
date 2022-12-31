<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Api;
use App\Models\StoreUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class ConfigTest extends TestCase
{
    use RefreshDatabase;

    private Api $api;
    private StoreUser $user;
    private array $excepted;

    protected function setUp(): void
    {
        parent::setUp();

        $this->api = Api::query()->create([
            'url' => 'https://exists.com',
            'name' => 'Exists',
            'version' => '2.0.0',
            'integration_token' => Str::random(),
            'refresh_token' => Str::random(),
            'uninstall_token' => Str::random(),
            'webhook_secret' => Str::random(32),
        ]);

        $this->user = new StoreUser(Str::uuid()->toString(), 'User', '', ['configure']);
    }

//    public function testConfig(): void
//    {
//        Http::fake([
//            "{$this->api->url}/auth/check" => Http::response([
//                'data' => [
//                    'id' => $this->user->id,
//                    'name' => 'Authenticated',
//                    'avatar' => '',
//                    'permissions' => [
//                        'configure',
//                    ],
//                ],
//            ]),
//        ]);
//
//        $this
//            ->json('GET', '/config')
//            ->assertOk();
//    }

    public function testConfigUnauthorized(): void
    {
        Http::fake([
            "{$this->api->url}/auth/check" => Http::response([
                'data' => [
                    'id' => null,
                    'name' => 'Unauthenticated',
                    'avatar' => '',
                    'permissions' => [],
                ],
            ]),
        ]);

        $this
            ->json('GET', '/config')
            ->assertForbidden();
    }
}
