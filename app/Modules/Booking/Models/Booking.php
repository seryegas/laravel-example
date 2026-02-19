<?php

declare(strict_types=1);

namespace App\Modules\Booking\Models;

use App\Modules\Booking\Database\Factories\BookingFactory;
use App\Modules\Booking\Enums\BookingStatus;
use App\Modules\Core\Models\User;
use App\Modules\Core\Traits\HasActivityLog;
use App\Modules\Payment\Models\Payment;
use App\Modules\Service\Models\Service;
use App\Modules\Service\Models\TimeSlot;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property int $service_id
 * @property int $time_slot_id
 * @property BookingStatus $status
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $cancelled_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read User $user
 * @property-read Service $service
 * @property-read TimeSlot $timeSlot
 * @property-read Collection<int, Payment> $payments
 */
class Booking extends Model
{
    use HasFactory;
    use HasActivityLog;

    protected $fillable = [
        'user_id',
        'service_id',
        'time_slot_id',
        'status',
        'notes',
        'cancelled_at',
    ];

    protected $casts = [
        'status' => BookingStatus::class,
        'cancelled_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    protected static function newFactory(): BookingFactory
    {
        return BookingFactory::new();
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', BookingStatus::Pending);
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', BookingStatus::Confirmed);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereHas('timeSlot', function (Builder $q): void {
            $q->where('date', '>=', now()->toDateString());
        });
    }

    public function isPending(): bool
    {
        return $this->status === BookingStatus::Pending;
    }

    public function isConfirmed(): bool
    {
        return $this->status === BookingStatus::Confirmed;
    }

    public function canBeCancelled(): bool
    {
        return $this->isPending() || $this->isConfirmed();
    }
}
