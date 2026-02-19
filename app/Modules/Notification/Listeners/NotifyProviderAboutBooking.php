<?php

declare(strict_types=1);

namespace App\Modules\Notification\Listeners;

use App\Modules\Booking\Events\BookingCreated;
use Illuminate\Support\Facades\Log;

class NotifyProviderAboutBooking
{
    public function handle(BookingCreated $event): void
    {
        Log::info('Service provider would be notified about new booking.', [
            'booking_id' => $event->booking->id,
            'service_id' => $event->booking->service_id,
            'user_id' => $event->booking->user_id,
        ]);
    }
}
