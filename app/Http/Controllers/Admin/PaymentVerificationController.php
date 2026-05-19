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
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'pending');

        $query = Payment::with(['booking.user', 'booking.vehicle'])->latest();

        match ($tab) {
            'verified' => $query->where('status', 'verified'),
            'rejected' => $query->where('status', 'rejected'),
            'all'      => $query,
            default    => $query->where('status', 'pending'),
        };

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference_code', 'like', "%{$search}%")
                  ->orWhere('gcash_transaction_reference_number', 'like', "%{$search}%")
                  ->orWhereHas('booking.user', fn($u) =>
                      $u->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                  )
                  ->orWhereHas('booking', fn($b) => $b->where('id', $search));
            });
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $payments = $query->paginate(15)->withQueryString();

        $counts = [
            'pending'  => Payment::where('status', 'pending')->count(),
            'verified' => Payment::where('status', 'verified')->count(),
            'rejected' => Payment::where('status', 'rejected')->count(),
            'all'      => Payment::count(),
        ];

        $stats = [
            'verified_today'        => Payment::where('status', 'verified')
                                              ->whereDate('verified_at', today())
                                              ->sum('amount'),
            'total_verified'        => Payment::where('status', 'verified')->sum('amount'),
            'bookings_with_balance' => Booking::whereRaw('total_amount > COALESCE((
                                            SELECT SUM(p.amount) FROM payments p
                                            WHERE p.booking_id = bookings.id AND p.status = "verified"
                                        ), 0)')
                                        ->whereNotIn('status', ['cancelled', 'rejected', 'completed', 'no_show'])
                                        ->count(),
        ];

        return view('admin.payments.index', compact('payments', 'tab', 'counts', 'stats'));

    }

    public function show(Payment $payment)
    {
        $payment->load(['booking.user', 'booking.vehicle', 'verifiedBy']);
        return view('admin.payments.show', compact('payment'));
    }

    public function verify(Request $request, Payment $payment)
    {
        $request->validate([
            'amount_matched' => ['required', 'boolean'],
            'admin_notes'    => ['nullable', 'string', 'max:1000'],
        ]);

        $payment->update([
            'status'              => 'verified',
            'verified_by'         => Auth::id(),
            'verified_at'         => now(),
            'amount_matched'      => $request->amount_matched,
            'admin_payment_notes' => $request->admin_notes,
        ]);

        $booking = $payment->booking;
        $totalPaid = $booking->paid_amount;

        // Only update booking payment status if the booking is in a payment-phase state.
        // Do not downgrade a booking that is already confirmed, ongoing, or completed.
        $paymentPhaseStatuses = [
            Booking::STATUS_AWAITING_VERIFICATION,
            Booking::STATUS_PARTIAL_PAID,
            Booking::STATUS_PENDING_PAYMENT,
        ];

        if (in_array($booking->status, $paymentPhaseStatuses)) {
            if ($totalPaid >= $booking->total_amount) {
                $booking->update(['status' => Booking::STATUS_FULLY_PAID]);
                $msg = 'Payment verified. Booking is now Fully Paid.';
            } else {
                $booking->update(['status' => Booking::STATUS_PARTIAL_PAID]);
                $msg = 'Payment verified. Booking is now Partial Paid.';
            }
        } else {
            // Booking is already confirmed/ongoing/completed — just acknowledge payment
            $msg = 'Payment verified. Booking status unchanged (already ' . ucwords(str_replace('_', ' ', $booking->status)) . ').';
        }

        ActivityLog::log(
            "Payment verified for booking #{$booking->id}. Paid: ₱" . number_format($payment->amount, 2),
            Payment::class,
            $payment->id
        );

        try {
            if ($booking->user) {
                $booking->user->notify(new PaymentVerified($booking));
            }
        } catch (\Exception) {}

        return redirect()->route('admin.payments.index')->with('success', $msg);
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

        // Only roll back to pending_payment if we were actually awaiting verification
        // Don't touch bookings that are ongoing, completed, etc.
        if ($payment->booking->status === Booking::STATUS_AWAITING_VERIFICATION) {
            $payment->booking->update(['status' => Booking::STATUS_PENDING_PAYMENT]);
        }

        ActivityLog::log(
            "Payment rejected for booking #{$payment->booking_id}: {$request->rejection_reason}",
            Payment::class,
            $payment->id
        );

        return redirect()->route('admin.payments.index')
            ->with('success', 'Payment rejected. Customer may re-submit.');
    }

    public function history(Request $request)
    {
        $query = Payment::with(['booking.user', 'booking.vehicle'])
            ->whereIn('status', ['verified', 'rejected'])
            ->latest('verified_at');

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference_code', 'like', "%{$search}%")
                  ->orWhere('gcash_transaction_reference_number', 'like', "%{$search}%")
                  ->orWhereHas('booking.user', fn($u) =>
                      $u->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name',  'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                  );
            });
        }

        if ($request->date_from) {
            $query->whereDate('verified_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('verified_at', '<=', $request->date_to);
        }

        $payments = $query->paginate(20)->withQueryString();

        return view('admin.payments.history', compact('payments'));
    }

    public function recordRefund(Request $request, Payment $payment)
    {
        $request->validate([
            'refund_gcash_reference' => ['required', 'string', 'max:100'],
            'refund_notes'           => ['nullable', 'string', 'max:1000'],
        ]);

        $payment->update([
            'refund_issued'          => true,
            'refund_issued_at'       => now(),
            'refund_gcash_reference' => $request->refund_gcash_reference,
            'refund_notes'           => $request->refund_notes,
        ]);

        ActivityLog::log(
            "Refund recorded for payment #{$payment->id} (booking #{$payment->booking_id})",
            Payment::class,
            $payment->id
        );

        return back()->with('success', 'Refund recorded successfully.');
    }
}
