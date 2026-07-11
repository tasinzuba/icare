<?php

use App\Http\Controllers\Api\DIDWebhookController;
use App\Http\Controllers\Api\OfflineAuthController;
use App\Http\Controllers\Api\OfflineDashboardController;
use App\Http\Controllers\Api\OfflineResultsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// D-ID Webhook (no auth required - called by D-ID service)
Route::post('/webhooks/d-id', [DIDWebhookController::class, 'handle'])
    ->name('webhooks.d-id');

/*
|--------------------------------------------------------------------------
| Offline Student Desktop App API
|--------------------------------------------------------------------------
*/

// Public — login (no auth needed)
Route::post('/offline/login', [OfflineAuthController::class, 'login']);

// Protected — requires Sanctum token
Route::middleware('auth:sanctum')->prefix('offline')->group(function () {
    Route::post('/logout', [OfflineAuthController::class, 'logout']);
    Route::get('/dashboard', [OfflineDashboardController::class, 'index']);
    Route::get('/results', [OfflineResultsController::class, 'index']);
});
