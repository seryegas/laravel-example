<?php

declare(strict_types=1);

namespace App\Modules\Booking\Listeners;

use App\Modules\Booking\Events\BookingCancelled;
use App\Modules\Booking\Events\BookingCompleted;
use App\Modules\Booking\Events\BookingCreated;

class LogBookingStatusChange
{
    public function handle(BookingCreated|BookingCancelled|BookingCompleted $event): void
    {
        $booking = $event->booking;

        $logType = match ($event::class) {
            BookingCreated::class => 'booking_created',
            BookingCancelled::class => 'booking_cancelled',
            BookingCompleted::class => 'booking_completed',
        };

        $booking->logActivity(
            type: $logType,
            description: "Booking #{$booking->id} status: {$booking->status->label()}",
            properties: [
                'booking_id' => $booking->id,
                'status' => $booking->status->value,
            ],
        );
    }
}
