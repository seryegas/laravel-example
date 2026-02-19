<?php

declare(strict_types=1);

namespace App\Modules\Service\Tests\Unit;

use App\Modules\Service\Models\Category;
use App\Modules\Service\Models\Service;
use App\Modules\Service\Services\ServiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceServiceTest extends TestCase
{
    use RefreshDatabase;

    private ServiceService $serviceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->serviceService = new ServiceService();
    }

    public function test_list_returns_only_active_services(): void
    {
        $category = Category::factory()->create();
        Service::factory()->count(3)->create(['category_id' => $category->id, 'is_active' => true]);
        Service::factory()->create(['category_id' => $category->id, 'is_active' => false]);

        $result = $this->serviceService->list();

        $this->assertCount(3, $result->items());
    }

    public function test_list_filters_by_category_id(): void
    {
        $cat1 = Category::factory()->create();
        $cat2 = Category::factory()->create();

        Service::factory()->count(2)->create(['category_id' => $cat1->id]);
        Service::factory()->count(3)->create(['category_id' => $cat2->id]);

        $result = $this->serviceService->list($cat1->id);

        $this->assertCount(2, $result->items());
    }

    public function test_list_without_category_returns_all_active(): void
    {
        $cat1 = Category::factory()->create();
        $cat2 = Category::factory()->create();

        Service::factory()->count(2)->create(['category_id' => $cat1->id]);
        Service::factory()->count(3)->create(['category_id' => $cat2->id]);

        $result = $this->serviceService->list();

        $this->assertCount(5, $result->items());
    }

    public function test_create_persists_service(): void
    {
        $category = Category::factory()->create();

        $data = [
            'category_id' => $category->id,
            'name' => 'Test Service',
            'slug' => 'test-service',
            'description' => 'Description',
            'duration_minutes' => 60,
            'price' => 99.99,
            'is_active' => true,
        ];

        $service = $this->serviceService->create($data);

        $this->assertDatabaseHas('services', ['id' => $service->id, 'name' => 'Test Service']);
    }

    public function test_update_modifies_service(): void
    {
        $service = Service::factory()->create(['name' => 'Old Name']);

        $updated = $this->serviceService->update($service, ['name' => 'New Name']);

        $this->assertEquals('New Name', $updated->name);
        $this->assertDatabaseHas('services', ['id' => $service->id, 'name' => 'New Name']);
    }

    public function test_delete_removes_service(): void
    {
        $service = Service::factory()->create();

        $this->serviceService->delete($service);

        $this->assertDatabaseMissing('services', ['id' => $service->id]);
    }
}
