<?php

declare(strict_types=1);

namespace App\Modules\Notification\Listeners;

use App\Modules\Notification\Notifications\BookingConfirmedNotification;
use App\Modules\Payment\Events\PaymentReceived;

class SendBookingConfirmation
{
    public function handle(PaymentReceived $event): void
    {
        $booking = $event->payment->booking;
        $user = $booking->user;

        $user->notify(new BookingConfirmedNotification($booking));
    }
}
