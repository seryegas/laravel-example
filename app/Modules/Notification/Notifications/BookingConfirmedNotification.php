<?php

declare(strict_types=1);

namespace App\Modules\Notification\Notifications;

use App\Modules\Booking\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmedNotification extends Notification
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
            ->subject('Booking Confirmed')
            ->line("Your booking for {$this->booking->service->name} on {$this->booking->timeSlot->date->format('M d, Y')} has been confirmed.")
            ->line('Thank you for choosing our service!');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'service_name' => $this->booking->service->name,
            'date' => $this->booking->timeSlot->date->toDateString(),
            'status' => $this->booking->status->value,
        ];
    }
}
