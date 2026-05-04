<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Notifications\ReturnConfirmed;

class ReturnProcessingController extends Controller
{
    public function index()
    {
        $bookings = Booking::with(['user', 'vehicle'])
            ->where('status', Booking::STATUS_ONGOING)
            ->latest()
            ->paginate(15);

        return view('admin.returns.index', compact('bookings'));
    }

    public function process(Booking $booking)
    {
        abort_unless($booking->status === Booking::STATUS_ONGOING, 422, 'Booking is not ongoing.');

        $booking->update(['status' => Booking::STATUS_COMPLETED]);
        $booking->vehicle->update(['status' => 'available']);

        ActivityLog::log("Return processed for booking #{$booking->id}", Booking::class, $booking->id);

        try { $booking->user->notify(new ReturnConfirmed($booking)); } catch (\Exception) {}

        return back()->with('success', 'Return processed. Vehicle is now available again.');
    }
}
