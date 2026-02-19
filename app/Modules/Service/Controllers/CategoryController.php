<?php

declare(strict_types=1);

namespace App\Modules\Service\Controllers;

use App\Modules\Service\Models\Category;
use App\Modules\Service\Resources\CategoryResource;
use App\Modules\Service\Services\CategoryService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryService $categoryService,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        return CategoryResource::collection($this->categoryService->list());
    }

    public function show(Category $category): CategoryResource
    {
        return new CategoryResource($this->categoryService->show($category));
    }
}
