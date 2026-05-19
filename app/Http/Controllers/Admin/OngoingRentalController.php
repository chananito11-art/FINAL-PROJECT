<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rental;

class OngoingRentalController extends Controller
{
    public function index()
    {
        $rentals = Rental::with(['booking.vehicle', 'booking.user', 'booking.guestProfile', 'vehicle'])
            ->where('status', 'active')
            ->orderByRaw("CASE WHEN expected_return_date < NOW() THEN 0 ELSE 1 END") // overdue first
            ->orderBy('expected_return_date')
            ->paginate(20);

        $overdueCount = Rental::where('status', 'active')
            ->where('expected_return_date', '<', now())
            ->count();

        return view('admin.rentals.index', compact('rentals', 'overdueCount'));
    }
}
