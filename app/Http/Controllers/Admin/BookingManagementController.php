<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Notifications\BookingCancelledByAdmin;
use App\Notifications\BookingConfirmed;
use App\Notifications\BookingRejected;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingManagementController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $type   = $request->query('type'); // 'walk-in' or 'online'

        $bookings = Booking::with(['user', 'vehicle', 'payment'])
            ->when($status, fn($q) => $q->where('status', $status))
            // By default (no status filter), hide 'ongoing' — they live in Ongoing Rentals module
            ->when(!$status, fn($q) => $q->whereNotIn('status', ['ongoing']))
            ->when($type === 'walk-in', fn($q) => $q->whereNull('user_id'))
            ->when($type === 'online',  fn($q) => $q->whereNotNull('user_id'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.bookings.index', compact('bookings', 'status', 'type'));
    }

    public function show(Booking $booking)
    {
        $booking->load(['user', 'vehicle', 'payment']);
        return view('admin.bookings.show', compact('booking'));
    }

    public function approve(Request $request, Booking $booking)
    {
        $request->validate(['admin_notes' => ['nullable', 'string', 'max:1000']]);

        $booking->update([
            'status'          => Booking::STATUS_PENDING_PAYMENT,
            'admin_notes'     => $request->admin_notes,
            'hold_expires_at' => now()->addHour(),
        ]);

        ActivityLog::log("Booking #{$booking->id} approved by admin (pending payment)", Booking::class, $booking->id);

        try { 
            if ($booking->user) {
                $booking->user->notify(new \App\Notifications\BookingApproved($booking)); 
            }
        } catch (\Exception) {}

        return back()->with('success', 'Booking approved. Customer has 1 hour to complete payment.');
    }

    public function reject(Request $request, Booking $booking)
    {
        $request->validate(['rejection_reason' => ['required', 'string', 'max:500']]);

        $booking->update([
            'status'           => Booking::STATUS_REJECTED,
            'rejection_reason' => $request->rejection_reason,
        ]);

        ActivityLog::log("Booking #{$booking->id} rejected", Booking::class, $booking->id);

        try { 
            if ($booking->user) {
                $booking->user->notify(new BookingRejected($booking)); 
            }
        } catch (\Exception) {}

        return back()->with('success', 'Booking rejected and customer notified.');
    }

    public function markOngoing(Booking $booking)
    {
        // Require pickup inspection
        $inspection = $booking->inspections()->where('type', 'pickup')->first();
        if (!$inspection) {
            return back()->with('error', 'You must perform a pickup inspection before handing over the vehicle.');
        }

        // Check if rental already exists to prevent duplicates
        if ($booking->rental()->exists()) {
            $booking->update(['status' => Booking::STATUS_ONGOING]);
            return back()->with('success', 'Rental is already active.');
        }

        // Create the Rental Record
        \App\Models\Rental::create([
            'booking_id'           => $booking->id,
            'vehicle_id'           => $booking->vehicle_id,
            'user_id'              => $booking->user_id,
            'pickup_date'          => now(),
            'expected_return_date' => $booking->return_date->setHour(12)->setMinute(0)->setSecond(0), // 12:00 PM cutoff
            'pickup_odometer'      => $inspection->odometer_reading,
            'pickup_fuel'          => $inspection->fuel_level,
            'status'               => 'active',
        ]);

        $booking->update(['status' => Booking::STATUS_ONGOING]);
        $booking->vehicle->update(['status' => 'rented']);

        ActivityLog::log("Booking #{$booking->id} handed over manually. Rental started.", Booking::class, $booking->id);
        
        return back()->with('success', 'Vehicle handed over successfully. Rental is now active.');
    }

    public function createWalkIn()
    {
        $vehicles = \App\Models\Vehicle::available()->get();
        return view('admin.bookings.walk-in', compact('vehicles'));
    }

    public function storeWalkIn(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id'              => ['required', 'exists:vehicles,id'],
            'guest_profile_id'        => ['nullable', 'exists:guest_profiles,id'],
            'first_name'              => ['required', 'string', 'max:80'],
            'last_name'               => ['required', 'string', 'max:80'],
            'email'                   => ['nullable', 'email'],
            'phone'                   => ['nullable', 'string', 'max:20'],
            'drivers_license_number'  => ['nullable', 'string', 'max:50'],
            'pickup_date'             => ['required', 'date'],
            'return_date'             => ['required', 'date', 'after:pickup_date'],
            'initial_payment'         => ['nullable', 'numeric', 'min:0'],
            'security_deposit'        => ['nullable', 'numeric', 'min:0'],
        ]);

        $vehicle = \App\Models\Vehicle::findOrFail($validated['vehicle_id']);
        
        $pickup = \Carbon\Carbon::parse($validated['pickup_date']);
        $return = \Carbon\Carbon::parse($validated['return_date']);

        if (!\App\Models\Vehicle::isAvailableForDates($vehicle->id, $pickup, $return)) {
            return back()->with('error', 'Vehicle is not available for these dates (including 1-day buffer).')->withInput();
        }

        // ── Resolve or create Guest Profile ──────────────────────────────────
        $guestData = [
            'first_name'             => $validated['first_name'],
            'last_name'              => $validated['last_name'],
            'email'                  => $validated['email'] ?? null,
            'phone'                  => $validated['phone'] ?? null,
            'drivers_license_number' => $validated['drivers_license_number'] ?? null,
        ];

        if (!empty($validated['guest_profile_id'])) {
            $guest = \App\Models\GuestProfile::find($validated['guest_profile_id']);
            $guest->update(array_filter($guestData, fn($v) => !is_null($v)));
        } else {
            $guest = \App\Models\GuestProfile::create($guestData);
        }
        // ─────────────────────────────────────────────────────────────────────

        $days        = $pickup->diffInDays($return) ?: 1;
        
        // Smart Pricing Calculation
        $smartPricing = new \App\Services\SmartPricingService();
        $totalAmount = $smartPricing->calculateFinalPrice($vehicle, $pickup, $return);

        $booking = Booking::create([
            'user_id'                => null,
            'guest_profile_id'       => $guest->id,
            'vehicle_id'             => $vehicle->id,
            'first_name'             => $guest->first_name,
            'last_name'              => $guest->last_name,
            'email'                  => $guest->email ?? 'walkin@orangecrush.local',
            'phone'                  => $guest->phone,
            'drivers_license_number' => $guest->drivers_license_number,
            'pickup_date'            => $validated['pickup_date'],
            'return_date'            => $validated['return_date'],
            'total_amount'           => $totalAmount,
            'security_deposit'       => $validated['security_deposit'] ?? 3000.00,
            'security_deposit_status'=> 'held',
            'status'                 => Booking::STATUS_PENDING_PAYMENT,
            'terms_agreed_at'        => now(),
        ]);

        if ($request->filled('initial_payment') && $request->initial_payment > 0) {
            \App\Models\Payment::create([
                'booking_id'     => $booking->id,
                'amount'         => $request->initial_payment,
                'payment_method' => 'cash',
                'reference_code' => 'CASH-' . strtoupper(uniqid()),
                'status'         => 'verified',
                'verified_at'    => now(),
                'verified_by'    => Auth::id(),
            ]);

            // Update status based on payment
            if ($request->initial_payment >= $totalAmount) {
                $booking->update(['status' => Booking::STATUS_FULLY_PAID]);
            } else {
                $booking->update(['status' => Booking::STATUS_PARTIAL_PAID]);
            }
        }

        ActivityLog::log("Walk-in booking #{$booking->id} created for {$guest->full_name}. Total: ₱" . number_format($totalAmount, 2), Booking::class, $booking->id);

        return redirect()->route('admin.bookings.show', $booking)->with('success', "Walk-in booking created for {$guest->full_name}.");
    }

    public function confirm(Booking $booking)
    {
        if ($booking->paid_amount <= 0) {
            return back()->with('error', 'Booking cannot be confirmed without at least a partial payment.');
        }

        $booking->update(['status' => Booking::STATUS_CONFIRMED]);
        $booking->vehicle->update(['status' => 'rented']); // Reserve the car

        ActivityLog::log("Booking #{$booking->id} manually confirmed by admin.", Booking::class, $booking->id);

        return back()->with('success', 'Booking confirmed. Car is now reserved.');
    }

    public function recordPayment(Request $request, Booking $booking)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'notes'  => ['nullable', 'string', 'max:500'],
        ]);

        $payment = \App\Models\Payment::create([
            'booking_id'          => $booking->id,
            'amount'              => $request->amount,
            'payment_method'      => 'cash',
            'reference_code'      => 'CASH-' . strtoupper(uniqid()),
            'status'              => 'verified',
            'verified_at'         => now(),
            'verified_by'         => Auth::id(),
            'admin_payment_notes' => $request->notes,
        ]);

        // Only update booking status if it's currently in a payment-phase state
        $paymentPhaseStatuses = [
            Booking::STATUS_PENDING_PAYMENT,
            Booking::STATUS_AWAITING_VERIFICATION,
            Booking::STATUS_PARTIAL_PAID,
            Booking::STATUS_FULLY_PAID,
        ];
        if (in_array($booking->status, $paymentPhaseStatuses)) {
            $totalPaid = $booking->paid_amount;
            if ($totalPaid >= $booking->total_amount) {
                $booking->update(['status' => Booking::STATUS_FULLY_PAID]);
            } else {
                $booking->update(['status' => Booking::STATUS_PARTIAL_PAID]);
            }
        }

        ActivityLog::log("Cash payment of ₱" . number_format($request->amount, 2) . " recorded for booking #{$booking->id}", Booking::class, $booking->id);

        return back()->with('success', 'Payment recorded successfully.');
    }

    public function settleDeposit(Request $request, Booking $booking)
    {
        $request->validate([
            'action'       => ['required', 'in:refund_full,deduct_and_refund,forfeit,excess_charge'],
            'refund_amount'=> ['nullable', 'numeric', 'min:0'],
            'extra_charge' => ['nullable', 'numeric', 'min:0'],
            'admin_notes'  => ['nullable', 'string', 'max:500'],
        ]);

        $lateFee       = $booking->late_fee       ?? 0;
        $refuelingFee  = $booking->refueling_fee  ?? 0;
        $totalDeductions = $lateFee + $refuelingFee;
        $deposit       = $booking->security_deposit;
        $surplus       = max(0, $deposit - $totalDeductions);
        $deficit       = max(0, $totalDeductions - $deposit);

        $action = $request->action;
        $note   = $request->admin_notes ?? '';

        switch ($action) {

            case 'refund_full':
                // No deductions — return full deposit
                $booking->update([
                    'security_deposit_status' => 'refunded',
                    'admin_notes' => trim(($booking->admin_notes ?? '') . "\nDeposit fully refunded. {$note}"),
                ]);
                $msg = "Full deposit of ₱" . number_format($deposit, 2) . " refunded to customer.";
                break;

            case 'deduct_and_refund':
                // Deduct fees, refund remainder
                $refundAmount = $request->refund_amount ?? $surplus;
                $booking->update([
                    'security_deposit_status' => 'settled',
                    'admin_notes' => trim(($booking->admin_notes ?? '') . "\nDeposit settled. Deducted: ₱" . number_format($totalDeductions, 2) . ". Refunded: ₱" . number_format($refundAmount, 2) . ". {$note}"),
                ]);
                $msg = "Deposit settled. ₱" . number_format($refundAmount, 2) . " refunded after deductions.";
                break;

            case 'excess_charge':
                // Deductions exceed deposit — customer owes extra
                $extraOwed = $request->extra_charge ?? $deficit;
                // Record the extra charge as a pending payment
                \App\Models\Payment::create([
                    'booking_id'          => $booking->id,
                    'amount'              => $extraOwed,
                    'amount_submitted'    => 0,
                    'payment_method'      => 'cash',
                    'reference_code'      => 'XCHG-' . strtoupper(uniqid()),
                    'status'              => 'pending',
                    'admin_payment_notes' => "Excess return charges. Deposit fully consumed. Extra owed: ₱" . number_format($extraOwed, 2) . ". {$note}",
                ]);
                $booking->update([
                    'security_deposit_status' => 'forfeited',
                    'admin_notes' => trim(($booking->admin_notes ?? '') . "\nDeposit forfeited (fully consumed by fees). Extra charge of ₱" . number_format($extraOwed, 2) . " pending. {$note}"),
                ]);
                $msg = "Deposit fully consumed. Extra charge of ₱" . number_format($extraOwed, 2) . " recorded as pending payment.";
                break;

            case 'forfeit':
            default:
                $booking->update([
                    'security_deposit_status' => 'forfeited',
                    'admin_notes' => trim(($booking->admin_notes ?? '') . "\nDeposit forfeited. {$note}"),
                ]);
                $msg = 'Deposit marked as forfeited.';
                break;
        }

        ActivityLog::log(
            "Deposit settlement for Booking #{$booking->id} — Action: {$action}. " . $msg,
            Booking::class,
            $booking->id
        );

        return back()->with('success', $msg);
    }


    public function markNoShow(Request $request, Booking $booking)
    {
        if ($booking->status !== Booking::STATUS_CONFIRMED) {
            return back()->with('error', 'Only confirmed bookings can be marked as No-Show.');
        }

        if ($booking->pickup_date->isFuture()) {
            return back()->with('error', 'Cannot mark as No-Show before the pickup date.');
        }

        $booking->update([
            'status'        => Booking::STATUS_NO_SHOW,
            'admin_notes'   => ($booking->admin_notes ? $booking->admin_notes . "\n" : '')
                               . 'Marked No-Show by admin on ' . now()->format('M d, Y H:i')
                               . ($request->filled('notes') ? ': ' . $request->notes : '.'),
            'cancelled_at'  => now(),
            'cancelled_by'  => Auth::id(),
        ]);

        // Free the vehicle
        $booking->vehicle->update(['status' => 'available']);

        // Forfeit deposit if requested
        if ($request->boolean('forfeit_deposit') && $booking->security_deposit > 0) {
            $booking->update(['security_deposit_status' => 'forfeited']);
        }

        ActivityLog::log(
            "Booking #{$booking->id} marked as No-Show by admin.",
            Booking::class,
            $booking->id
        );

        try {
            if ($booking->user) {
                $booking->user->notify(new BookingCancelledByAdmin($booking, 'Customer did not show up for pickup.'));
            }
        } catch (\Exception) {}

        return back()->with('success', 'Booking marked as No-Show. Vehicle is now available.');
    }

    public function cancel(Request $request, Booking $booking)
    {
        $request->validate([
            'cancellation_reason' => ['required', 'string', 'max:500'],
        ]);

        if ($booking->status === Booking::STATUS_COMPLETED) {
            return back()->with('error', 'Completed bookings cannot be cancelled.');
        }

        $booking->update([
            'status'              => Booking::STATUS_CANCELLED,
            'cancellation_reason' => 'admin_cancelled',
            'admin_notes'         => $request->cancellation_reason,
            'cancelled_by'        => Auth::id(),
            'cancelled_at'        => now(),
        ]);

        $booking->vehicle->update(['status' => 'available']);

        ActivityLog::log(
            "Admin cancelled booking #{$booking->id}: {$request->cancellation_reason}",
            Booking::class,
            $booking->id
        );

        try {
            if ($booking->user) {
                $booking->user->notify(new BookingCancelledByAdmin($booking, $request->cancellation_reason));
            }
        } catch (\Exception) {}

        return back()->with('success', 'Booking has been cancelled and customer notified.');
    }
}
