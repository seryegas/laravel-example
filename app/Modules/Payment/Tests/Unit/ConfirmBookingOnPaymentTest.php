<?php

declare(strict_types=1);

namespace App\Modules\Payment\Tests\Unit;

use App\Modules\Booking\Enums\BookingStatus;
use App\Modules\Booking\Models\Booking;
use App\Modules\Core\Models\User;
use App\Modules\Payment\Events\PaymentReceived;
use App\Modules\Payment\Listeners\ConfirmBookingOnPayment;
use App\Modules\Payment\Models\Payment;
use App\Modules\Service\Models\Category;
use App\Modules\Service\Models\Service;
use App\Modules\Service\Models\TimeSlot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConfirmBookingOnPaymentTest extends TestCase
{
    use RefreshDatabase;

    private function createBookingWithStatus(BookingStatus $status): Booking
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $service = Service::factory()->create(['category_id' => $category->id]);
        $slot = TimeSlot::factory()->create(['service_id' => $service->id]);

        return Booking::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'time_slot_id' => $slot->id,
            'status' => $status,
        ]);
    }

    public function test_listener_confirms_pending_booking(): void
    {
        $booking = $this->createBookingWithStatus(BookingStatus::Pending);
        $payment = Payment::factory()->paid()->create(['booking_id' => $booking->id]);

        $listener = new ConfirmBookingOnPayment();
        $listener->handle(new PaymentReceived($payment));

        $booking->refresh();
        $this->assertEquals(BookingStatus::Confirmed, $booking->status);
    }

    public function test_listener_does_not_change_non_pending_booking(): void
    {
        $booking = $this->createBookingWithStatus(BookingStatus::Confirmed);
        $payment = Payment::factory()->paid()->create(['booking_id' => $booking->id]);

        $listener = new ConfirmBookingOnPayment();
        $listener->handle(new PaymentReceived($payment));

        $booking->refresh();
        $this->assertEquals(BookingStatus::Confirmed, $booking->status);
    }
}
