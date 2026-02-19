<?php

declare(strict_types=1);

namespace App\Modules\Service\Tests\Unit;

use App\Modules\Service\Enums\SlotStatus;
use App\Modules\Service\Models\Service;
use App\Modules\Service\Models\TimeSlot;
use App\Modules\Service\Services\SlotService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlotServiceTest extends TestCase
{
    use RefreshDatabase;

    private SlotService $slotService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->slotService = new SlotService();
    }

    public function test_list_returns_only_available_slots(): void
    {
        $service = Service::factory()->create();

        TimeSlot::factory()->count(3)->create([
            'service_id' => $service->id,
            'status' => SlotStatus::Available,
            'date' => Carbon::today()->format('Y-m-d'),
        ]);
        TimeSlot::factory()->create([
            'service_id' => $service->id,
            'status' => SlotStatus::Booked,
            'date' => Carbon::today()->format('Y-m-d'),
        ]);

        $result = $this->slotService->list($service, []);

        $this->assertCount(3, $result->items());
    }

    public function test_list_filters_by_date_from(): void
    {
        $service = Service::factory()->create();

        TimeSlot::factory()->create([
            'service_id' => $service->id,
            'date' => Carbon::today()->format('Y-m-d'),
        ]);
        TimeSlot::factory()->create([
            'service_id' => $service->id,
            'date' => Carbon::tomorrow()->format('Y-m-d'),
        ]);

        $result = $this->slotService->list($service, [
            'date_from' => Carbon::tomorrow()->format('Y-m-d'),
        ]);

        $this->assertCount(1, $result->items());
    }

    public function test_list_filters_by_date_to(): void
    {
        $service = Service::factory()->create();

        TimeSlot::factory()->create([
            'service_id' => $service->id,
            'date' => Carbon::today()->format('Y-m-d'),
        ]);
        TimeSlot::factory()->create([
            'service_id' => $service->id,
            'date' => Carbon::today()->addDays(3)->format('Y-m-d'),
        ]);

        $result = $this->slotService->list($service, [
            'date_to' => Carbon::today()->format('Y-m-d'),
        ]);

        $this->assertCount(1, $result->items());
    }

    public function test_create_persists_slot_with_defaults(): void
    {
        $service = Service::factory()->create();

        $slot = $this->slotService->create($service, [
            'date' => '2025-06-01',
            'start_time' => '10:00',
            'end_time' => '11:00',
        ]);

        $this->assertDatabaseHas('time_slots', [
            'id' => $slot->id,
            'service_id' => $service->id,
            'status' => SlotStatus::Available->value,
        ]);
    }

    public function test_create_accepts_custom_status(): void
    {
        $service = Service::factory()->create();

        $slot = $this->slotService->create($service, [
            'date' => '2025-06-01',
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => 'blocked',
        ]);

        $this->assertDatabaseHas('time_slots', [
            'id' => $slot->id,
            'status' => SlotStatus::Blocked->value,
        ]);
    }

    public function test_delete_removes_slot(): void
    {
        $slot = TimeSlot::factory()->create();

        $this->slotService->delete($slot);

        $this->assertDatabaseMissing('time_slots', ['id' => $slot->id]);
    }
}
