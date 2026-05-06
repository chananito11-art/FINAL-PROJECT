<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class BookingHoldExpired extends Notification
{
    use Queueable;

    public function __construct(public Booking $booking) {}

    public function via($notifiable): array { return ['database']; }

    public function toArray($notifiable): array
    {
        return [
            'message'    => "Your reservation hold for {$this->booking->vehicle->name} has expired. The booking has been automatically cancelled. Please book again if you're still interested.",
            'booking_id' => $this->booking->id,
            'vehicle_id' => $this->booking->vehicle_id,
            'type'       => 'hold_expired',
        ];
    }
}
