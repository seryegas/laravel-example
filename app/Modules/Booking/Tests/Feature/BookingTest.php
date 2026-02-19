<?php

declare(strict_types=1);

namespace App\Modules\Booking\Tests\Feature;

use App\Modules\Booking\Enums\BookingStatus;
use App\Modules\Core\Enums\UserRole;
use App\Modules\Core\Models\User;
use App\Modules\Service\Enums\SlotStatus;
use App\Modules\Service\Models\Category;
use App\Modules\Service\Models\Service;
use App\Modules\Service\Models\TimeSlot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    private function createServiceWithSlot(): array
    {
        $category = Category::factory()->create();
        $service = Service::factory()->create(['category_id' => $category->id]);
        $slot = TimeSlot::factory()->create([
            'service_id' => $service->id,
            'status' => SlotStatus::Available,
        ]);

        return [$service, $slot];
    }

    public function test_client_can_create_booking(): void
    {
        $user = User::factory()->create(['role' => UserRole::Client]);
        [$service, $slot] = $this->createServiceWithSlot();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/bookings', [
                'service_id' => $service->id,
                'time_slot_id' => $slot->id,
                'notes' => 'Test booking',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', BookingStatus::Pending->value);

        $this->assertDatabaseHas('time_slots', [
            'id' => $slot->id,
            'status' => SlotStatus::Booked->value,
        ]);
    }

    public function test_client_can_cancel_own_booking(): void
    {
        $user = User::factory()->create(['role' => UserRole::Client]);
        [$service, $slot] = $this->createServiceWithSlot();

        $booking = \App\Modules\Booking\Models\Booking::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'time_slot_id' => $slot->id,
            'status' => BookingStatus::Pending,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/bookings/{$booking->id}/cancel");

        $response->assertOk()
            ->assertJsonPath('data.status', BookingStatus::Cancelled->value);
    }

    public function test_client_cannot_cancel_others_booking(): void
    {
        $owner = User::factory()->create(['role' => UserRole::Client]);
        $other = User::factory()->create(['role' => UserRole::Client]);
        [$service, $slot] = $this->createServiceWithSlot();

        $booking = \App\Modules\Booking\Models\Booking::create([
            'user_id' => $owner->id,
            'service_id' => $service->id,
            'time_slot_id' => $slot->id,
            'status' => BookingStatus::Pending,
        ]);

        $response = $this->actingAs($other, 'sanctum')
            ->patchJson("/api/v1/bookings/{$booking->id}/cancel");

        $response->assertForbidden();
    }

    public function test_admin_can_complete_booking(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $client = User::factory()->create(['role' => UserRole::Client]);
        [$service, $slot] = $this->createServiceWithSlot();

        $booking = \App\Modules\Booking\Models\Booking::create([
            'user_id' => $client->id,
            'service_id' => $service->id,
            'time_slot_id' => $slot->id,
            'status' => BookingStatus::Confirmed,
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/bookings/{$booking->id}/complete");

        $response->assertOk()
            ->assertJsonPath('data.status', BookingStatus::Completed->value);
    }

    public function test_client_cannot_complete_booking(): void
    {
        $user = User::factory()->create(['role' => UserRole::Client]);
        [$service, $slot] = $this->createServiceWithSlot();

        $booking = \App\Modules\Booking\Models\Booking::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'time_slot_id' => $slot->id,
            'status' => BookingStatus::Confirmed,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/bookings/{$booking->id}/complete");

        $response->assertForbidden();
    }
}
