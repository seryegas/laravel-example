<?php

declare(strict_types=1);

namespace App\Modules\Service\Models;

use App\Modules\Service\Database\Factories\TimeSlotFactory;
use App\Modules\Service\Enums\SlotStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class TimeSlot
 *
 * @property int $id
 * @property int $service_id
 * @property Carbon $date
 * @property Carbon $start_time
 * @property Carbon $end_time
 * @property SlotStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Service $service
 */
class TimeSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'date',
        'start_time',
        'end_time',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'status' => SlotStatus::class,
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    protected static function newFactory(): TimeSlotFactory
    {
        return TimeSlotFactory::new();
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', SlotStatus::Available);
    }

    public function scopeForDate(Builder $query, string $date): Builder
    {
        return $query->where('date', $date);
    }
}
