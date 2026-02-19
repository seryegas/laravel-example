<?php

declare(strict_types=1);

namespace App\Modules\Payment\Resources;

use App\Modules\Booking\Resources\BookingResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_id' => $this->booking_id,
            'user_id' => $this->user_id,
            'amount' => $this->amount,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'transaction_id' => $this->transaction_id,
            'paid_at' => $this->paid_at,
            'refunded_at' => $this->refunded_at,
            'booking' => new BookingResource($this->whenLoaded('booking')),
            'created_at' => $this->created_at,
        ];
    }
}
