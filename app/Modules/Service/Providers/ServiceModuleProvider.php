<?php

declare(strict_types=1);

namespace App\Modules\Service\Providers;

use App\Modules\Service\Models\Service;
use App\Modules\Service\Policies\ServicePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ServiceModuleProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutes();
        $this->loadMigrations();
        $this->registerPolicies();
    }

    private function loadRoutes(): void
    {
        Route::prefix('api/v1')
            ->middleware('api')
            ->group(__DIR__ . '/../Routes/api.php');
    }

    private function loadMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }

    private function registerPolicies(): void
    {
        Gate::policy(Service::class, ServicePolicy::class);
    }
}
