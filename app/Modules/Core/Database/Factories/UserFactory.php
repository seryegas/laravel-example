<?php

declare(strict_types=1);

namespace App\Modules\Core\Database\Factories;

use App\Modules\Core\Enums\UserRole;
use App\Modules\Core\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'role' => UserRole::Client,
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes): array => [
            'role' => UserRole::Admin,
        ]);
    }

    public function manager(): static
    {
        return $this->state(fn (array $attributes): array => [
            'role' => UserRole::Manager,
        ]);
    }

    public function client(): static
    {
        return $this->state(fn (array $attributes): array => [
            'role' => UserRole::Client,
        ]);
    }
}
