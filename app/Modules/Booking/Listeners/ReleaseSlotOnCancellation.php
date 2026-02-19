<?php

declare(strict_types=1);

namespace App\Modules\Booking\Listeners;

use App\Modules\Booking\Events\BookingCancelled;
use App\Modules\Service\Enums\SlotStatus;

class ReleaseSlotOnCancellation
{
    public function handle(BookingCancelled $event): void
    {
        $timeSlot = $event->booking->timeSlot;

        if ($timeSlot->status !== SlotStatus::Available) {
            $timeSlot->update(['status' => SlotStatus::Available]);
        }
    }
}
