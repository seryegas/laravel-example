<?php

declare(strict_types=1);

namespace App\Modules\Payment\Tests\Unit;

use App\Modules\Booking\Enums\BookingStatus;
use App\Modules\Booking\Models\Booking;
use App\Modules\Core\Models\User;
use App\Modules\Payment\Enums\PaymentStatus;
use App\Modules\Payment\Events\PaymentReceived;
use App\Modules\Payment\Models\Payment;
use App\Modules\Payment\Services\PaymentService;
use App\Modules\Service\Models\Category;
use App\Modules\Service\Models\Service;
use App\Modules\Service\Models\TimeSlot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use InvalidArgumentException;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentService = new PaymentService();
    }

    private function createBookingWithService(): Booking
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $service = Service::factory()->create(['category_id' => $category->id, 'price' => '100.00']);
        $slot = TimeSlot::factory()->create(['service_id' => $service->id]);

        return Booking::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'time_slot_id' => $slot->id,
            'status' => BookingStatus::Pending,
        ]);
    }

    public function test_process_payment_creates_paid_payment(): void
    {
        Event::fake([PaymentReceived::class]);

        $booking = $this->createBookingWithService();
        $payment = $this->paymentService->processPayment($booking);

        $this->assertEquals(PaymentStatus::Paid, $payment->status);
        $this->assertEquals($booking->id, $payment->booking_id);
        $this->assertEquals($booking->user_id, $payment->user_id);
        $this->assertEquals('100.00', $payment->amount);
        $this->assertEquals('card', $payment->payment_method);
        $this->assertNotNull($payment->transaction_id);
        $this->assertNotNull($payment->paid_at);
    }

    public function test_process_payment_dispatches_payment_received_event(): void
    {
        Event::fake([PaymentReceived::class]);

        $booking = $this->createBookingWithService();
        $this->paymentService->processPayment($booking);

        Event::assertDispatched(PaymentReceived::class);
    }

    public function test_process_payment_accepts_custom_payment_method(): void
    {
        Event::fake([PaymentReceived::class]);

        $booking = $this->createBookingWithService();
        $payment = $this->paymentService->processPayment($booking, 'cash');

        $this->assertEquals('cash', $payment->payment_method);
    }

    public function test_refund_marks_payment_as_refunded(): void
    {
        $payment = Payment::factory()->paid()->create();

        $refunded = $this->paymentService->refund($payment);

        $this->assertEquals(PaymentStatus::Refunded, $refunded->status);
        $this->assertNotNull($refunded->refunded_at);
    }

    public function test_refund_throws_exception_for_pending_payment(): void
    {
        $payment = Payment::factory()->create();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('This payment cannot be refunded.');

        $this->paymentService->refund($payment);
    }

    public function test_refund_throws_exception_for_already_refunded_payment(): void
    {
        $payment = Payment::factory()->refunded()->create();

        $this->expectException(InvalidArgumentException::class);

        $this->paymentService->refund($payment);
    }

    public function test_list_for_user_returns_only_user_payments(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Payment::factory()->count(3)->create(['user_id' => $user->id]);
        Payment::factory()->count(2)->create(['user_id' => $otherUser->id]);

        $result = $this->paymentService->listForUser($user);

        $this->assertCount(3, $result->items());
    }

    public function test_list_all_returns_all_payments(): void
    {
        Payment::factory()->count(5)->create();

        $result = $this->paymentService->listAll();

        $this->assertCount(5, $result->items());
    }
}
