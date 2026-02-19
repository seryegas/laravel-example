<?php

declare(strict_types=1);

namespace App\Modules\Payment\Listeners;

use App\Modules\Booking\Enums\BookingStatus;
use App\Modules\Payment\Events\PaymentReceived;

class ConfirmBookingOnPayment
{
    public function handle(PaymentReceived $event): void
    {
        $booking = $event->payment->booking;

        if ($booking->status === BookingStatus::Pending) {
            $booking->update([
                'status' => BookingStatus::Confirmed,
            ]);
        }
    }
}
