<?php

declare(strict_types=1);

namespace App\Modules\Booking\Database\Seeders;

use App\Modules\Booking\Enums\BookingStatus;
use App\Modules\Booking\Models\Booking;
use App\Modules\Core\Enums\UserRole;
use App\Modules\Core\Models\User;
use App\Modules\Service\Enums\SlotStatus;
use App\Modules\Service\Models\TimeSlot;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $clients = User::where('role', UserRole::Client->value)->get();
        $statuses = [
            BookingStatus::Pending,
            BookingStatus::Confirmed,
            BookingStatus::Completed,
        ];

        foreach ($clients as $client) {
            $slots = TimeSlot::where('status', SlotStatus::Available->value)
                ->inRandomOrder()
                ->limit(fake()->numberBetween(2, 5))
                ->get();

            foreach ($slots as $slot) {
                $status = fake()->randomElement($statuses);

                Booking::create([
                    'user_id' => $client->id,
                    'service_id' => $slot->service_id,
                    'time_slot_id' => $slot->id,
                    'status' => $status,
                    'notes' => fake()->optional(0.3)->sentence(),
                ]);

                $slot->update(['status' => SlotStatus::Booked]);
            }
        }
    }
}
