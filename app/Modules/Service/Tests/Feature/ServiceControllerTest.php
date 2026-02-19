<?php

declare(strict_types=1);

namespace App\Modules\Service\Tests\Feature;

use App\Modules\Core\Enums\UserRole;
use App\Modules\Core\Models\User;
use App\Modules\Service\Models\Category;
use App\Modules\Service\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceControllerTest extends TestCase
{
    use RefreshDatabase;

    // --- Index (public) ---

    public function test_anyone_can_list_services(): void
    {
        Service::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/services');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_list_filters_by_category_id(): void
    {
        $cat1 = Category::factory()->create();
        $cat2 = Category::factory()->create();

        Service::factory()->count(2)->create(['category_id' => $cat1->id]);
        Service::factory()->count(3)->create(['category_id' => $cat2->id]);

        $response = $this->getJson("/api/v1/services?category_id={$cat1->id}");

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_list_excludes_inactive_services(): void
    {
        Service::factory()->count(2)->create(['is_active' => true]);
        Service::factory()->create(['is_active' => false]);

        $response = $this->getJson('/api/v1/services');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    // --- Show (public) ---

    public function test_anyone_can_view_service(): void
    {
        $service = Service::factory()->create();

        $response = $this->getJson("/api/v1/services/{$service->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $service->id)
            ->assertJsonPath('data.name', $service->name);
    }

    // --- Store (admin/manager) ---

    public function test_admin_can_create_service(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $category = Category::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/services', [
                'category_id' => $category->id,
                'name' => 'New Service',
                'slug' => 'new-service',
                'duration_minutes' => 60,
                'price' => 100,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'New Service');
    }

    public function test_manager_can_create_service(): void
    {
        $manager = User::factory()->create(['role' => UserRole::Manager]);
        $category = Category::factory()->create();

        $response = $this->actingAs($manager, 'sanctum')
            ->postJson('/api/v1/services', [
                'category_id' => $category->id,
                'name' => 'Manager Service',
                'slug' => 'manager-service',
                'duration_minutes' => 30,
                'price' => 50,
            ]);

        $response->assertStatus(201);
    }

    public function test_client_cannot_create_service(): void
    {
        $client = User::factory()->create(['role' => UserRole::Client]);
        $category = Category::factory()->create();

        $response = $this->actingAs($client, 'sanctum')
            ->postJson('/api/v1/services', [
                'category_id' => $category->id,
                'name' => 'Unauthorized',
                'slug' => 'unauthorized',
                'duration_minutes' => 60,
                'price' => 100,
            ]);

        $response->assertForbidden();
    }

    public function test_guest_cannot_create_service(): void
    {
        $this->postJson('/api/v1/services', [])->assertUnauthorized();
    }

    public function test_store_validates_required_fields(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/services', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['category_id', 'name', 'duration_minutes', 'price']);
    }

    // --- Update (admin/manager) ---

    public function test_admin_can_update_service(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $service = Service::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($admin, 'sanctum')
            ->putJson("/api/v1/services/{$service->id}", [
                'category_id' => $service->category_id,
                'name' => 'Updated Name',
                'duration_minutes' => $service->duration_minutes,
                'price' => $service->price,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Name');
    }

    public function test_client_cannot_update_service(): void
    {
        $client = User::factory()->create(['role' => UserRole::Client]);
        $service = Service::factory()->create();

        $response = $this->actingAs($client, 'sanctum')
            ->putJson("/api/v1/services/{$service->id}", [
                'category_id' => $service->category_id,
                'name' => 'Hacked',
                'duration_minutes' => 60,
                'price' => 100,
            ]);

        $response->assertForbidden();
    }

    // --- Destroy (admin/manager) ---

    public function test_admin_can_delete_service(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $service = Service::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/services/{$service->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('services', ['id' => $service->id]);
    }

    public function test_client_cannot_delete_service(): void
    {
        $client = User::factory()->create(['role' => UserRole::Client]);
        $service = Service::factory()->create();

        $response = $this->actingAs($client, 'sanctum')
            ->deleteJson("/api/v1/services/{$service->id}");

        $response->assertForbidden();
    }
}
