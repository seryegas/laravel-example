<?php

declare(strict_types=1);

namespace App\Modules\Notification\Tests\Unit;

use App\Modules\Booking\Enums\BookingStatus;
use App\Modules\Booking\Models\Booking;
use App\Modules\Core\Models\User;
use App\Modules\Notification\Notifications\BookingConfirmedNotification;
use App\Modules\Notification\Services\NotificationService;
use App\Modules\Payment\Enums\PaymentStatus;
use App\Modules\Payment\Models\Payment;
use App\Modules\Service\Models\Category;
use App\Modules\Service\Models\Service;
use App\Modules\Service\Models\TimeSlot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    private NotificationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new NotificationService();
    }

    private function createBookingForUser(User $user): Booking
    {
        $category = Category::factory()->create();
        $service = Service::factory()->create(['category_id' => $category->id]);
        $slot = TimeSlot::factory()->create(['service_id' => $service->id]);

        return Booking::factory()->create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'time_slot_id' => $slot->id,
        ]);
    }

    public function test_list_returns_paginated_notifications(): void
    {
        $user = User::factory()->create();
        $booking = $this->createBookingForUser($user);

        $user->notify(new BookingConfirmedNotification($booking));
        $user->notify(new BookingConfirmedNotification($booking));

        $result = $this->service->list($user);

        $this->assertEquals(2, $result->total());
        $this->assertEquals(15, $result->perPage());
    }

    public function test_mark_as_read_marks_single_notification(): void
    {
        $user = User::factory()->create();
        $booking = $this->createBookingForUser($user);

        $user->notify(new BookingConfirmedNotification($booking));
        $user->notify(new BookingConfirmedNotification($booking));

        $notification = $user->unreadNotifications()->first();

        $this->service->markAsRead($user, $notification->id);

        $this->assertEquals(1, $user->unreadNotifications()->count());

        $notification->refresh();
        $this->assertNotNull($notification->read_at);
    }

    public function test_mark_as_read_fails_for_nonexistent_notification(): void
    {
        $user = User::factory()->create();

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->service->markAsRead($user, '00000000-0000-0000-0000-000000000000');
    }

    public function test_mark_all_as_read(): void
    {
        $user = User::factory()->create();
        $booking = $this->createBookingForUser($user);

        $user->notify(new BookingConfirmedNotification($booking));
        $user->notify(new BookingConfirmedNotification($booking));
        $user->notify(new BookingConfirmedNotification($booking));

        $this->assertEquals(3, $user->unreadNotifications()->count());

        $this->service->markAllAsRead($user);

        $user->refresh();
        $this->assertEquals(0, $user->unreadNotifications()->count());
    }

    public function test_generate_daily_report_returns_correct_stats(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $service = Service::factory()->create(['category_id' => $category->id]);

        // 3 бронирования за сегодня: completed, cancelled, pending
        $slot1 = TimeSlot::factory()->create(['service_id' => $service->id]);
        $booking1 = Booking::factory()->completed()->create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'time_slot_id' => $slot1->id,
        ]);

        $slot2 = TimeSlot::factory()->create(['service_id' => $service->id]);
        Booking::factory()->cancelled()->create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'time_slot_id' => $slot2->id,
        ]);

        $slot3 = TimeSlot::factory()->create(['service_id' => $service->id]);
        Booking::factory()->create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'time_slot_id' => $slot3->id,
        ]);

        // 1 оплата на 150.50
        Payment::factory()->paid()->create([
            'booking_id' => $booking1->id,
            'user_id' => $user->id,
            'amount' => 150.50,
        ]);

        $report = $this->service->generateDailyReport();

        $this->assertEquals(3, $report['total_bookings']);
        $this->assertEquals(1, $report['completed']);
        $this->assertEquals(1, $report['cancelled']);
        $this->assertEquals(150.50, $report['revenue']);
    }

    public function test_generate_daily_report_ignores_other_dates(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $service = Service::factory()->create(['category_id' => $category->id]);
        $slot = TimeSlot::factory()->create(['service_id' => $service->id]);

        // Бронирование вчерашнее — не должно попасть в отчёт
        Booking::factory()->completed()->create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'time_slot_id' => $slot->id,
            'created_at' => now()->subDay(),
        ]);

        $report = $this->service->generateDailyReport();

        $this->assertEquals(0, $report['total_bookings']);
        $this->assertEquals(0, $report['completed']);
        $this->assertEquals(0, $report['cancelled']);
        $this->assertEquals(0.0, $report['revenue']);
    }
}
