<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IdentityVerificationUpdated extends Notification
{
    use Queueable;

    protected $status;
    protected $notes;

    public function __construct($status, $notes = null)
    {
        $this->status = $status;
        $this->notes = $notes;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $msg = (new MailMessage)
            ->subject('Identity Verification Status Updated')
            ->line('Your identity verification status has been updated to: ' . ucfirst($this->status) . '.');

        if ($this->status === 'verified') {
            $msg->line('You now have instant booking privileges!')
                ->action('Start Booking', url('/vehicles'));
        } else {
            $msg->line('Reason: ' . ($this->notes ?: 'No specific reason provided.'))
                ->line('Please re-upload a clear copy of your document.')
                ->action('Verification Module', url('/customer/verification'));
        }

        return $msg;
    }

    public function toArray($notifiable): array
    {
        return [
            'status' => $this->status,
            'notes'  => $this->notes,
            'message' => 'Identity verification updated to ' . $this->status,
        ];
    }
}
