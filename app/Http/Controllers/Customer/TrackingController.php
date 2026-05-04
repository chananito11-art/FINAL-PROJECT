<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;

class TrackingController extends Controller
{
    public function index()
    {
        $bookings = Booking::with(['vehicle'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('customer.tracking.index', compact('bookings'));
    }

    public function show(Booking $booking)
    {
        abort_if($booking->user_id !== Auth::id(), 403);

        $booking->load(['vehicle', 'payment']);
        return view('customer.tracking.show', compact('booking'));
    }
}
