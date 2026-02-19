<?php

declare(strict_types=1);

namespace App\Modules\Service\Tests\Unit;

use App\Modules\Service\Models\Category;
use App\Modules\Service\Models\Service;
use App\Modules\Service\Services\CategoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private CategoryService $categoryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->categoryService = new CategoryService();
    }

    public function test_list_returns_only_active_categories(): void
    {
        Category::factory()->count(3)->create(['is_active' => true]);
        Category::factory()->create(['is_active' => false]);

        $result = $this->categoryService->list();

        $this->assertCount(3, $result->items());
    }

    public function test_list_includes_services_count(): void
    {
        $category = Category::factory()->create();
        Service::factory()->count(2)->create(['category_id' => $category->id]);

        $result = $this->categoryService->list();
        $item = $result->items()[0];

        $this->assertEquals(2, $item->services_count);
    }

    public function test_show_loads_services_relation(): void
    {
        $category = Category::factory()->create();
        Service::factory()->count(3)->create(['category_id' => $category->id]);

        $result = $this->categoryService->show($category);

        $this->assertTrue($result->relationLoaded('services'));
        $this->assertCount(3, $result->services);
    }
}
