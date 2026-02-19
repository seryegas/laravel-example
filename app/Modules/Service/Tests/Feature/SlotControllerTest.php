<?php

declare(strict_types=1);

namespace App\Modules\Service\Tests\Feature;

use App\Modules\Core\Enums\UserRole;
use App\Modules\Core\Models\User;
use App\Modules\Service\Enums\SlotStatus;
use App\Modules\Service\Models\Service;
use App\Modules\Service\Models\TimeSlot;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlotControllerTest extends TestCase
{
    use RefreshDatabase;

    // --- Index (public) ---

    public function test_anyone_can_list_slots_for_service(): void
    {
        $service = Service::factory()->create();
        TimeSlot::factory()->count(3)->create([
            'service_id' => $service->id,
            'status' => SlotStatus::Available,
            'date' => Carbon::today()->format('Y-m-d'),
        ]);

        $response = $this->getJson("/api/v1/services/{$service->id}/slots");

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_list_returns_only_available_slots(): void
    {
        $service = Service::factory()->create();
        TimeSlot::factory()->count(2)->create([
            'service_id' => $service->id,
            'status' => SlotStatus::Available,
            'date' => Carbon::today()->format('Y-m-d'),
        ]);
        TimeSlot::factory()->create([
            'service_id' => $service->id,
            'status' => SlotStatus::Booked,
            'date' => Carbon::today()->format('Y-m-d'),
        ]);

        $response = $this->getJson("/api/v1/services/{$service->id}/slots");

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_list_filters_by_date_range(): void
    {
        $service = Service::factory()->create();
        $today = Carbon::today()->format('Y-m-d');
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');

        TimeSlot::factory()->create([
            'service_id' => $service->id,
            'date' => $today,
        ]);
        TimeSlot::factory()->create([
            'service_id' => $service->id,
            'date' => $tomorrow,
        ]);

        $response = $this->getJson("/api/v1/services/{$service->id}/slots?date_from={$tomorrow}");

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    // --- Store (admin/manager) ---

    public function test_admin_can_create_slot(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $service = Service::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/services/{$service->id}/slots", [
                'date' => '2025-06-01',
                'start_time' => '10:00',
                'end_time' => '11:00',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', SlotStatus::Available->value);
    }

    public function test_client_cannot_create_slot(): void
    {
        $client = User::factory()->create(['role' => UserRole::Client]);
        $service = Service::factory()->create();

        $response = $this->actingAs($client, 'sanctum')
            ->postJson("/api/v1/services/{$service->id}/slots", [
                'date' => '2025-06-01',
                'start_time' => '10:00',
                'end_time' => '11:00',
            ]);

        $response->assertForbidden();
    }

    public function test_store_validates_required_fields(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $service = Service::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/services/{$service->id}/slots", []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['date', 'start_time', 'end_time']);
    }

    public function test_store_validates_end_time_after_start_time(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $service = Service::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/services/{$service->id}/slots", [
                'date' => '2025-06-01',
                'start_time' => '14:00',
                'end_time' => '13:00',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['end_time']);
    }

    // --- Destroy (admin/manager) ---

    public function test_admin_can_delete_slot(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $slot = TimeSlot::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/slots/{$slot->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('time_slots', ['id' => $slot->id]);
    }

    public function test_client_cannot_delete_slot(): void
    {
        $client = User::factory()->create(['role' => UserRole::Client]);
        $slot = TimeSlot::factory()->create();

        $response = $this->actingAs($client, 'sanctum')
            ->deleteJson("/api/v1/slots/{$slot->id}");

        $response->assertForbidden();
    }

    public function test_guest_cannot_create_slot(): void
    {
        $service = Service::factory()->create();

        $this->postJson("/api/v1/services/{$service->id}/slots", [
            'date' => '2025-06-01',
            'start_time' => '10:00',
            'end_time' => '11:00',
        ])->assertUnauthorized();
    }
}
