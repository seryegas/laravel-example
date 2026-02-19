<?php

declare(strict_types=1);

namespace App\Modules\Service\Controllers;

use App\Modules\Service\Models\Service;
use App\Modules\Service\Requests\StoreServiceRequest;
use App\Modules\Service\Resources\ServiceResource;
use App\Modules\Service\Services\ServiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class ServiceController extends Controller
{
    public function __construct(
        private readonly ServiceService $serviceService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $categoryId = $request->query('category_id') ? (int) $request->query('category_id') : null;

        return ServiceResource::collection($this->serviceService->list($categoryId));
    }

    public function show(Service $service): ServiceResource
    {
        return new ServiceResource($service);
    }

    public function store(StoreServiceRequest $request): JsonResponse
    {
        $service = $this->serviceService->create($request->validated());

        return (new ServiceResource($service))
            ->response()
            ->setStatusCode(201);
    }

    public function update(StoreServiceRequest $request, Service $service): ServiceResource
    {
        $service = $this->serviceService->update($service, $request->validated());

        return new ServiceResource($service);
    }

    public function destroy(Service $service): JsonResponse
    {
        $this->serviceService->delete($service);

        return response()->json(null, 204);
    }
}
