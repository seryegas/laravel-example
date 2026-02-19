<?php

declare(strict_types=1);

namespace App\Modules\Booking\Observers;

use App\Modules\Booking\Models\Booking;

class BookingObserver
{
    public function created(Booking $booking): void
    {
        $booking->logActivity(
            type: 'booking_created',
            description: "Booking #{$booking->id} was created.",
        );
    }

    public function updated(Booking $booking): void
    {
        if ($booking->wasChanged('status')) {
            $oldStatus = $booking->getOriginal('status');
            $newStatus = $booking->status;

            $booking->logActivity(
                type: 'booking_status_changed',
                description: "Booking #{$booking->id} status changed.",
                properties: [
                    'old_status' => $oldStatus instanceof \BackedEnum ? $oldStatus->value : $oldStatus,
                    'new_status' => $newStatus->value,
                ],
            );
        }
    }
}
