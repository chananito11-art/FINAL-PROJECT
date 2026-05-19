<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $stats = [
            'total_bookings'   => Booking::where('user_id', $user->id)->count(),
            'active_rentals'   => Booking::where('user_id', $user->id)->whereIn('status', ['partial_paid', 'fully_paid', 'confirmed', 'ongoing'])->count(),
            'pending_approval' => Booking::where('user_id', $user->id)->where('status', 'awaiting_approval')->count(),
            'total_spent'      => Booking::where('user_id', $user->id)
                                    ->whereIn('status', ['partial_paid', 'fully_paid', 'confirmed', 'ongoing', 'completed'])
                                    ->sum('total_amount'),
        ];

        $recentBookings = Booking::with('vehicle')
            ->where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        $recommendedVehicles = Vehicle::available()
            ->orderBy('price_per_day', 'asc')
            ->take(3)
            ->get();

        return view('customer.dashboard', compact('stats', 'recentBookings', 'recommendedVehicles'));
    }
}
