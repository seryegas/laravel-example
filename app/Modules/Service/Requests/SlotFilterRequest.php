<?php

declare(strict_types=1);

namespace App\Modules\Service\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SlotFilterRequest extends FormRequest
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
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'status' => ['nullable', 'string', 'in:available,booked,blocked'],
        ];
    }

    /**
     * @return array{date_from: ?string, date_to: ?string, status: ?string}
     */
    public function toDto(): array
    {
        return [
            'date_from' => $this->validated('date_from'),
            'date_to' => $this->validated('date_to'),
            'status' => $this->validated('status'),
        ];
    }
}
