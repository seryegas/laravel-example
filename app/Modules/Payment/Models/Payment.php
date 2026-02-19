<?php

declare(strict_types=1);

namespace App\Modules\Payment\Models;

use App\Modules\Booking\Models\Booking;
use App\Modules\Core\Models\User;
use App\Modules\Payment\Database\Factories\PaymentFactory;
use App\Modules\Payment\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $booking_id
 * @property int $user_id
 * @property string $amount
 * @property PaymentStatus $status
 * @property string $payment_method
 * @property string|null $transaction_id
 * @property Carbon|null $paid_at
 * @property Carbon|null $refunded_at
 *
 * @property-read Booking $booking
 * @property-read User $user
 */
class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'user_id',
        'amount',
        'status',
        'payment_method',
        'transaction_id',
        'paid_at',
        'refunded_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'status' => PaymentStatus::class,
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function newFactory(): PaymentFactory
    {
        return PaymentFactory::new();
    }

    public function isPaid(): bool
    {
        return $this->status === PaymentStatus::Paid;
    }

    public function canBeRefunded(): bool
    {
        return $this->isPaid() && $this->status !== PaymentStatus::Refunded;
    }
}
