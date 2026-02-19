<?php

declare(strict_types=1);

namespace App\Modules\Service\Tests\Unit;

use App\Modules\Service\Models\Category;
use App\Modules\Service\Models\Service;
use App\Modules\Service\Models\TimeSlot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_belongs_to_category(): void
    {
        $service = Service::factory()->create();

        $this->assertNotNull($service->category);
        $this->assertInstanceOf(Category::class, $service->category);
    }

    public function test_service_has_many_time_slots(): void
    {
        $service = Service::factory()->create();
        TimeSlot::factory()->count(3)->create(['service_id' => $service->id]);

        $this->assertCount(3, $service->timeSlots);
    }

    public function test_active_scope_filters_inactive(): void
    {
        Service::factory()->count(2)->create(['is_active' => true]);
        Service::factory()->create(['is_active' => false]);

        $this->assertCount(2, Service::active()->get());
    }

    public function test_price_is_cast_to_decimal(): void
    {
        $service = Service::factory()->create(['price' => 99.9]);

        $this->assertEquals('99.90', $service->price);
    }

    public function test_deleting_service_cascades_to_time_slots(): void
    {
        $service = Service::factory()->create();
        TimeSlot::factory()->count(2)->create(['service_id' => $service->id]);

        $service->delete();

        $this->assertDatabaseMissing('time_slots', ['service_id' => $service->id]);
    }
}
