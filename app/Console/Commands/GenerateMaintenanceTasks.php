<?php

namespace App\Console\Commands;

use App\Models\Vehicle;
use App\Models\MaintenanceLog;
use App\Models\ActivityLog;
use Illuminate\Console\Command;

class GenerateMaintenanceTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:generate-tasks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically generate maintenance alerts/logs for vehicles approaching their service interval';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $vehicles = Vehicle::all();
        $count = 0;

        foreach ($vehicles as $vehicle) {
            // Get the last maintenance log
            $lastLog = $vehicle->maintenanceLogs()->latest('mileage_at_service')->first();
            
            $lastServiceMileage = $lastLog ? $lastLog->mileage_at_service : 0;
            $currentOdometer = $vehicle->odometer;
            $interval = $vehicle->maintenance_interval_km ?? 5000;

            // If the vehicle has traveled more than the interval since last service
            if (($currentOdometer - $lastServiceMileage) >= $interval) {
                
                // Check if a "Scheduled Maintenance" log for this mileage already exists to avoid duplicates
                $exists = MaintenanceLog::where('vehicle_id', $vehicle->id)
                    ->where('service_type', 'Scheduled Maintenance')
                    ->where('mileage_at_service', '>=', $currentOdometer)
                    ->exists();

                if (!$exists) {
                    $log = MaintenanceLog::create([
                        'vehicle_id' => $vehicle->id,
                        'service_type' => 'Scheduled Maintenance',
                        'description' => "Automated alert: Vehicle has reached {$currentOdometer}km. Service interval is every {$interval}km.",
                        'mileage_at_service' => $currentOdometer,
                        'service_date' => now(),
                        'next_service_due_mileage' => $currentOdometer + $interval,
                        'next_service_due_date' => now()->addMonths(6),
                    ]);

                    ActivityLog::log(
                        "Automated maintenance task generated for {$vehicle->name} at {$currentOdometer}km",
                        MaintenanceLog::class,
                        $log->id
                    );

                    // Notify Admins
                    $admins = \App\Models\User::role(['admin', 'super_admin'])->get();
                    foreach ($admins as $admin) {
                        try {
                            $admin->notify(new \App\Notifications\MaintenanceTaskCreated($log));
                        } catch (\Exception $e) {
                            $this->error("Failed to notify {$admin->email}: " . $e->getMessage());
                        }
                    }

                    $count++;
                }
            }
        }

        if ($count > 0) {
            $this->info("Generated {$count} new maintenance tasks.");
        } else {
            $this->info("No vehicles require new maintenance tasks at this time.");
        }
    }
}
