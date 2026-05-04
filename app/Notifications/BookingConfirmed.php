<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class BookingConfirmed extends Notification
{
    use Queueable;

    public function __construct(public Booking $booking) {}

    public function via($notifiable): array { return ['database']; }

    public function toArray($notifiable): array
    {
        return [
            'message'    => "Your booking #{$this->booking->id} for {$this->booking->vehicle->name} has been confirmed!",
            'booking_id' => $this->booking->id,
            'type'       => 'booking_confirmed',
        ];
    }
}
