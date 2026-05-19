<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = $this->getStats();

        $recentActivity = ActivityLog::with('user')
            ->latest()
            ->take(10)
            ->get();

        $quickCounts = [
            'awaiting_approval' => Booking::where('status', Booking::STATUS_AWAITING_APPROVAL)->count(),
            'pending_payments'  => Payment::where('status', 'pending')->count(),
            'awaiting_verify'   => Booking::where('status', Booking::STATUS_AWAITING_VERIFICATION)->count(),
            'returns_today'     => Booking::where('status', Booking::STATUS_ONGOING)
                                    ->whereDate('return_date', today())->count(),
        ];

        return view('admin.dashboard', compact('stats', 'recentActivity', 'quickCounts'));
    }

    private function getStats(): array
    {
        return [
            'total_revenue'        => Payment::where('status', 'verified')->sum('amount'),
            'revenue_this_month'   => Payment::where('status', 'verified')
                                       ->whereMonth('verified_at', now()->month)
                                       ->whereYear('verified_at', now()->year)
                                       ->sum('amount'),
            'total_vehicles'       => Vehicle::count(),
            'available_vehicles'   => Vehicle::where('status', 'available')->count(),
            'total_customers'      => User::role('customer')->count(),
            'awaiting_approval'    => Booking::where('status', Booking::STATUS_AWAITING_APPROVAL)->count(),
            'active_bookings'      => Booking::whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_ONGOING])->count(),
            'pending_verification' => Booking::where('status', Booking::STATUS_AWAITING_VERIFICATION)->count(),
            'cancelled_this_month' => Booking::where('status', Booking::STATUS_CANCELLED)
                                       ->whereMonth('cancelled_at', now()->month)
                                       ->whereYear('cancelled_at', now()->year)
                                       ->count(),
        ];
    }

    public function revenueChart(): JsonResponse
    {
        $data = Payment::where('status', 'verified')
            ->where('verified_at', '>=', now()->subMonths(6)->startOfMonth())
            ->select(
                DB::raw("DATE_FORMAT(verified_at, '%Y-%m') as month"),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json([
            'labels' => $data->pluck('month')->map(fn($m) => date('M Y', strtotime($m . '-01'))),
            'data'   => $data->pluck('total'),
        ]);
    }

    public function bookingChart(): JsonResponse
    {
        $counts = Booking::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->orderBy('count', 'desc')
            ->get();

        $labels = $counts->map(fn($c) => ucwords(str_replace('_', ' ', $c->status)));
        $data   = $counts->pluck('count');

        return response()->json(compact('labels', 'data'));
    }

    public function vehicleChart(): JsonResponse
    {
        $data = Booking::select('vehicle_id', DB::raw('COUNT(*) as bookings'))
            ->with('vehicle:id,name')
            ->whereNotNull('vehicle_id')
            ->groupBy('vehicle_id')
            ->orderByDesc('bookings')
            ->take(5)
            ->get();

        return response()->json([
            'labels' => $data->map(fn($b) => $b->vehicle?->name ?? 'Unknown'),
            'data'   => $data->pluck('bookings'),
        ]);
    }

    public function bookingTimeline(): JsonResponse
    {
        $data = Booking::where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->select(
                DB::raw("DATE(created_at) as date"),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'labels' => $data->pluck('date'),
            'data'   => $data->pluck('count'),
        ]);
    }
}
