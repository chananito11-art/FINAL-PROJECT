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
        $status   = $request->query('status');
        $bookings = Booking::with(['user', 'vehicle', 'payment'])
            ->when($status, fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15);

        return view('admin.bookings.index', compact('bookings', 'status'));
    }

    public function show(Booking $booking)
    {
        $booking->load(['user', 'vehicle', 'payment', 'requirements']);
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

        try { $booking->user->notify(new \App\Notifications\BookingApproved($booking)); } catch (\Exception) {}

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

        try { $booking->user->notify(new BookingRejected($booking)); } catch (\Exception) {}

        return back()->with('success', 'Booking rejected and customer notified.');
    }

    public function markOngoing(Booking $booking)
    {
        $booking->update(['status' => Booking::STATUS_ONGOING]);
        ActivityLog::log("Booking #{$booking->id} marked ongoing", Booking::class, $booking->id);
        return back()->with('success', 'Booking marked as ongoing.');
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
            $booking->user->notify(new BookingCancelledByAdmin($booking, $request->cancellation_reason));
        } catch (\Exception) {}

        return back()->with('success', 'Booking has been cancelled and customer notified.');
    }
}
