<?php

declare(strict_types=1);

namespace App\Modules\Booking\Database\Factories;

use App\Modules\Booking\Enums\BookingStatus;
use App\Modules\Booking\Models\Booking;
use App\Modules\Core\Database\Factories\UserFactory;
use App\Modules\Service\Database\Factories\ServiceFactory;
use App\Modules\Service\Database\Factories\TimeSlotFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => UserFactory::new(),
            'service_id' => ServiceFactory::new(),
            'time_slot_id' => TimeSlotFactory::new(),
            'status' => BookingStatus::Pending,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function confirmed(): static
    {
        return $this->state(['status' => BookingStatus::Confirmed]);
    }

    public function completed(): static
    {
        return $this->state(['status' => BookingStatus::Completed]);
    }

    public function cancelled(): static
    {
        return $this->state([
            'status' => BookingStatus::Cancelled,
            'cancelled_at' => now(),
        ]);
    }
}
