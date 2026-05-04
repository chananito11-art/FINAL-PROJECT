<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BookingRejected extends Notification
{
    use Queueable;

    public function __construct(public Booking $booking) {}

    public function via($notifiable): array { return ['database']; }

    public function toArray($notifiable): array
    {
        return [
            'message'          => "Your booking #{$this->booking->id} was rejected. Reason: {$this->booking->rejection_reason}",
            'booking_id'       => $this->booking->id,
            'rejection_reason' => $this->booking->rejection_reason,
            'type'             => 'booking_rejected',
        ];
    }
}
