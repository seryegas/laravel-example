<?php

declare(strict_types=1);

namespace App\Modules\Booking\Jobs;

use App\Modules\Booking\Models\Booking;
use App\Modules\Booking\Services\BookingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CleanExpiredBookings implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(BookingService $bookingService): void
    {
        $expiredBookings = Booking::pending()
            ->where('created_at', '<', now()->subMinutes(30))
            ->whereDoesntHave('payments')
            ->get();

        foreach ($expiredBookings as $booking) {
            try {
                $bookingService->cancel($booking);
            } catch (\Throwable $e) {
                Log::warning("Failed to cancel expired booking #{$booking->id}: {$e->getMessage()}");
            }
        }
    }
}
