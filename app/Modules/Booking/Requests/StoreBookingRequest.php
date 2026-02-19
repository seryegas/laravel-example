<?php

declare(strict_types=1);

namespace App\Modules\Booking\Requests;

use App\Modules\Service\Enums\SlotStatus;
use App\Modules\Service\Models\TimeSlot;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'service_id' => ['required', 'exists:services,id'],
            'time_slot_id' => ['required', 'exists:time_slots,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $timeSlot = TimeSlot::find($this->validated('time_slot_id'));

                if (! $timeSlot) {
                    return;
                }

                if ((int) $timeSlot->service_id !== (int) $this->validated('service_id')) {
                    $validator->errors()->add(
                        'time_slot_id',
                        'The selected time slot does not belong to the specified service.',
                    );
                }

                if ($timeSlot->status !== SlotStatus::Available) {
                    $validator->errors()->add(
                        'time_slot_id',
                        'The selected time slot is not available.',
                    );
                }
            },
        ];
    }
}
