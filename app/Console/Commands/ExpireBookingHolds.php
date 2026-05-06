<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\Booking;
use App\Notifications\BookingHoldExpired;
use Illuminate\Console\Command;

class ExpireBookingHolds extends Command
{
    protected $signature   = 'bookings:expire-holds';
    protected $description = 'Auto-cancel pending_payment bookings whose 24-hour hold has expired.';

    public function handle(): int
    {
        $expired = Booking::where('status', Booking::STATUS_PENDING_PAYMENT)
            ->where('hold_expires_at', '<', now())
            ->with(['user', 'vehicle'])
            ->get();

        if ($expired->isEmpty()) {
            $this->info('No expired holds found.');
            return self::SUCCESS;
        }

        foreach ($expired as $booking) {
            $booking->update([
                'status'              => Booking::STATUS_CANCELLED,
                'cancellation_reason' => 'hold_expired',
                'cancelled_at'        => now(),
            ]);

            // Free up the vehicle
            if ($booking->vehicle) {
                $booking->vehicle->update(['status' => 'available']);
            }

            // Notify customer
            try {
                $booking->user?->notify(new BookingHoldExpired($booking));
            } catch (\Exception $e) {
                $this->warn("Notification failed for booking #{$booking->id}: " . $e->getMessage());
            }

            // Log the action
            ActivityLog::log(
                "Booking #{$booking->id} auto-cancelled — hold expired",
                Booking::class,
                $booking->id
            );

            $this->line("  ✓ Cancelled booking #{$booking->id} (hold expired at {$booking->hold_expires_at})");
        }

        $this->info("Expired {$expired->count()} booking hold(s).");
        return self::SUCCESS;
    }
}
