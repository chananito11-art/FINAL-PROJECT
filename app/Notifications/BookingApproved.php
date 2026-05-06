<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingApproved extends Notification
{
    use Queueable;

    public $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Booking Approved - Payment Required')
            ->greeting('Hello ' . $this->booking->first_name . '!')
            ->line('Great news! Your booking for ' . $this->booking->vehicle->name . ' has been approved by our admin.')
            ->line('To secure your reservation, please complete the GCash payment within 1 hour.')
            ->action('Pay Now', route('customer.payment.show', $this->booking))
            ->line('If payment is not received within the time limit, your reservation will be automatically cancelled.')
            ->line('Thank you for choosing OrangeCrush!');
    }

    public function toArray($notifiable)
    {
        return [
            'booking_id' => $this->booking->id,
            'message'    => 'Your booking #' . $this->booking->id . ' was approved. Please pay within 1 hour.',
            'action_url' => route('customer.payment.show', $this->booking),
        ];
    }
}
