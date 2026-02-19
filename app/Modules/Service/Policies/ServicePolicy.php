<?php

declare(strict_types=1);

namespace App\Modules\Service\Policies;

use App\Modules\Core\Models\User;

class ServicePolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('manager');
    }

    public function update(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('manager');
    }

    public function delete(User $user): bool
    {
        return $user->hasRole('admin');
    }
}
