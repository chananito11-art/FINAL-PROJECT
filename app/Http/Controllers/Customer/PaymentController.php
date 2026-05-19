<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Payment;
use App\Notifications\BookingConfirmed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function show(Booking $booking)
    {
        $this->authorizeBooking($booking);
        return view('customer.payment.show', compact('booking'));
    }

    public function store(Request $request, Booking $booking)
    {
        $this->authorizeBooking($booking);

        if ($booking->balance_amount <= 0) {
            return back()->with('error', 'This booking is already fully paid.');
        }

        if ($booking->status === Booking::STATUS_CANCELLED) {
            return back()->with('error', 'This booking has been cancelled and cannot accept payment.');
        }

        // Check for pending payments
        if ($booking->payments()->where('status', 'pending')->exists()) {
            return back()->with('error', 'You have a pending payment verification. Please wait for us to verify it before submitting another.');
        }

        $request->validate([
            'reference_code'                     => ['required', 'string', 'max:100'],
            'gcash_transaction_reference_number'  => ['required', 'string', 'max:50', 'unique:payments,gcash_transaction_reference_number'],
            'amount_submitted'                    => ['required', 'numeric', 'min:1', 'max:' . ($booking->balance_amount + 100)], // Allow small overpayment
            'gcash_account_name'                  => ['nullable', 'string', 'max:100'],
            'screenshot'                          => ['required', 'image', 'max:5120'],
        ]);

        $path = $request->file('screenshot')->store('payments', 'public');

        Payment::create([
            'booking_id'                          => $booking->id,
            'amount'                              => $request->amount_submitted, // Store submitted amount as the amount to be verified
            'amount_submitted'                    => $request->amount_submitted,
            'payment_method'                      => 'gcash',
            'reference_code'                      => $request->reference_code,
            'gcash_transaction_reference_number'  => $request->gcash_transaction_reference_number,
            'gcash_account_name'                  => $request->gcash_account_name,
            'screenshot_path'                     => $path,
            'status'                              => 'pending',
        ]);

        if (!in_array($booking->status, [Booking::STATUS_COMPLETED, Booking::STATUS_ONGOING])) {
            $booking->update(['status' => Booking::STATUS_AWAITING_VERIFICATION]);
        }
        ActivityLog::log("Payment submitted for booking #{$booking->id} (₱" . number_format($request->amount_submitted, 2) . ")", Booking::class, $booking->id);

        return redirect()->route('customer.tracking.show', $booking)
            ->with('success', 'Payment submitted! We\'ll verify it shortly.');
    }

    private function authorizeBooking(Booking $booking): void
    {
        abort_if($booking->user_id !== Auth::id(), 403);
    }
}
