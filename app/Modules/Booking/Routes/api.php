<?php

declare(strict_types=1);

use App\Modules\Booking\Controllers\BookingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function (): void {
    Route::get('bookings', [BookingController::class, 'index']);
    Route::post('bookings', [BookingController::class, 'store']);
    Route::get('bookings/{booking}', [BookingController::class, 'show']);
    Route::patch('bookings/{booking}/cancel', [BookingController::class, 'cancel']);
    Route::patch('bookings/{booking}/complete', [BookingController::class, 'complete'])
        ->middleware('role:admin,manager');
});
