<?php

declare(strict_types=1);

namespace App\Modules\Booking\Controllers;

use App\Modules\Booking\DTOs\BookingData;
use App\Modules\Booking\Models\Booking;
use App\Modules\Booking\Requests\StoreBookingRequest;
use App\Modules\Booking\Resources\BookingResource;
use App\Modules\Booking\Services\BookingService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class BookingController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly BookingService $bookingService,
    ) {}

    /**
     * @throws AuthorizationException
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Booking::class);

        $user = $request->user();

        $bookings = ($user->isAdmin() || $user->isManager())
            ? $this->bookingService->listAll()
            : $this->bookingService->listForUser($user);

        return BookingResource::collection($bookings);
    }

    public function store(StoreBookingRequest $request): JsonResponse
    {
        $this->authorize('create', Booking::class);

        $booking = $this->bookingService->create(BookingData::fromRequest($request));

        return (new BookingResource($booking->load(['service', 'timeSlot'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Booking $booking): BookingResource
    {
        $this->authorize('view', $booking);

        return new BookingResource($booking->load(['user', 'service', 'timeSlot']));
    }

    public function cancel(Booking $booking): BookingResource
    {
        $this->authorize('cancel', $booking);

        $booking = $this->bookingService->cancel($booking);

        return new BookingResource($booking->load(['service', 'timeSlot']));
    }

    public function complete(Booking $booking): BookingResource
    {
        $this->authorize('complete', $booking);

        $booking = $this->bookingService->complete($booking);

        return new BookingResource($booking->load(['service', 'timeSlot']));
    }
}
