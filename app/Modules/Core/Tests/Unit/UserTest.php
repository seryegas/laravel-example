<?php

declare(strict_types=1);

namespace App\Modules\Core\Tests\Unit;

use App\Modules\Core\Enums\UserRole;
use App\Modules\Core\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_default_client_role(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($user->isClient());
    }

    public function test_admin_role_is_detected(): void
    {
        $user = User::factory()->admin()->create();

        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isManager());
        $this->assertFalse($user->isClient());
    }

    public function test_manager_role_is_detected(): void
    {
        $user = User::factory()->manager()->create();

        $this->assertTrue($user->isManager());
        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->isClient());
    }

    public function test_user_can_log_activity(): void
    {
        $user = User::factory()->create();

        $log = $user->logActivity('test', 'Test description', ['key' => 'value']);

        $this->assertDatabaseHas('activity_logs', [
            'log_type' => 'test',
            'description' => 'Test description',
            'subject_type' => User::class,
            'subject_id' => $user->id,
        ]);

        $this->assertEquals(['key' => 'value'], $log->properties);
    }
}
