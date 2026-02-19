<?php

declare(strict_types=1);

namespace App\Modules\Service\Services;

use App\Modules\Service\Models\Service;
use App\Modules\Service\Models\TimeSlot;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SlotService
{
    public function list(Service $service, array $filters): LengthAwarePaginator
    {
        return $service->timeSlots()
            ->available()
            ->when(
                $filters['date_from'] ?? null,
                fn ($query, $dateFrom) => $query->where('date', '>=', $dateFrom),
            )
            ->when(
                $filters['date_to'] ?? null,
                fn ($query, $dateTo) => $query->where('date', '<=', $dateTo),
            )
            ->orderBy('date')
            ->orderBy('start_time')
            ->paginate(30);
    }

    public function create(Service $service, array $data): TimeSlot
    {
        return $service->timeSlots()->create([
            'date' => $data['date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'status' => $data['status'] ?? 'available',
        ]);
    }

    public function delete(TimeSlot $slot): void
    {
        $slot->delete();
    }
}
