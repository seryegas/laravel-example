<?php

declare(strict_types=1);

namespace App\Modules\Booking\DTOs;

use App\Modules\Booking\Requests\StoreBookingRequest;

readonly class BookingData
{
    public function __construct(
        public int $userId,
        public int $serviceId,
        public int $timeSlotId,
        public ?string $notes = null,
    ) {}

    public static function fromRequest(StoreBookingRequest $request): self
    {
        return new self(
            userId: (int) $request->user()->id,
            serviceId: (int) $request->validated('service_id'),
            timeSlotId: (int) $request->validated('time_slot_id'),
            notes: $request->validated('notes'),
        );
    }
}
