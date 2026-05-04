<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\TermsAndCondition;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        return view('customer.booking.create', compact('vehicle', 'terms'));
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
        $days    = \Carbon\Carbon::parse($validated['pickup_date'])
                       ->diffInDays(\Carbon\Carbon::parse($validated['return_date'])) ?: 1;

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
            'status'                 => Booking::STATUS_PENDING_PAYMENT,
            'terms_agreed_at'        => now(),
        ]);

        ActivityLog::log("Booking created #{$booking->id}", Booking::class, $booking->id);

        return redirect()->route('customer.payment.show', $booking)
            ->with('success', 'Booking created! Please complete your GCash payment.');
    }
}
