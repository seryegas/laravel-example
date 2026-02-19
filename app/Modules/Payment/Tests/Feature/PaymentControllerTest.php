<?php

declare(strict_types=1);

namespace App\Modules\Payment\Tests\Feature;

use App\Modules\Booking\Enums\BookingStatus;
use App\Modules\Booking\Models\Booking;
use App\Modules\Core\Enums\UserRole;
use App\Modules\Core\Models\User;
use App\Modules\Payment\Enums\PaymentStatus;
use App\Modules\Payment\Models\Payment;
use App\Modules\Service\Models\Category;
use App\Modules\Service\Models\Service;
use App\Modules\Service\Models\TimeSlot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createBooking(User $user, BookingStatus $status = BookingStatus::Pending): Booking
    {
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

    // --- Index ---

    public function test_guest_cannot_list_payments(): void
    {
        $this->getJson('/api/v1/payments')->assertUnauthorized();
    }

    public function test_client_sees_only_own_payments(): void
    {
        $user = User::factory()->create(['role' => UserRole::Client]);
        $otherUser = User::factory()->create(['role' => UserRole::Client]);

        Payment::factory()->count(2)->create(['user_id' => $user->id]);
        Payment::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/payments');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_admin_sees_all_payments(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        Payment::factory()->count(3)->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/payments');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    // --- Store (Pay) ---

    public function test_client_can_pay_for_own_pending_booking(): void
    {
        $user = User::factory()->create(['role' => UserRole::Client]);
        $booking = $this->createBooking($user);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/bookings/{$booking->id}/pay", [
                'payment_method' => 'card',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', PaymentStatus::Paid->value)
            ->assertJsonPath('data.booking_id', $booking->id);
    }

    public function test_client_cannot_pay_for_others_booking(): void
    {
        $owner = User::factory()->create(['role' => UserRole::Client]);
        $other = User::factory()->create(['role' => UserRole::Client]);
        $booking = $this->createBooking($owner);

        $response = $this->actingAs($other, 'sanctum')
            ->postJson("/api/v1/bookings/{$booking->id}/pay");

        $response->assertForbidden();
    }

    public function test_cannot_pay_for_non_pending_booking(): void
    {
        $user = User::factory()->create(['role' => UserRole::Client]);
        $booking = $this->createBooking($user, BookingStatus::Confirmed);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/bookings/{$booking->id}/pay");

        $response->assertStatus(422);
    }

    public function test_guest_cannot_pay(): void
    {
        $this->postJson('/api/v1/bookings/1/pay')->assertUnauthorized();
    }

    // --- Refund ---

    public function test_admin_can_refund_paid_payment(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $payment = Payment::factory()->paid()->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/payments/{$payment->id}/refund");

        $response->assertOk()
            ->assertJsonPath('data.status', PaymentStatus::Refunded->value);
    }

    public function test_client_cannot_refund(): void
    {
        $user = User::factory()->create(['role' => UserRole::Client]);
        $payment = Payment::factory()->paid()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/payments/{$payment->id}/refund");

        $response->assertForbidden();
    }

    public function test_guest_cannot_refund(): void
    {
        $this->postJson('/api/v1/payments/1/refund')->assertUnauthorized();
    }
}
