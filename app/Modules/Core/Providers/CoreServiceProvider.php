<?php

declare(strict_types=1);

namespace App\Modules\Core\Providers;

use App\Modules\Core\Http\Middleware\EnsureUserHasRole;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        $this->app['router']->aliasMiddleware('role', EnsureUserHasRole::class);
    }
}
