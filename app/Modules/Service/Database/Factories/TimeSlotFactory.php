<?php

declare(strict_types=1);

namespace App\Modules\Service\Database\Factories;

use App\Modules\Service\Enums\SlotStatus;
use App\Modules\Service\Models\TimeSlot;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TimeSlot>
 */
class TimeSlotFactory extends Factory
{
    protected $model = TimeSlot::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = fake()->randomElement([
            Carbon::today(),
            Carbon::tomorrow(),
        ]);

        $startHour = fake()->numberBetween(8, 18);
        $startTime = Carbon::createFromTime($startHour, 0);
        $endTime = (clone $startTime)->addMinutes(60);

        return [
            'service_id' => ServiceFactory::new(),
            'date' => $date->format('Y-m-d'),
            'start_time' => $startTime->format('H:i'),
            'end_time' => $endTime->format('H:i'),
            'status' => SlotStatus::Available,
        ];
    }
}
