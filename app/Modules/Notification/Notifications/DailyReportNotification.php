<?php

declare(strict_types=1);

namespace App\Modules\Notification\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DailyReportNotification extends Notification
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $reportData
     */
    public function __construct(
        public readonly array $reportData,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Daily Bookings Report')
            ->line("Total Bookings: {$this->reportData['total_bookings']}")
            ->line("Completed: {$this->reportData['completed']}")
            ->line("Cancelled: {$this->reportData['cancelled']}")
            ->line("Revenue: \${$this->reportData['revenue']}");
    }
}
