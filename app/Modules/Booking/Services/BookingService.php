<?php

declare(strict_types=1);

namespace App\Modules\Booking\Services;

use App\Modules\Booking\DTOs\BookingData;
use App\Modules\Booking\Enums\BookingStatus;
use App\Modules\Booking\Events\BookingCancelled;
use App\Modules\Booking\Events\BookingCompleted;
use App\Modules\Booking\Events\BookingCreated;
use App\Modules\Booking\Models\Booking;
use App\Modules\Core\Models\User;
use App\Modules\Service\Enums\SlotStatus;
use App\Modules\Service\Models\TimeSlot;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BookingService
{
    public function listForUser(User $user): LengthAwarePaginator
    {
        return Booking::with(['service', 'timeSlot'])
            ->forUser($user->id)
            ->latest()
            ->paginate();
    }

    public function listAll(): LengthAwarePaginator
    {
        return Booking::with(['service', 'timeSlot'])
            ->latest()
            ->paginate();
    }

    public function create(BookingData $data): Booking
    {
        return DB::transaction(function () use ($data): Booking {
            $timeSlot = TimeSlot::lockForUpdate()->findOrFail($data->timeSlotId);

            if ($timeSlot->status !== SlotStatus::Available) {
                throw new RuntimeException('The selected time slot is no longer available.');
            }

            $timeSlot->update(['status' => SlotStatus::Booked]);

            $booking = Booking::create([
                'user_id' => $data->userId,
                'service_id' => $data->serviceId,
                'time_slot_id' => $data->timeSlotId,
                'status' => BookingStatus::Pending,
                'notes' => $data->notes,
            ]);

            BookingCreated::dispatch($booking);

            return $booking;
        });
    }

    public function cancel(Booking $booking): Booking
    {
        if (!$booking->canBeCancelled()) {
            throw new RuntimeException('This booking cannot be cancelled.');
        }

        return DB::transaction(function () use ($booking): Booking {
            $booking->update([
                'status' => BookingStatus::Cancelled,
                'cancelled_at' => now(),
            ]);

            $booking->timeSlot()->update([
                'status' => SlotStatus::Available,
            ]);

            BookingCancelled::dispatch($booking);

            return $booking->refresh();
        });
    }

    public function complete(Booking $booking): Booking
    {
        if (!$booking->isConfirmed()) {
            throw new RuntimeException('Only confirmed bookings can be completed.');
        }

        $booking->update([
            'status' => BookingStatus::Completed,
        ]);

        BookingCompleted::dispatch($booking);

        return $booking->refresh();
    }
}
