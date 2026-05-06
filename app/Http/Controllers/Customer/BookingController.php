<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\TermsAndCondition;
use App\Models\Vehicle;
use App\Notifications\BookingCancelled;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function create(Request $request)
    {
        $vehicleId = $request->query('vehicle');
        $vehicle   = Vehicle::findOrFail($vehicleId);

        if (!$vehicle->isAvailable()) {
            return back()->with('error', 'This vehicle is no longer available.');
        }

        $terms = TermsAndCondition::current();

        // Near-conflict detection — warn if a booking ends within 2 days of the requested pickup
        $nearConflict = null;
        if ($request->pickup_date) {
            $pickup = Carbon::parse($request->pickup_date);
            $nearConflict = Booking::where('vehicle_id', $vehicle->id)
                ->whereIn('status', ['confirmed', 'ongoing'])
                ->whereBetween('return_date', [
                    $pickup->copy()->subDays(2),
                    $pickup->copy()->addDays(2),
                ])
                ->first();
        }

        return view('customer.booking.create', compact('vehicle', 'terms', 'nearConflict'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id'              => ['required', 'exists:vehicles,id'],
            'first_name'              => ['required', 'string', 'max:80'],
            'last_name'               => ['required', 'string', 'max:80'],
            'email'                   => ['required', 'email'],
            'phone'                   => ['required', 'string', 'max:20'],
            'drivers_license_number'  => ['required', 'string', 'max:50'],
            'pickup_date'             => ['required', 'date', 'after_or_equal:today'],
            'return_date'             => ['required', 'date', 'after:pickup_date'],
            'terms_agreed'            => ['accepted'],
        ]);

        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);

        // Availability check — prevent double-booking
        $pickup = Carbon::parse($validated['pickup_date']);
        $return = Carbon::parse($validated['return_date']);

        if (!Vehicle::isAvailableForDates($vehicle->id, $pickup, $return)) {
            return back()->withErrors(['dates' => 'This vehicle is not available for your selected dates. Please choose different dates.'])->withInput();
        }

        $days = $pickup->diffInDays($return) ?: 1;

        $booking = Booking::create([
            'user_id'                => Auth::id(),
            'vehicle_id'             => $vehicle->id,
            'first_name'             => $validated['first_name'],
            'last_name'              => $validated['last_name'],
            'email'                  => $validated['email'],
            'phone'                  => $validated['phone'],
            'drivers_license_number' => $validated['drivers_license_number'],
            'pickup_date'            => $validated['pickup_date'],
            'return_date'            => $validated['return_date'],
            'total_amount'           => $vehicle->price_per_day * $days,
            'status'                 => Booking::STATUS_AWAITING_APPROVAL,
            'terms_agreed_at'        => now(),
            'hold_expires_at'        => null,
        ]);

        ActivityLog::log("Booking created #{$booking->id}", Booking::class, $booking->id);

        return redirect()->route('customer.tracking.index')
            ->with('success', 'Booking submitted! Please wait for admin approval before making a payment.');
    }

    public function cancel(Booking $booking)
    {
        // Gate: must belong to auth user and be in pending_payment status
        if ($booking->user_id !== Auth::id() || $booking->status !== Booking::STATUS_PENDING_PAYMENT) {
            abort(403, 'You cannot cancel this booking.');
        }

        $booking->update([
            'status'              => Booking::STATUS_CANCELLED,
            'cancellation_reason' => 'customer_cancelled',
            'cancelled_by'        => Auth::id(),
            'cancelled_at'        => now(),
        ]);

        $booking->vehicle->update(['status' => 'available']);
        ActivityLog::log("Booking #{$booking->id} cancelled by customer", Booking::class, $booking->id);

        try {
            $booking->user->notify(new BookingCancelled($booking));
        } catch (\Exception) {}

        return redirect()->route('customer.tracking.index')
            ->with('success', 'Your reservation has been cancelled.');
    }
}
