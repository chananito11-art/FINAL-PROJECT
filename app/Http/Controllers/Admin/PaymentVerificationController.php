<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Payment;
use App\Notifications\PaymentVerified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentVerificationController extends Controller
{
    public function index()
    {
        $payments = Payment::with(['booking.user', 'booking.vehicle'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(15);

        return view('admin.payments.index', compact('payments'));
    }

    public function verify(Request $request, Payment $payment)
    {
        $payment->update([
            'status'      => 'verified',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        $payment->booking->update(['status' => Booking::STATUS_CONFIRMED]);
        $payment->booking->vehicle->update(['status' => 'rented']);

        ActivityLog::log("Payment verified for booking #{$payment->booking_id}", Payment::class, $payment->id);

        try { $payment->booking->user->notify(new PaymentVerified($payment->booking)); } catch (\Exception) {}

        return back()->with('success', 'Payment verified and booking confirmed.');
    }

    public function reject(Request $request, Payment $payment)
    {
        $request->validate(['rejection_reason' => ['required', 'string', 'max:500']]);

        $payment->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'verified_by'      => Auth::id(),
            'verified_at'      => now(),
        ]);

        $payment->booking->update(['status' => Booking::STATUS_PENDING_PAYMENT]);
        ActivityLog::log("Payment rejected for booking #{$payment->booking_id}", Payment::class, $payment->id);

        return back()->with('success', 'Payment rejected. Customer may re-submit.');
    }
}
