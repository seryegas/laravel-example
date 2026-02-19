<?php

declare(strict_types=1);

namespace App\Modules\Service\Services;

use App\Modules\Service\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CategoryService
{
    public function list(): LengthAwarePaginator
    {
        return Category::query()
            ->active()
            ->withCount('services')
            ->paginate(15);
    }

    public function show(Category $category): Category
    {
        return $category->load('services');
    }
}
