<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Notifications\BookingConfirmed;
use App\Notifications\BookingRejected;
use Illuminate\Http\Request;

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
            'status'      => Booking::STATUS_CONFIRMED,
            'admin_notes' => $request->admin_notes,
        ]);

        $booking->vehicle->update(['status' => 'rented']);
        ActivityLog::log("Booking #{$booking->id} confirmed", Booking::class, $booking->id);

        try { $booking->user->notify(new BookingConfirmed($booking)); } catch (\Exception) {}

        return back()->with('success', 'Booking confirmed and customer notified.');
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
}
