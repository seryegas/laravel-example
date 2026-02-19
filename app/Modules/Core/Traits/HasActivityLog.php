<?php

declare(strict_types=1);

namespace App\Modules\Core\Traits;

use App\Modules\Core\Models\ActivityLog;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasActivityLog
{
    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }

    public function logActivity(string $type, ?string $description = null, ?array $properties = null): ActivityLog
    {
        return $this->activityLogs()->create([
            'log_type' => $type,
            'description' => $description,
            'properties' => $properties,
            'causer_type' => auth()->check() ? get_class(auth()->user()) : null,
            'causer_id' => auth()->id(),
        ]);
    }
}
