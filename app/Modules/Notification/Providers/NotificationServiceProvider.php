<?php

declare(strict_types=1);

namespace App\Modules\Notification\Providers;

use App\Modules\Booking\Events\BookingCreated;
use App\Modules\Notification\Listeners\NotifyProviderAboutBooking;
use App\Modules\Notification\Listeners\SendBookingConfirmation;
use App\Modules\Payment\Events\PaymentReceived;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutes();
        $this->registerEventListeners();
    }

    private function loadRoutes(): void
    {
        Route::prefix('api/v1')
            ->middleware('api')
            ->group(__DIR__ . '/../Routes/api.php');
    }

    private function registerEventListeners(): void
    {
        Event::listen(PaymentReceived::class, SendBookingConfirmation::class);
        Event::listen(BookingCreated::class, NotifyProviderAboutBooking::class);
    }
}
