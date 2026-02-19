<?php

declare(strict_types=1);

namespace App\Modules\Notification\Tests\Unit;

use App\Modules\Booking\Events\BookingCreated;
use App\Modules\Booking\Models\Booking;
use App\Modules\Core\Models\User;
use App\Modules\Notification\Listeners\NotifyProviderAboutBooking;
use App\Modules\Notification\Listeners\SendBookingConfirmation;
use App\Modules\Notification\Notifications\BookingConfirmedNotification;
use App\Modules\Payment\Enums\PaymentStatus;
use App\Modules\Payment\Events\PaymentReceived;
use App\Modules\Payment\Models\Payment;
use App\Modules\Service\Models\Category;
use App\Modules\Service\Models\Service;
use App\Modules\Service\Models\TimeSlot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ListenersTest extends TestCase
{
    use RefreshDatabase;

    private function createBookingWithPayment(): array
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

        $payment = Payment::factory()->paid()->create([
            'booking_id' => $booking->id,
            'user_id' => $user->id,
        ]);

        return [$user, $booking, $payment];
    }

    public function test_send_booking_confirmation_sends_notification(): void
    {
        Notification::fake();

        [$user, $booking, $payment] = $this->createBookingWithPayment();

        $listener = new SendBookingConfirmation();
        $listener->handle(new PaymentReceived($payment));

        Notification::assertSentTo($user, BookingConfirmedNotification::class);
    }

    public function test_notify_provider_about_booking_logs_info(): void
    {
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function (string $message, array $context) {
                return str_contains($message, 'notified about new booking')
                    && isset($context['booking_id'], $context['service_id'], $context['user_id']);
            });

        $user = User::factory()->create();
        $category = Category::factory()->create();
        $service = Service::factory()->create(['category_id' => $category->id]);
        $slot = TimeSlot::factory()->create(['service_id' => $service->id]);

        $booking = Booking::factory()->create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'time_slot_id' => $slot->id,
        ]);

        $listener = new NotifyProviderAboutBooking();
        $listener->handle(new BookingCreated($booking));
    }

    public function test_events_are_wired_to_listeners(): void
    {
        $dispatcher = app('events');

        $this->assertTrue(
            $dispatcher->hasListeners(PaymentReceived::class),
            'PaymentReceived event has no listeners.',
        );

        $this->assertTrue(
            $dispatcher->hasListeners(BookingCreated::class),
            'BookingCreated event has no listeners.',
        );
    }
}
