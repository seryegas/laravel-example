<?php

declare(strict_types=1);

namespace App\Modules\Notification\Notifications;

use App\Modules\Booking\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Booking $booking,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Booking Reminder')
            ->line('Your appointment is in 1 hour.')
            ->line("Service: {$this->booking->service->name}")
            ->line("Time: {$this->booking->timeSlot->start_time->format('h:i A')}");
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'service_name' => $this->booking->service->name,
            'time' => $this->booking->timeSlot->start_time->toDateTimeString(),
        ];
    }
}
