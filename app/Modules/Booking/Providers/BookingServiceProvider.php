<?php

declare(strict_types=1);

namespace App\Modules\Booking\Providers;

use App\Modules\Booking\Events\BookingCancelled;
use App\Modules\Booking\Events\BookingCompleted;
use App\Modules\Booking\Events\BookingCreated;
use App\Modules\Booking\Jobs\CleanExpiredBookings;
use App\Modules\Booking\Jobs\SendBookingReminder;
use App\Modules\Booking\Listeners\LogBookingStatusChange;
use App\Modules\Booking\Listeners\ReleaseSlotOnCancellation;
use App\Modules\Booking\Models\Booking;
use App\Modules\Booking\Observers\BookingObserver;
use App\Modules\Booking\Policies\BookingPolicy;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class BookingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutes();
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->registerObservers();
        $this->registerPolicies();
        $this->registerEvents();
        $this->registerSchedule();
    }

    private function loadRoutes(): void
    {
        Route::prefix('api/v1')
            ->middleware('api')
            ->group(__DIR__ . '/../Routes/api.php');
    }

    private function registerObservers(): void
    {
        Booking::observe(BookingObserver::class);
    }

    private function registerPolicies(): void
    {
        Gate::policy(Booking::class, BookingPolicy::class);
    }

    private function registerEvents(): void
    {
        Event::listen(BookingCancelled::class, ReleaseSlotOnCancellation::class);
        Event::listen(BookingCreated::class, LogBookingStatusChange::class);
        Event::listen(BookingCancelled::class, LogBookingStatusChange::class);
        Event::listen(BookingCompleted::class, LogBookingStatusChange::class);
    }

    private function registerSchedule(): void
    {
        $this->app->booted(function (): void {
            /** @var Schedule $schedule */
            $schedule = $this->app->make(Schedule::class);
            $schedule->job(new CleanExpiredBookings())->everyFifteenMinutes();
            $schedule->job(new SendBookingReminder())->hourly();
        });
    }
}
