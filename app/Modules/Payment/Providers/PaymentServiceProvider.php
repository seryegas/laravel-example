<?php

declare(strict_types=1);

namespace App\Modules\Payment\Providers;

use App\Modules\Payment\Events\PaymentReceived;
use App\Modules\Payment\Listeners\ConfirmBookingOnPayment;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutes();
        $this->loadMigrations();
        $this->registerEventListeners();
    }

    private function loadRoutes(): void
    {
        Route::prefix('api/v1')
            ->middleware('api')
            ->group(__DIR__ . '/../Routes/api.php');
    }

    private function loadMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }

    private function registerEventListeners(): void
    {
        Event::listen(PaymentReceived::class, ConfirmBookingOnPayment::class);
    }
}
