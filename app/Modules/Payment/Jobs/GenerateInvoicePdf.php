<?php

declare(strict_types=1);

namespace App\Modules\Payment\Jobs;

use App\Modules\Payment\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateInvoicePdf implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Payment $payment,
    ) {}

    public function handle(): void
    {
        Log::info('Generating invoice PDF for payment.', [
            'payment_id' => $this->payment->id,
            'booking_id' => $this->payment->booking_id,
            'user_id' => $this->payment->user_id,
            'amount' => $this->payment->amount,
            'transaction_id' => $this->payment->transaction_id,
        ]);
    }
}
