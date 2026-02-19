<?php

declare(strict_types=1);

namespace App\Modules\Notification\Controllers;

use App\Modules\Notification\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->notificationService->list($request->user()));
    }

    public function markAsRead(Request $request, string $notificationId): JsonResponse
    {
        $this->notificationService->markAsRead($request->user(), $notificationId);

        return response()->json(['message' => 'Notification marked as read.']);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $this->notificationService->markAllAsRead($request->user());

        return response()->json(['message' => 'All notifications marked as read.']);
    }
}
