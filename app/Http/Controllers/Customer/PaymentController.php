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

        if ($booking->payment) {
            return back()->with('error', 'Payment already submitted for this booking.');
        }

        $request->validate([
            'reference_code' => ['required', 'string', 'max:50'],
            'screenshot'     => ['required', 'image', 'max:5120'], // 5 MB
        ]);

        $path = $request->file('screenshot')->store('payments', 'public');

        Payment::create([
            'booking_id'     => $booking->id,
            'amount'         => $booking->total_amount,
            'payment_method' => 'gcash',
            'reference_code' => $request->reference_code,
            'screenshot_path'=> $path,
            'status'         => 'pending',
        ]);

        $booking->update(['status' => Booking::STATUS_AWAITING_VERIFICATION]);
        ActivityLog::log("Payment submitted for booking #{$booking->id}", Booking::class, $booking->id);

        return redirect()->route('customer.tracking.show', $booking)
            ->with('success', 'Payment submitted! We\'ll verify it shortly.');
    }

    private function authorizeBooking(Booking $booking): void
    {
        abort_if($booking->user_id !== Auth::id(), 403);
    }
}
