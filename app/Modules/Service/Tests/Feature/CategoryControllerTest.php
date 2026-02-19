<?php

declare(strict_types=1);

namespace App\Modules\Service\Tests\Feature;

use App\Modules\Service\Models\Category;
use App\Modules\Service\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_anyone_can_list_categories(): void
    {
        Category::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/categories');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_list_excludes_inactive_categories(): void
    {
        Category::factory()->count(2)->create(['is_active' => true]);
        Category::factory()->create(['is_active' => false]);

        $response = $this->getJson('/api/v1/categories');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_anyone_can_view_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->getJson("/api/v1/categories/{$category->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $category->id)
            ->assertJsonPath('data.name', $category->name);
    }

    public function test_show_includes_services(): void
    {
        $category = Category::factory()->create();
        Service::factory()->count(2)->create(['category_id' => $category->id]);

        $response = $this->getJson("/api/v1/categories/{$category->id}");

        $response->assertOk()
            ->assertJsonCount(2, 'data.services');
    }
}
