<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ProductPrice;
use App\Services\Contracts\ProductServiceContract;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public const PRODUCT_ID = 'f6322db5-97a1-4c8b-9e30-0cedacae0c16';

    public function testNoPrices(): void
    {
        $this
            ->json('GET', '/products/' . self::PRODUCT_ID)
            ->assertJsonFragment([
                'price_min' => null,
                'price_max' => null,
                'changed_at' => null,
                'currency' => null,
            ]);
    }

    public function testNoPricesOlderThan30Days(): void
    {
        $this->createPrice(10, Carbon::now()->subDay()); // current price
        $this->createPrice(5, Carbon::now()->subDays(40)); // more than 40 day
        $this
            ->json('GET', '/products/' . self::PRODUCT_ID)
            ->assertJsonFragment([
                'price_min' => null,
                'price_max' => null,
                'changed_at' => null,
                'currency' => null,
            ]);
    }

    public function testCheapestPrice(): void
    {
        $this->createPrice(20, Carbon::now()); // current
        $this->createPrice(10, Carbon::now()->subDay()); // lowest
        $this->createPrice(30, Carbon::now()->subDay());
        $this->createPrice(5, Carbon::now()->subDays(40)); // more than 30 day
        $this->createPrice(5, null, Str::uuid()->toString()); // other product
        $this
            ->json('GET', '/products/' . self::PRODUCT_ID)
            ->assertJsonFragment([
                'price_min' => 10.0,
                'price_max' => 10.0,
                'currency' => ProductServiceContract::DEFAULT_CURRENCY,
            ]);
    }

    private function createPrice(float $price, ?Carbon $changed_at = null, string $id = self::PRODUCT_ID): ProductPrice
    {
        return ProductPrice::query()->create([
            'product_id' => $id,
            'price_min' => $price,
            'price_max' => $price,
            'changed_at' => $changed_at ?? Carbon::now(),
        ]);
    }
}
