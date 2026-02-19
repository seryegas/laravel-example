<?php

declare(strict_types=1);

namespace App\Modules\Service\Services;

use App\Modules\Service\Models\Service;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ServiceService
{
    public function list(?int $categoryId = null): LengthAwarePaginator
    {
        return Service::query()
            ->active()
            ->when(
                $categoryId,
                fn ($query, $id) => $query->where('category_id', $id),
            )
            ->paginate(15);
    }

    public function create(array $data): Service
    {
        return Service::create($data);
    }

    public function update(Service $service, array $data): Service
    {
        $service->update($data);

        return $service;
    }

    public function delete(Service $service): void
    {
        $service->delete();
    }
}
