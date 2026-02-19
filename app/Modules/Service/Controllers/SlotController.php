<?php

declare(strict_types=1);

namespace App\Modules\Service\Controllers;

use App\Modules\Service\Models\Service;
use App\Modules\Service\Models\TimeSlot;
use App\Modules\Service\Requests\SlotFilterRequest;
use App\Modules\Service\Resources\SlotResource;
use App\Modules\Service\Services\SlotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class SlotController extends Controller
{
    public function __construct(
        private readonly SlotService $slotService,
    ) {}

    public function index(SlotFilterRequest $request, Service $service): AnonymousResourceCollection
    {
        return SlotResource::collection($this->slotService->list($service, $request->toDto()));
    }

    public function store(Request $request, Service $service): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'status' => ['nullable', 'string', 'in:available,booked,blocked'],
        ]);

        $slot = $this->slotService->create($service, $validated);

        return (new SlotResource($slot))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(TimeSlot $slot): JsonResponse
    {
        $this->slotService->delete($slot);

        return response()->json(null, 204);
    }
}
