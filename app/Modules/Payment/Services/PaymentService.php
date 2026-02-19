<?php

declare(strict_types=1);

namespace App\Modules\Payment\Services;

use App\Modules\Booking\Models\Booking;
use App\Modules\Core\Models\User;
use App\Modules\Payment\Enums\PaymentStatus;
use App\Modules\Payment\Events\PaymentReceived;
use App\Modules\Payment\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PaymentService
{
    public function listForUser(User $user): LengthAwarePaginator
    {
        return Payment::query()
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(15);
    }

    public function listAll(): LengthAwarePaginator
    {
        return Payment::query()
            ->latest()
            ->paginate(15);
    }

    public function processPayment(Booking $booking, string $paymentMethod = 'card'): Payment
    {
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'user_id' => $booking->user_id,
            'amount' => $booking->service->price,
            'status' => PaymentStatus::Pending,
            'payment_method' => $paymentMethod,
        ]);

        // Simulate payment processing
        $payment->update([
            'status' => PaymentStatus::Paid,
            'transaction_id' => Str::uuid()->toString(),
            'paid_at' => now(),
        ]);

        PaymentReceived::dispatch($payment);

        return $payment;
    }

    public function refund(Payment $payment): Payment
    {
        if (!$payment->canBeRefunded()) {
            throw new InvalidArgumentException('This payment cannot be refunded.');
        }

        $payment->update([
            'status' => PaymentStatus::Refunded,
            'refunded_at' => now(),
        ]);

        return $payment;
    }
}
