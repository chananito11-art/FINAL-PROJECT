<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BookingCancelled extends Notification
{
    use Queueable;

    public function __construct(public Booking $booking) {}

    public function via($notifiable): array { return ['database']; }

    public function toArray($notifiable): array
    {
        return [
            'message'    => "You have cancelled your reservation (Booking #{$this->booking->id}) for {$this->booking->vehicle->name}. The vehicle is now available for others.",
            'booking_id' => $this->booking->id,
            'type'       => 'booking_cancelled',
        ];
    }
}
