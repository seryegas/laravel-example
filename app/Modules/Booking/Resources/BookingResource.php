<?php

declare(strict_types=1);

namespace App\Modules\Booking\Resources;

use App\Modules\Service\Resources\ServiceResource;
use App\Modules\Service\Resources\SlotResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'service_id' => $this->service_id,
            'time_slot_id' => $this->time_slot_id,
            'status' => $this->status,
            'status_label' => $this->status->label(),
            'notes' => $this->notes,
            'cancelled_at' => $this->cancelled_at,
            'user' => $this->whenLoaded('user'),
            'service' => new ServiceResource($this->whenLoaded('service')),
            'time_slot' => new SlotResource($this->whenLoaded('timeSlot')),
            'payments_count' => $this->whenCounted('payments'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
