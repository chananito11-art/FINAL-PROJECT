<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BookingCancelledByAdmin extends Notification
{
    use Queueable;

    public function __construct(
        public Booking $booking,
        public string  $reason
    ) {}

    public function via($notifiable): array { return ['database']; }

    public function toArray($notifiable): array
    {
        return [
            'message'    => "Your booking #{$this->booking->id} for {$this->booking->vehicle->name} has been cancelled by admin. Reason: {$this->reason}",
            'booking_id' => $this->booking->id,
            'reason'     => $this->reason,
            'type'       => 'booking_cancelled_by_admin',
        ];
    }
}
