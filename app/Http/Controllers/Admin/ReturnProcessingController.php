<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Notifications\ReturnConfirmed;

class ReturnProcessingController extends Controller
{
    public function index()
    {
        $rentals = \App\Models\Rental::with(['user', 'vehicle', 'booking'])
            ->whereIn('status', ['active', 'overdue'])
            ->latest()
            ->paginate(15);

        return view('admin.returns.index', compact('rentals'));
    }

    public function process(\App\Models\Rental $rental)
    {
        abort_unless(in_array($rental->status, ['active', 'overdue']), 422, 'Rental is not active.');

        // Requirement: Return Inspection must exist for the booking
        $returnInsp = $rental->booking->inspections()->where('type', 'return')->first();
        if (!$returnInsp) {
            return back()->with('error', 'A return inspection must be recorded before processing the return.');
        }

        $now = now();
        $lateFee = 0;
        
        // Late calculation (12:00 PM cutoff on the expected return date)
        $dueDate = $rental->expected_return_date; // This was set to 12:00 PM during pickup
        if ($now->gt($dueDate)) {
            $hoursLate = ceil($now->diffInMinutes($dueDate) / 60);
            if ($hoursLate > 0) {
                $lateFee = $hoursLate * $rental->vehicle->late_penalty_per_hour;
            }
        }

        // Fuel calculation (Missing liters * refueling_fee_per_liter)
        $refuelFee = 0;
        $fuelDiffPercent = $rental->pickup_fuel - $returnInsp->fuel_level;
        if ($fuelDiffPercent > 0) {
            $litersMissing = ($fuelDiffPercent / 100) * $rental->vehicle->fuel_capacity_liters;
            $refuelFee = $litersMissing * $rental->vehicle->refueling_fee_per_liter;
        }

        $rental->update([
            'status'             => 'completed',
            'actual_return_date' => $now,
            'return_odometer'    => $returnInsp->odometer_reading,
            'return_fuel'        => $returnInsp->fuel_level,
            'late_fee'           => $lateFee,
            'refueling_fee'      => $refuelFee,
        ]);

        $rental->vehicle->update([
            'status'   => 'available',
            'odometer' => $returnInsp->odometer_reading
        ]);

        // Booking status update
        $rental->booking->update([
            'status'                  => \App\Models\Booking::STATUS_COMPLETED,
            'security_deposit_status' => $lateFee > 0 || $refuelFee > 0 ? 'held_for_deduction' : 'released',
            'late_fee'                => $lateFee,
            'refueling_fee'           => $refuelFee,
        ]);

        // Loyalty Points Accrual (1 point per ₱100 of total_amount)
        $user = $rental->user;
        if ($user) {
            $pointsEarned = floor($rental->booking->total_amount / 100);
            $user->increment('loyalty_points', $pointsEarned);
            ActivityLog::log("Customer {$user->name} earned {$pointsEarned} loyalty points from Booking #{$rental->booking_id}", \App\Models\User::class, $user->id);
        }

        $totalExtra = $lateFee + $refuelFee;
        ActivityLog::log("Rental #{$rental->id} completed. Extra fees: ₱" . number_format($totalExtra, 2), \App\Models\Rental::class, $rental->id);

        if ($rental->user) {
            try { $rental->user->notify(new ReturnConfirmed($rental->booking)); } catch (\Exception) {}
        }

        $msg = 'Rental completed successfully.';
        if ($totalExtra > 0) {
            $msg .= " Extra charges: ₱" . number_format($totalExtra, 2) . ". Security deposit status: " . $rental->booking->security_deposit_status;
        } else {
            $msg .= " Security deposit released.";
        }

        return redirect()->route('admin.rentals.index')->with('success', $msg);
    }
}
