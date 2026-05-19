<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackingController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'upcoming');
        $query = Booking::with(['vehicle'])->where('user_id', Auth::id());

        switch ($tab) {
            case 'ongoing':
                $query->where('status', Booking::STATUS_ONGOING);
                break;
            case 'past':
                $query->where('status', Booking::STATUS_COMPLETED);
                break;
            case 'cancelled':
                $query->whereIn('status', [Booking::STATUS_CANCELLED, Booking::STATUS_REJECTED]);
                break;
            case 'no_show':
                $query->where('status', Booking::STATUS_NO_SHOW);
                break;
            default: // upcoming
                $query->whereIn('status', [
                    Booking::STATUS_AWAITING_APPROVAL,
                    Booking::STATUS_PENDING_PAYMENT,
                    Booking::STATUS_AWAITING_VERIFICATION,
                    Booking::STATUS_PARTIAL_PAID,
                    Booking::STATUS_FULLY_PAID,
                    Booking::STATUS_CONFIRMED
                ]);
                break;
        }

        $bookings = $query->latest()->get();

        return view('customer.tracking.index', compact('bookings', 'tab'));
    }

    public function show(Booking $booking)
    {
        abort_if($booking->user_id !== Auth::id(), 403);

        $booking->load(['vehicle', 'payment']);
        return view('customer.tracking.show', compact('booking'));
    }
}
