<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentListController extends Controller
{
    public function index()
    {
        // Get bookings that are pending payment (admin approved)
        // Also show awaiting verification (submitted but not confirmed)
        $bookings = Booking::with('vehicle', 'payment')
            ->where('user_id', Auth::id())
            ->whereIn('status', [
                Booking::STATUS_PENDING_PAYMENT,
                Booking::STATUS_AWAITING_VERIFICATION,
                Booking::STATUS_PARTIAL_PAID
            ])
            ->latest()
            ->get();

        return view('customer.payments.index', compact('bookings'));
    }
}
