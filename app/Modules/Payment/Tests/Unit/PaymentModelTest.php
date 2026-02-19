<?php

declare(strict_types=1);

namespace App\Modules\Payment\Tests\Unit;

use App\Modules\Payment\Enums\PaymentStatus;
use App\Modules\Payment\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_paid_returns_true_for_paid_status(): void
    {
        $payment = Payment::factory()->paid()->create();

        $this->assertTrue($payment->isPaid());
    }

    public function test_is_paid_returns_false_for_pending_status(): void
    {
        $payment = Payment::factory()->create();

        $this->assertFalse($payment->isPaid());
    }

    public function test_can_be_refunded_returns_true_for_paid_payment(): void
    {
        $payment = Payment::factory()->paid()->create();

        $this->assertTrue($payment->canBeRefunded());
    }

    public function test_can_be_refunded_returns_false_for_pending_payment(): void
    {
        $payment = Payment::factory()->create();

        $this->assertFalse($payment->canBeRefunded());
    }

    public function test_can_be_refunded_returns_false_for_refunded_payment(): void
    {
        $payment = Payment::factory()->refunded()->create();

        $this->assertFalse($payment->canBeRefunded());
    }

    public function test_payment_belongs_to_booking(): void
    {
        $payment = Payment::factory()->create();

        $this->assertNotNull($payment->booking);
    }

    public function test_payment_belongs_to_user(): void
    {
        $payment = Payment::factory()->create();

        $this->assertNotNull($payment->user);
    }

    public function test_status_is_cast_to_enum(): void
    {
        $payment = Payment::factory()->create();

        $this->assertInstanceOf(PaymentStatus::class, $payment->status);
    }

    public function test_paid_at_is_cast_to_datetime(): void
    {
        $payment = Payment::factory()->paid()->create();

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $payment->paid_at);
    }
}
