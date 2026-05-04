<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use App\Models\Vehicle;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'vehicles'              => Vehicle::count(),
            'available_vehicles'    => Vehicle::available()->count(),
            'bookings'              => Booking::count(),
            'pending_verification'  => Booking::where('status', Booking::STATUS_AWAITING_VERIFICATION)->count(),
            'confirmed'             => Booking::where('status', Booking::STATUS_CONFIRMED)->count(),
            'ongoing'               => Booking::where('status', Booking::STATUS_ONGOING)->count(),
            'customers'             => User::role('customer')->count(),
            'revenue'               => Payment::where('status', 'verified')->sum('amount'),
        ];

        $recentBookings = Booking::with(['user', 'vehicle'])
            ->latest()
            ->take(8)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentBookings'));
    }
}
