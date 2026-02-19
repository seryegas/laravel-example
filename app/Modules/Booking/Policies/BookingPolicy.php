<?php

declare(strict_types=1);

namespace App\Modules\Booking\Policies;

use App\Modules\Booking\Models\Booking;
use App\Modules\Core\Models\User;

class BookingPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Booking $booking): bool
    {
        return $user->id === $booking->user_id
            || $user->isAdmin()
            || $user->isManager();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function cancel(User $user, Booking $booking): bool
    {
        if ($user->isAdmin()) {
            return $booking->canBeCancelled();
        }

        return $user->id === $booking->user_id && $booking->canBeCancelled();
    }

    public function complete(User $user, Booking $booking): bool
    {
        return $user->isAdmin() || $user->isManager();
    }
}
