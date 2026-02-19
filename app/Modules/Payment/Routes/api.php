<?php

declare(strict_types=1);

use App\Modules\Payment\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function (): void {
    Route::get('payments', [PaymentController::class, 'index']);
    Route::post('bookings/{booking}/pay', [PaymentController::class, 'store']);
    Route::post('payments/{payment}/refund', [PaymentController::class, 'refund'])->middleware('role:admin');
});
