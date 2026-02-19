<?php

declare(strict_types=1);

namespace App\Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * Class ActivityLog
 *
 * @property int $id
 * @property string $log_type
 * @property string $description
 * @property array<string, mixed>|null $properties
 *
 * @property string|null $subject_type
 * @property int|null $subject_id
 * @property string|null $causer_type
 * @property int|null $causer_id
 *
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 **/

class ActivityLog extends Model
{
    protected $fillable = [
        'log_type',
        'description',
        'properties',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function causer(): MorphTo
    {
        return $this->morphTo();
    }
}
