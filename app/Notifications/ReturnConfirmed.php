<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReturnConfirmed extends Notification
{
    use Queueable;

    public function __construct(public Booking $booking) {}

    public function via($notifiable): array { return ['database']; }

    public function toArray($notifiable): array
    {
        return [
            'message'    => "Your rental for booking #{$this->booking->id} has been completed. Thank you!",
            'booking_id' => $this->booking->id,
            'type'       => 'return_confirmed',
        ];
    }
}
