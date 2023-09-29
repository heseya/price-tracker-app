<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Api;
use App\Models\ProductPrice;
use App\Services\Contracts\ProductServiceContract;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    use RefreshDatabase;

    public const PRODUCT_ID = 'f6322db5-97a1-4c8b-9e30-0cedacae0c16';

    public Api $api;

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
    }

    public function testUpdate(): void
    {
        $now = Carbon::now()->toIso8601String();

        $this
            ->json('POST', '/webhooks', [
                'api_url' => $this->api->url,
                'event' => 'ProductPriceUpdated',
                'data_type' => 'ProductPrices',
                'data' => [
                    'id' => self::PRODUCT_ID,
                    'new_price_min' => 10.0,
                    'new_price_max' => 20.0,
                    'updated_at' => $now,
                ],
            ], ['Signature' => $this->api->webhook_secret])
            ->assertNoContent();

        $this->assertDatabaseHas('product_prices', [
            'product_id' => self::PRODUCT_ID,
            'price_min' => 10.0,
            'price_max' => 20.0,
            'changed_at' => $now,
            'currency' => ProductServiceContract::DEFAULT_CURRENCY,
        ]);
    }

    public function testUpdateSamePrice(): void
    {
        $price = ProductPrice::query()->create([
            'product_id' => self::PRODUCT_ID,
            'price_min' => 10.0,
            'price_max' => 20.0,
            'changed_at' => Carbon::now()->toIso8601String(),
        ]);

        $this
            ->json('POST', '/webhooks', [
                'api_url' => $this->api->url,
                'event' => 'ProductPriceUpdated',
                'data_type' => 'ProductPrices',
                'data' => [
                    'id' => self::PRODUCT_ID,
                    'new_price_min' => 10.0,
                    'new_price_max' => 20.0,
                    'updated_at' => Carbon::now()->toIso8601String(),
                ],
            ], ['Signature' => $this->api->webhook_secret])
            ->assertNoContent();

        $this
            ->assertDatabaseCount('product_prices', 1)
            ->assertDatabaseHas('product_prices', ['id' => $price->getKey()]);
    }
}
