<?php

declare(strict_types=1);

namespace App\Modules\Notification\Tests\Feature;

use App\Modules\Core\Models\User;
use App\Modules\Notification\Notifications\BookingConfirmedNotification;
use App\Modules\Booking\Models\Booking;
use App\Modules\Service\Models\Category;
use App\Modules\Service\Models\Service;
use App\Modules\Service\Models\TimeSlot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithNotifications(int $count = 3): User
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $service = Service::factory()->create(['category_id' => $category->id]);
        $slot = TimeSlot::factory()->create(['service_id' => $service->id]);

        $booking = Booking::factory()->create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'time_slot_id' => $slot->id,
        ]);

        for ($i = 0; $i < $count; $i++) {
            $user->notify(new BookingConfirmedNotification($booking));
        }

        return $user;
    }

    public function test_guest_cannot_access_notifications(): void
    {
        $this->getJson('/api/v1/notifications')->assertUnauthorized();
        $this->patchJson('/api/v1/notifications/fake-id/read')->assertUnauthorized();
        $this->patchJson('/api/v1/notifications/read-all')->assertUnauthorized();
    }

    public function test_user_can_list_notifications(): void
    {
        $user = $this->createUserWithNotifications(3);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/notifications');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    ['id', 'type', 'data', 'read_at', 'created_at'],
                ],
                'current_page',
                'per_page',
                'total',
            ]);
    }

    public function test_user_sees_only_own_notifications(): void
    {
        $user1 = $this->createUserWithNotifications(2);
        $user2 = $this->createUserWithNotifications(1);

        $response = $this->actingAs($user1, 'sanctum')
            ->getJson('/api/v1/notifications');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_user_can_mark_notification_as_read(): void
    {
        $user = $this->createUserWithNotifications(1);
        $notification = $user->notifications()->first();

        $this->assertNull($notification->read_at);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/notifications/{$notification->id}/read");

        $response->assertOk()
            ->assertJsonPath('message', 'Notification marked as read.');

        $notification->refresh();
        $this->assertNotNull($notification->read_at);
    }

    public function test_user_cannot_mark_others_notification_as_read(): void
    {
        $owner = $this->createUserWithNotifications(1);
        $other = User::factory()->create();
        $notification = $owner->notifications()->first();

        $response = $this->actingAs($other, 'sanctum')
            ->patchJson("/api/v1/notifications/{$notification->id}/read");

        $response->assertNotFound();
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $user = $this->createUserWithNotifications(3);

        $this->assertEquals(3, $user->unreadNotifications()->count());

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson('/api/v1/notifications/read-all');

        $response->assertOk()
            ->assertJsonPath('message', 'All notifications marked as read.');

        $user->refresh();
        $this->assertEquals(0, $user->unreadNotifications()->count());
    }

    public function test_notifications_are_paginated(): void
    {
        $user = $this->createUserWithNotifications(20);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/notifications');

        $response->assertOk()
            ->assertJsonPath('per_page', 15)
            ->assertJsonPath('total', 20)
            ->assertJsonCount(15, 'data');
    }
}
