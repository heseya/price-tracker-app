<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('apis', function (Blueprint $table): void {
            $table->id();
            $table->string('url')->unique();
            $table->string('version');
            $table->string('integration_token', 500);
            $table->string('refresh_token', 500);
            $table->string('uninstall_token', 500)->unique();
            $table->string('webhook_secret', 32)->unique();
            $table->timestamps();
        });

        Schema::create('product_prices', function (Blueprint $table): void {
            $table->id();
            $table->uuid('product_id');
            $table->decimal('price_min', 16, 4);
            $table->decimal('price_max', 16, 4);
            $table->dateTime('changed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apis');
        Schema::dropIfExists('product_prices');
    }
};
