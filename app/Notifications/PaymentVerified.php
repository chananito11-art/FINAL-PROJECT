<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentVerified extends Notification
{
    use Queueable;

    public function __construct(public Booking $booking) {}

    public function via($notifiable): array { return ['database']; }

    public function toArray($notifiable): array
    {
        return [
            'message'    => "Your GCash payment for booking #{$this->booking->id} has been verified!",
            'booking_id' => $this->booking->id,
            'type'       => 'payment_verified',
        ];
    }
}
