<?php

declare(strict_types=1);

namespace App\Modules\Payment\Database\Factories;

use App\Modules\Booking\Database\Factories\BookingFactory;
use App\Modules\Core\Database\Factories\UserFactory;
use App\Modules\Payment\Enums\PaymentStatus;
use App\Modules\Payment\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_id' => BookingFactory::new(),
            'user_id' => UserFactory::new(),
            'amount' => fake()->randomFloat(2, 10, 500),
            'status' => PaymentStatus::Pending,
            'payment_method' => 'card',
            'transaction_id' => null,
            'paid_at' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PaymentStatus::Paid,
            'transaction_id' => Str::uuid()->toString(),
            'paid_at' => now(),
        ]);
    }

    public function refunded(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PaymentStatus::Refunded,
            'transaction_id' => Str::uuid()->toString(),
            'paid_at' => now()->subHour(),
            'refunded_at' => now(),
        ]);
    }
}
