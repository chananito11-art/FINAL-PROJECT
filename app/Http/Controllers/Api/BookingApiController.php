<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\Payment;
use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingApiController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->user();
        
        $stats = [
            'total_bookings'   => Booking::where('user_id', $user->id)->count(),
            'active_rentals'   => Booking::where('user_id', $user->id)->whereIn('status', ['partial_paid', 'fully_paid', 'confirmed', 'ongoing'])->count(),
            'pending_approval' => Booking::where('user_id', $user->id)->where('status', 'awaiting_approval')->count(),
            'total_spent'      => (float)Booking::where('user_id', $user->id)
                                    ->whereIn('status', ['partial_paid', 'fully_paid', 'confirmed', 'ongoing', 'completed'])
                                    ->sum('total_amount'),
        ];

        $recentBookings = Booking::with('vehicle')
            ->where('user_id', $user->id)
            ->latest()
            ->take(3)
            ->get()
            ->map(function ($booking) {
                return $this->formatBooking($booking);
            });

        $recommendedVehicles = Vehicle::available()
            ->orderBy('price_per_day', 'asc')
            ->take(3)
            ->get()
            ->map(function ($vehicle) {
                return [
                    'id' => $vehicle->id,
                    'name' => $vehicle->name,
                    'brand' => $vehicle->brand,
                    'type' => $vehicle->type,
                    'transmission' => $vehicle->transmission,
                    'fuel' => $vehicle->fuel,
                    'capacity' => $vehicle->capacity,
                    'price_per_day' => (float)$vehicle->price_per_day,
                    'image_url' => $vehicle->image_url,
                    'odometer' => $vehicle->odometer,
                    'status' => $vehicle->status,
                ];
            });

        return response()->json([
            'stats' => $stats,
            'recent_bookings' => $recentBookings,
            'recommended_vehicles' => $recommendedVehicles,
        ]);
    }

    public function transactions(Request $request)
    {
        $user = $request->user();

        $transactions = Payment::whereHas('booking', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with('booking.vehicle')
            ->latest()
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'booking_id' => $payment->booking_id,
                    'vehicle_name' => $payment->booking->vehicle->name,
                    'vehicle_image_url' => $payment->booking->vehicle->image_url,
                    'amount' => (float)$payment->amount,
                    'amount_submitted' => (float)$payment->amount_submitted,
                    'payment_method' => $payment->payment_method,
                    'reference_code' => $payment->reference_code,
                    'gcash_transaction_reference_number' => $payment->gcash_transaction_reference_number,
                    'gcash_account_name' => $payment->gcash_account_name,
                    'status' => $payment->status,
                    'date' => $payment->created_at->toFormattedDateString(),
                ];
            });

        return response()->json($transactions);
    }

    public function myBookings(Request $request)
    {
        $user = $request->user();
        
        $bookings = Booking::where('user_id', $user->id)
            ->with(['vehicle', 'payments', 'inspections'])
            ->latest()
            ->get()
            ->map(function ($booking) {
                return $this->formatBooking($booking);
            });

        return response()->json($bookings);
    }

    public function show(Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $booking->load(['vehicle', 'payments', 'inspections']);
        return response()->json($this->formatBooking($booking));
    }

    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user->isVerified()) {
            return response()->json([
                'message' => 'Driver verification is required before placing a reservation.'
            ], 403);
        }

        $validated = $request->validate([
            'vehicle_id'  => ['required', 'exists:vehicles,id'],
            'pickup_date' => ['required', 'date', 'after_or_equal:today'],
            'return_date' => ['required', 'date', 'after:pickup_date'],
            'discount_code' => ['nullable', 'string'],
        ]);

        return DB::transaction(function () use ($validated, $request, $user) {
            $vehicle = Vehicle::where('id', $validated['vehicle_id'])->lockForUpdate()->firstOrFail();

            $pickup = Carbon::parse($validated['pickup_date']);
            $return = Carbon::parse($validated['return_date']);

            if (!Vehicle::isAvailableForDates($vehicle->id, $pickup, $return)) {
                return response()->json([
                    'message' => 'This vehicle is not available for the selected dates.'
                ], 422);
            }

            $days = $pickup->diffInDays($return) ?: 1;
            
            // Smart Pricing Calculation
            $smartPricing = new \App\Services\SmartPricingService();
            $subtotal = $smartPricing->calculateFinalPrice($vehicle, $pickup, $return);
            
            // Handle Discount Code
            $discountAmount = 0;
            if ($request->filled('discount_code')) {
                $discount = Discount::where('code', $request->discount_code)->first();
                if ($discount && $discount->isValid()) {
                    $discountAmount = $discount->calculateDiscount($subtotal);
                    $discount->increment('times_used');
                }
            }

            $totalAmount = $subtotal - $discountAmount;

            $booking = Booking::create([
                'user_id'                => $user->id,
                'vehicle_id'             => $vehicle->id,
                'first_name'             => $user->first_name,
                'last_name'              => $user->last_name,
                'email'                  => $user->email,
                'phone'                  => $user->phone ?? 'N/A',
                'drivers_license_number' => $user->documents()->where('document_type', "Driver's License")->where('status', 'approved')->first()?->file_path ?? 'VERIFIED_USER',
                'pickup_date'            => $validated['pickup_date'],
                'return_date'            => $validated['return_date'],
                'total_amount'           => $totalAmount,
                'discount_amount'        => $discountAmount,
                'security_deposit'       => 3000.00,
                'security_deposit_status'=> 'pending',
                'status'                 => Booking::STATUS_PENDING_PAYMENT,
                'terms_agreed_at'        => now(),
                'hold_expires_at'        => now()->addHour(),
            ]);

            ActivityLog::log("Booking created via mobile API #{$booking->id}", Booking::class, $booking->id);

            return response()->json([
                'message' => 'Booking created successfully! Please complete payment within 1 hour to secure your reservation.',
                'booking' => $this->formatBooking($booking),
            ]);
        });
    }

    public function submitPayment(Request $request, Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($booking->balance_amount <= 0) {
            return response()->json(['message' => 'This booking is already fully paid.'], 422);
        }

        if ($booking->status === Booking::STATUS_CANCELLED) {
            return response()->json(['message' => 'This booking is cancelled and cannot receive payments.'], 422);
        }

        // Check for pending payments
        if ($booking->payments()->where('status', 'pending')->exists()) {
            return response()->json(['message' => 'You already have a pending payment verification.'], 422);
        }

        $request->validate([
            'amount_submitted' => ['required', 'numeric', 'min:1', 'max:' . ($booking->balance_amount + 100)],
            'gcash_transaction_reference_number' => ['required', 'string', 'max:50', 'unique:payments,gcash_transaction_reference_number'],
            'gcash_account_name' => ['nullable', 'string', 'max:100'],
            'screenshot' => ['required', 'image', 'max:5120'],
        ]);

        $path = $request->file('screenshot')->store('payments', 'public');

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => $request->amount_submitted,
            'amount_submitted' => $request->amount_submitted,
            'payment_method' => 'gcash',
            'reference_code' => 'GCASH-' . strtoupper(uniqid()),
            'gcash_transaction_reference_number' => $request->gcash_transaction_reference_number,
            'gcash_account_name' => $request->gcash_account_name,
            'screenshot_path' => $path,
            'status' => 'pending',
        ]);

        if (!in_array($booking->status, [Booking::STATUS_COMPLETED, Booking::STATUS_ONGOING])) {
            $booking->update(['status' => Booking::STATUS_AWAITING_VERIFICATION]);
        }

        ActivityLog::log("Payment submitted via API for booking #{$booking->id} (₱" . number_format($request->amount_submitted, 2) . ")", Booking::class, $booking->id);

        return response()->json([
            'message' => 'Payment submitted successfully! Please wait for admin verification.',
            'booking' => $this->formatBooking($booking->fresh(['vehicle', 'payments', 'inspections'])),
        ]);
    }

    private function formatBooking($booking)
    {
        return [
            'id' => $booking->id,
            'vehicle' => [
                'id' => $booking->vehicle->id,
                'name' => $booking->vehicle->name,
                'brand' => $booking->vehicle->brand,
                'type' => $booking->vehicle->type,
                'transmission' => $booking->vehicle->transmission,
                'fuel' => $booking->vehicle->fuel,
                'capacity' => $booking->vehicle->capacity,
                'price_per_day' => (float)$booking->vehicle->price_per_day,
                'image_url' => $booking->vehicle->image_url,
                'odometer' => $booking->vehicle->odometer,
                'status' => $booking->vehicle->status,
            ],
            'pickup_date' => $booking->pickup_date->toDateString(),
            'return_date' => $booking->return_date->toDateString(),
            'total_amount' => (float)$booking->total_amount,
            'paid_amount' => (float)$booking->paid_amount,
            'balance_amount' => (float)$booking->balance_amount,
            'security_deposit' => (float)$booking->security_deposit,
            'security_deposit_status' => $booking->security_deposit_status,
            'status' => $booking->status,
            'payments' => $booking->payments->map(function ($payment) {
                return [
                    'date' => $payment->created_at->toFormattedDateString(),
                    'method' => $payment->payment_method,
                    'amount' => (float)$payment->amount,
                    'status' => $payment->status,
                    'notes' => $payment->gcash_transaction_reference_number ? "Ref: {$payment->gcash_transaction_reference_number}" : ($payment->reference_code ?? ''),
                ];
            })->values()->all(),
            'inspections' => $booking->inspections->map(function ($inspection) {
                return [
                    'type' => $inspection->type,
                    'date' => $inspection->created_at->toFormattedDateString(),
                    'odometer' => $inspection->odometer_reading,
                    'fuel' => $inspection->fuel_level,
                    'notes' => $inspection->notes ?? '',
                ];
            })->values()->all(),
        ];
    }
}
