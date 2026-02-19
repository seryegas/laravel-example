<?php

declare(strict_types=1);

namespace App\Modules\Notification\Services;

use App\Modules\Booking\Enums\BookingStatus;
use App\Modules\Booking\Models\Booking;
use App\Modules\Core\Models\User;
use App\Modules\Payment\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NotificationService
{
    public function list(User $user): LengthAwarePaginator
    {
        return $user->notifications()->paginate(15);
    }

    public function markAsRead(User $user, string $notificationId): void
    {
        $notification = $user->notifications()->findOrFail($notificationId);

        $notification->markAsRead();
    }

    public function markAllAsRead(User $user): void
    {
        $user->unreadNotifications->markAsRead();
    }

    /**
     * @return array<string, mixed>
     */
    public function generateDailyReport(): array
    {
        $today = now()->toDateString();

        $totalBookings = Booking::whereDate('created_at', $today)->count();
        $completed = Booking::whereDate('created_at', $today)
            ->where('status', BookingStatus::Completed)
            ->count();
        $cancelled = Booking::whereDate('created_at', $today)
            ->where('status', BookingStatus::Cancelled)
            ->count();
        $revenue = Payment::whereDate('paid_at', $today)
            ->sum('amount');

        return [
            'total_bookings' => $totalBookings,
            'completed' => $completed,
            'cancelled' => $cancelled,
            'revenue' => round((float) $revenue, 2),
        ];
    }
}
