<?php

declare(strict_types=1);

use App\Modules\Service\Controllers\CategoryController;
use App\Modules\Service\Controllers\ServiceController;
use App\Modules\Service\Controllers\SlotController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{category}', [CategoryController::class, 'show']);
Route::get('services', [ServiceController::class, 'index']);
Route::get('services/{service}', [ServiceController::class, 'show']);
Route::get('services/{service}/slots', [SlotController::class, 'index']);

// Protected routes
Route::middleware('auth:sanctum')->group(function (): void {
    Route::middleware('role:admin,manager')->group(function (): void {
        Route::post('services', [ServiceController::class, 'store']);
        Route::put('services/{service}', [ServiceController::class, 'update']);
        Route::delete('services/{service}', [ServiceController::class, 'destroy']);
        Route::post('services/{service}/slots', [SlotController::class, 'store']);
        Route::delete('slots/{slot}', [SlotController::class, 'destroy']);
    });
});
