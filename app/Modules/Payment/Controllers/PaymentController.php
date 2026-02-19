<?php

declare(strict_types=1);

namespace App\Modules\Payment\Controllers;

use App\Modules\Booking\Enums\BookingStatus;
use App\Modules\Booking\Models\Booking;
use App\Modules\Payment\Models\Payment;
use App\Modules\Payment\Resources\PaymentResource;
use App\Modules\Payment\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $payments = $user->isAdmin()
            ? $this->paymentService->listAll()
            : $this->paymentService->listForUser($user);

        return PaymentResource::collection($payments);
    }

    public function store(Request $request, Booking $booking): JsonResponse
    {
        $user = $request->user();

        if ($booking->user_id !== $user->id) {
            abort(403, 'This booking does not belong to you.');
        }

        if ($booking->status !== BookingStatus::Pending) {
            abort(422, 'Only pending bookings can be paid.');
        }

        $payment = $this->paymentService->processPayment($booking, $request->input('payment_method', 'card'));

        return (new PaymentResource($payment))
            ->response()
            ->setStatusCode(201);
    }

    public function refund(Payment $payment): JsonResponse
    {
        $payment = $this->paymentService->refund($payment);

        return (new PaymentResource($payment))
            ->response()
            ->setStatusCode(200);
    }
}
