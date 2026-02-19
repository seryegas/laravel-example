<?php

declare(strict_types=1);

namespace App\Modules\Service\Database\Seeders;

use App\Modules\Service\Enums\SlotStatus;
use App\Modules\Service\Models\Category;
use App\Modules\Service\Models\Service;
use App\Modules\Service\Models\TimeSlot;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $categories = $this->createCategories();

        foreach ($categories as $category) {
            $services = $this->createServices($category);

            foreach ($services as $service) {
                $this->createTimeSlots($service);
            }
        }
    }

    /**
     * @return array<int, Category>
     */
    private function createCategories(): array
    {
        $names = ['Haircuts', 'Spa', 'Consulting', 'Fitness'];

        return array_map(
            fn (string $name): Category => Category::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => "Professional {$name} services",
                'is_active' => true,
            ]),
            $names,
        );
    }

    /**
     * @return array<int, Service>
     */
    private function createServices(Category $category): array
    {
        $serviceMap = [
            'Haircuts' => [
                ['name' => 'Men\'s Haircut', 'duration' => 30, 'price' => 25.00],
                ['name' => 'Women\'s Haircut', 'duration' => 45, 'price' => 45.00],
                ['name' => 'Kids Haircut', 'duration' => 30, 'price' => 15.00],
                ['name' => 'Beard Trim', 'duration' => 15, 'price' => 12.00],
            ],
            'Spa' => [
                ['name' => 'Swedish Massage', 'duration' => 60, 'price' => 80.00],
                ['name' => 'Deep Tissue Massage', 'duration' => 90, 'price' => 120.00],
                ['name' => 'Facial Treatment', 'duration' => 45, 'price' => 65.00],
                ['name' => 'Hot Stone Therapy', 'duration' => 60, 'price' => 95.00],
                ['name' => 'Aromatherapy', 'duration' => 60, 'price' => 85.00],
            ],
            'Consulting' => [
                ['name' => 'Business Strategy', 'duration' => 60, 'price' => 150.00],
                ['name' => 'Financial Planning', 'duration' => 90, 'price' => 200.00],
                ['name' => 'Career Coaching', 'duration' => 45, 'price' => 100.00],
            ],
            'Fitness' => [
                ['name' => 'Personal Training', 'duration' => 60, 'price' => 70.00],
                ['name' => 'Yoga Class', 'duration' => 60, 'price' => 30.00],
                ['name' => 'Pilates Session', 'duration' => 45, 'price' => 35.00],
                ['name' => 'CrossFit Training', 'duration' => 60, 'price' => 40.00],
                ['name' => 'Spin Class', 'duration' => 45, 'price' => 25.00],
            ],
        ];

        $services = $serviceMap[$category->name] ?? [];

        return array_map(
            fn (array $data): Service => Service::create([
                'category_id' => $category->id,
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'description' => "Professional {$data['name']} service",
                'duration_minutes' => $data['duration'],
                'price' => $data['price'],
                'is_active' => true,
            ]),
            $services,
        );
    }

    private function createTimeSlots(Service $service): void
    {
        $period = CarbonPeriod::create(
            Carbon::today(),
            Carbon::today()->addDays(6),
        );

        foreach ($period as $date) {
            $startHour = 9;
            $endHour = 18;
            $currentTime = Carbon::parse($date)->setTime($startHour, 0);
            $dayEnd = Carbon::parse($date)->setTime($endHour, 0);

            while ($currentTime->copy()->addMinutes($service->duration_minutes)->lte($dayEnd)) {
                $slotEnd = $currentTime->copy()->addMinutes($service->duration_minutes);

                TimeSlot::create([
                    'service_id' => $service->id,
                    'date' => $date->format('Y-m-d'),
                    'start_time' => $currentTime->format('H:i'),
                    'end_time' => $slotEnd->format('H:i'),
                    'status' => SlotStatus::Available,
                ]);

                $currentTime = $slotEnd;
            }
        }
    }
}
