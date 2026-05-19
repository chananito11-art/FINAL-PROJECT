<?php

namespace App\Notifications;

use App\Models\MaintenanceLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceTaskCreated extends Notification
{
    use Queueable;

    protected $log;

    public function __construct(MaintenanceLog $log)
    {
        $this->log = $log;
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Maintenance Alert: ' . $this->log->vehicle->name)
                    ->greeting('Hello Admin!')
                    ->line('A new automated maintenance task has been generated.')
                    ->line('Vehicle: ' . $this->log->vehicle->name)
                    ->line('Plate Number: ' . $this->log->vehicle->plate_number)
                    ->line('Current Odometer: ' . number_format($this->log->mileage_at_service) . ' km')
                    ->action('View Maintenance Dashboard', url('/admin/maintenance'))
                    ->line('Please schedule the service as soon as possible.');
    }

    public function toArray($notifiable): array
    {
        return [
            'maintenance_log_id' => $this->log->id,
            'vehicle_name' => $this->log->vehicle->name,
            'mileage' => $this->log->mileage_at_service,
            'message' => "Maintenance due for {$this->log->vehicle->name} at {$this->log->mileage_at_service}km",
            'link' => '/admin/maintenance/' . $this->log->vehicle_id
        ];
    }
}
