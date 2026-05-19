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

        // Redirect unverified users to the verification page before allowing booking
        if (!Auth::user()->isVerified()) {
            return redirect()->route('customer.verification.show')
                ->with('warning', 'You must complete account verification before making a booking.');
        }

        $terms = TermsAndCondition::current();

        return view('customer.booking.create', compact('vehicle', 'terms'));
    }

    public function store(Request $request)
    {
        // Double-check verification on submission (in case session state changed)
        if (!Auth::user()->isVerified()) {
            return redirect()->route('customer.verification.show')
                ->with('warning', 'Account verification is required before completing a booking.');
        }

        $validated = $request->validate([
            'vehicle_id'              => ['required', 'exists:vehicles,id'],
            'first_name'              => ['required', 'string', 'max:80'],
            'last_name'               => ['required', 'string', 'max:80'],
            'email'                   => ['required', 'email'],
            'phone'                   => ['required', 'string', 'max:20'],
            'pickup_date'             => ['required', 'date', 'after_or_equal:today'],
            'return_date'             => ['required', 'date', 'after:pickup_date'],
            'terms_agreed'            => ['accepted'],
        ]);

        return \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $request) {
            $vehicle = Vehicle::where('id', $validated['vehicle_id'])->lockForUpdate()->firstOrFail();
            $user = Auth::user();

            // Availability check — prevent double-booking
            $pickup = Carbon::parse($validated['pickup_date']);
            $return = Carbon::parse($validated['return_date']);

            if (!Vehicle::isAvailableForDates($vehicle->id, $pickup, $return)) {
                return back()->withErrors(['dates' => 'This vehicle is not available for your selected dates. Please choose different dates.'])->withInput();
            }

            $days = $pickup->diffInDays($return) ?: 1;
            
            // Smart Pricing Calculation
            $smartPricing = new \App\Services\SmartPricingService();
            $subtotal = $smartPricing->calculateFinalPrice($vehicle, $pickup, $return);
            
            // Handle Discount Code
            $discountAmount = 0;
            if ($request->filled('discount_code')) {
                $discount = \App\Models\Discount::where('code', $request->discount_code)->first();
                if ($discount && $discount->isValid()) {
                    $discountAmount = $discount->calculateDiscount($subtotal);
                    $discount->increment('times_used');
                }
            }

            $totalAmount = $subtotal - $discountAmount;

            // New Business Logic: Auto-approve if verified
            $isVerified = $user->isVerified();
            $status = $isVerified ? Booking::STATUS_PENDING_PAYMENT : Booking::STATUS_AWAITING_APPROVAL;
            $holdExpires = $isVerified ? now()->addHour() : null;

            $booking = Booking::create([
                'user_id'                => $user->id,
                'vehicle_id'             => $vehicle->id,
                'first_name'             => $validated['first_name'],
                'last_name'              => $validated['last_name'],
                'email'                  => $validated['email'],
                'phone'                  => $validated['phone'],
                'drivers_license_number' => $user->documents()->where('document_type', "Driver's License")->where('status', 'approved')->first()?->file_path ?? 'VERIFIED_USER',
                'pickup_date'            => $validated['pickup_date'],
                'return_date'            => $validated['return_date'],
                'total_amount'           => $totalAmount,
                'discount_amount'        => $discountAmount,
                'security_deposit'       => 3000.00,
                'security_deposit_status'=> 'pending',
                'status'                 => $status,
                'terms_agreed_at'        => now(),
                'hold_expires_at'        => $holdExpires,
            ]);

            ActivityLog::log("Booking created #{$booking->id} (" . ($isVerified ? 'Auto-approved' : 'Awaiting verification') . ")", Booking::class, $booking->id);

            $msg = $isVerified 
                ? 'Booking confirmed! Please complete your payment within 1 hour to secure your reservation.' 
                : 'Booking submitted! Please wait for admin approval (Verification required).';

            return redirect()->route('customer.tracking.index')->with('success', $msg);
        });
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
