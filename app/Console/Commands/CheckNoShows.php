<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\ActivityLog;
use Illuminate\Console\Command;

class CheckNoShows extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:check-no-shows';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark confirmed bookings as no-show if the pickup date has passed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Find bookings that are confirmed or fully paid but the pickup date was yesterday or earlier
        // and they are not yet 'ongoing' or 'completed'
        $noShows = Booking::whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_FULLY_PAID])
            ->whereDate('pickup_date', '<', now()->toDateString())
            ->get();

        $count = 0;
        foreach ($noShows as $booking) {
            $booking->update([
                'status' => Booking::STATUS_NO_SHOW,
                'admin_notes' => ($booking->admin_notes ? $booking->admin_notes . "\n" : "") . "Automatically marked as No-Show on " . now()->toDateTimeString()
            ]);

            // Release the vehicle
            $booking->vehicle->update(['status' => 'available']);

            ActivityLog::log(
                "Booking #{$booking->id} automatically marked as NO-SHOW (Pickup date: {$booking->pickup_date->format('Y-m-d')} passed)",
                Booking::class,
                $booking->id
            );

            $count++;
        }

        if ($count > 0) {
            $this->info("Successfully marked {$count} bookings as No-Show.");
        } else {
            $this->info("No expired pickup windows found.");
        }
    }
}
