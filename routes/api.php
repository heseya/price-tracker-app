<?php

declare(strict_types=1);

use App\Http\Controllers\ConfigController;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\InstallationController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', [InfoController::class, 'index']);

Route::post('/install', [InstallationController::class, 'install']);
Route::post('/uninstall', [InstallationController::class, 'uninstall']);

Route::get('/config', [ConfigController::class, 'show'])
    ->middleware('can:configure');
Route::post('/config', [ConfigController::class, 'show'])
    ->middleware('can:configure');

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product_id}/{currency?}', [ProductController::class, 'show']);
Route::post('/webhooks', [ProductController::class, 'update']);
