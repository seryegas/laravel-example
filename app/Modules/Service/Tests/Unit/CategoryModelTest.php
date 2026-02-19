<?php

declare(strict_types=1);

namespace App\Modules\Service\Tests\Unit;

use App\Modules\Service\Models\Category;
use App\Modules\Service\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_has_many_services(): void
    {
        $category = Category::factory()->create();
        Service::factory()->count(3)->create(['category_id' => $category->id]);

        $this->assertCount(3, $category->services);
    }

    public function test_active_scope_filters_inactive(): void
    {
        Category::factory()->count(2)->create(['is_active' => true]);
        Category::factory()->create(['is_active' => false]);

        $this->assertCount(2, Category::active()->get());
    }

    public function test_is_active_is_cast_to_boolean(): void
    {
        $category = Category::factory()->create(['is_active' => true]);

        $this->assertIsBool($category->is_active);
    }

    public function test_deleting_category_cascades_to_services(): void
    {
        $category = Category::factory()->create();
        Service::factory()->count(2)->create(['category_id' => $category->id]);

        $category->delete();

        $this->assertDatabaseMissing('services', ['category_id' => $category->id]);
    }
}
