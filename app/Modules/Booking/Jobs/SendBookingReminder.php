<?php

declare(strict_types=1);

namespace App\Modules\Booking\Jobs;

use App\Modules\Booking\Models\Booking;
use App\Modules\Notification\Notifications\BookingReminderNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendBookingReminder implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(): void
    {
        $bookings = Booking::confirmed()
            ->with(['user', 'service', 'timeSlot'])
            ->whereHas('timeSlot', function ($query): void {
                $query->where('date', now()->toDateString())
                    ->where('start_time', '>=', now()->format('H:i'))
                    ->where('start_time', '<=', now()->addHour()->format('H:i'));
            })
            ->get();

        foreach ($bookings as $booking) {
            $booking->user->notify(new BookingReminderNotification($booking));
        }
    }
}
