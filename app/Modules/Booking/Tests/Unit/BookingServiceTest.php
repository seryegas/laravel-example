<?php

declare(strict_types=1);

namespace App\Modules\Booking\Tests\Unit;

use App\Modules\Booking\DTOs\BookingData;
use App\Modules\Booking\Enums\BookingStatus;
use App\Modules\Booking\Events\BookingCreated;
use App\Modules\Booking\Services\BookingService;
use App\Modules\Core\Models\User;
use App\Modules\Service\Enums\SlotStatus;
use App\Modules\Service\Models\Category;
use App\Modules\Service\Models\Service;
use App\Modules\Service\Models\TimeSlot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class BookingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_booking_and_marks_slot_as_booked(): void
    {
        Event::fake([BookingCreated::class]);

        $user = User::factory()->create();
        $category = Category::factory()->create();
        $service = Service::factory()->create(['category_id' => $category->id]);
        $slot = TimeSlot::factory()->create([
            'service_id' => $service->id,
            'status' => SlotStatus::Available,
        ]);

        $data = new BookingData(
            userId: $user->id,
            serviceId: $service->id,
            timeSlotId: $slot->id,
            notes: 'Test notes',
        );

        $bookingService = new BookingService();
        $booking = $bookingService->create($data);

        $this->assertEquals(BookingStatus::Pending, $booking->status);
        $this->assertEquals($user->id, $booking->user_id);
        $this->assertEquals($service->id, $booking->service_id);
        $this->assertEquals($slot->id, $booking->time_slot_id);

        $slot->refresh();
        $this->assertEquals(SlotStatus::Booked, $slot->status);

        Event::assertDispatched(BookingCreated::class);
    }
}
