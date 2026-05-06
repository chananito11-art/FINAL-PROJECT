<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use App\Models\Vehicle;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index()
    {
        return view('super-admin.reports.index');
    }

    public function revenue(Request $request)
    {
        $dateFrom = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : now()->startOfYear();
        $dateTo   = $request->date_to   ? Carbon::parse($request->date_to)->endOfDay()     : now()->endOfDay();

        $monthly = Payment::where('status', 'verified')
            ->whereBetween('verified_at', [$dateFrom, $dateTo])
            ->select(
                DB::raw("DATE_FORMAT(verified_at, '%Y-%m') as month"),
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $byVehicle = Payment::where('status', 'verified')
            ->whereBetween('verified_at', [$dateFrom, $dateTo])
            ->with('booking.vehicle')
            ->get()
            ->groupBy(fn($p) => $p->booking?->vehicle?->name ?? 'Unknown')
            ->map(fn($g) => ['total' => $g->sum('amount'), 'count' => $g->count()])
            ->sortByDesc('total')
            ->take(10);

        $totalRevenue = $monthly->sum('total');

        return view('super-admin.reports.revenue', compact('monthly', 'byVehicle', 'totalRevenue', 'dateFrom', 'dateTo'));
    }

    public function bookings(Request $request)
    {
        $query = Booking::with(['user', 'vehicle'])->latest();

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->vehicle_id) {
            $query->where('vehicle_id', $request->vehicle_id);
        }
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->customer) {
            $query->whereHas('user', fn($u) =>
                $u->where('first_name', 'like', "%{$request->customer}%")
                  ->orWhere('last_name',  'like', "%{$request->customer}%")
                  ->orWhere('email',      'like', "%{$request->customer}%")
            );
        }

        $bookings = $query->paginate(25)->withQueryString();
        $vehicles = Vehicle::orderBy('name')->get(['id', 'name']);

        return view('super-admin.reports.bookings', compact('bookings', 'vehicles'));
    }

    public function vehicleUtilization(Request $request)
    {
        $dateFrom = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : now()->startOfYear();
        $dateTo   = $request->date_to   ? Carbon::parse($request->date_to)->endOfDay()     : now()->endOfDay();

        $totalDays = $dateFrom->diffInDays($dateTo) ?: 1;

        $vehicles = Vehicle::withCount(['bookings as booking_count' => fn($q) =>
                $q->whereIn('status', ['confirmed', 'ongoing', 'completed'])
                  ->whereBetween('pickup_date', [$dateFrom, $dateTo])
            ])
            ->get()
            ->map(function ($v) use ($dateFrom, $dateTo, $totalDays) {
                $completedBookings = $v->bookings()
                    ->whereIn('status', ['completed', 'ongoing'])
                    ->whereBetween('pickup_date', [$dateFrom, $dateTo])
                    ->get();

                $daysRented = $completedBookings->sum(fn($b) => $b->pickup_date->diffInDays($b->return_date) ?: 1);
                $revenue    = Payment::whereIn('booking_id', $completedBookings->pluck('id'))
                    ->where('status', 'verified')
                    ->sum('amount');

                $v->days_rented      = $daysRented;
                $v->utilization_pct  = $totalDays > 0 ? round(($daysRented / $totalDays) * 100, 1) : 0;
                $v->total_revenue    = $revenue;
                return $v;
            })
            ->sortByDesc('total_revenue');

        return view('super-admin.reports.vehicles', compact('vehicles', 'dateFrom', 'dateTo'));
    }

    public function customers(Request $request)
    {
        $customers = User::role('customer')
            ->withCount('bookings')
            ->with(['bookings' => fn($q) => $q->latest()->take(1)])
            ->get()
            ->map(function ($u) {
                $u->total_spent   = Payment::whereHas('booking', fn($b) => $b->where('user_id', $u->id))
                    ->where('status', 'verified')
                    ->sum('amount');
                $u->last_booking  = $u->bookings->first();
                return $u;
            })
            ->sortByDesc('total_spent');

        return view('super-admin.reports.customers', compact('customers'));
    }

    // ── PDF Exports ────────────────────────────────────────────────────────────

    public function exportRevenuePdf(Request $request)
    {
        $dateFrom = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : now()->startOfYear();
        $dateTo   = $request->date_to   ? Carbon::parse($request->date_to)->endOfDay()     : now()->endOfDay();

        $monthly = Payment::where('status', 'verified')
            ->whereBetween('verified_at', [$dateFrom, $dateTo])
            ->select(
                DB::raw("DATE_FORMAT(verified_at, '%Y-%m') as month"),
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $totalRevenue = $monthly->sum('total');

        $pdf = Pdf::loadView('super-admin.reports.pdf.revenue', compact('monthly', 'totalRevenue', 'dateFrom', 'dateTo'));
        return $pdf->download('revenue-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportBookingsPdf(Request $request)
    {
        $bookings = Booking::with(['user', 'vehicle'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to,   fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->latest()
            ->get();

        $pdf = Pdf::loadView('super-admin.reports.pdf.bookings', compact('bookings'));
        $pdf->setPaper('a4', 'landscape');
        return $pdf->download('bookings-report-' . now()->format('Y-m-d') . '.pdf');
    }

    // ── CSV Exports ────────────────────────────────────────────────────────────

    public function exportRevenueCsv(Request $request): StreamedResponse
    {
        $dateFrom = $request->date_from ? Carbon::parse($request->date_from) : now()->startOfYear();
        $dateTo   = $request->date_to   ? Carbon::parse($request->date_to)   : now();

        $data = Payment::where('status', 'verified')
            ->whereBetween('verified_at', [$dateFrom, $dateTo])
            ->with('booking.vehicle')
            ->get();

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Payment ID', 'Booking ID', 'Vehicle', 'Amount', 'Reference', 'GCash Ref #', 'Verified At']);
            foreach ($data as $p) {
                fputcsv($handle, [
                    $p->id,
                    $p->booking_id,
                    $p->booking?->vehicle?->name,
                    $p->amount,
                    $p->reference_code,
                    $p->gcash_transaction_reference_number,
                    $p->verified_at?->format('Y-m-d H:i'),
                ]);
            }
            fclose($handle);
        }, 'revenue-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function exportBookingsCsv(Request $request): StreamedResponse
    {
        $bookings = Booking::with(['user', 'vehicle'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to,   fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->latest()
            ->get();

        return response()->streamDownload(function () use ($bookings) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Booking ID', 'Customer', 'Vehicle', 'Pickup', 'Return', 'Amount', 'Status', 'Created At']);
            foreach ($bookings as $b) {
                fputcsv($handle, [
                    $b->id,
                    $b->user?->first_name . ' ' . $b->user?->last_name,
                    $b->vehicle?->name,
                    $b->pickup_date?->format('Y-m-d'),
                    $b->return_date?->format('Y-m-d'),
                    $b->total_amount,
                    $b->status,
                    $b->created_at?->format('Y-m-d H:i'),
                ]);
            }
            fclose($handle);
        }, 'bookings-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function exportVehiclesCsv(Request $request): StreamedResponse
    {
        $dateFrom = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : now()->startOfYear();
        $dateTo   = $request->date_to   ? Carbon::parse($request->date_to)->endOfDay()     : now()->endOfDay();
        $totalDays = $dateFrom->diffInDays($dateTo) ?: 1;

        $vehicles = Vehicle::get()->map(function ($v) use ($dateFrom, $dateTo, $totalDays) {
            $completedBookings = $v->bookings()
                ->whereIn('status', ['completed', 'ongoing'])
                ->whereBetween('pickup_date', [$dateFrom, $dateTo])
                ->get();
            $daysRented = $completedBookings->sum(fn($b) => $b->pickup_date->diffInDays($b->return_date) ?: 1);
            $revenue    = Payment::whereIn('booking_id', $completedBookings->pluck('id'))->where('status', 'verified')->sum('amount');
            $v->days_rented     = $daysRented;
            $v->utilization_pct = $totalDays > 0 ? round(($daysRented / $totalDays) * 100, 1) : 0;
            $v->total_revenue   = $revenue;
            return $v;
        });

        return response()->streamDownload(function () use ($vehicles) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Vehicle', 'Brand', 'Model', 'Year', 'Days Rented', 'Utilization %', 'Revenue (₱)']);
            foreach ($vehicles as $v) {
                fputcsv($handle, [$v->name, $v->brand, $v->model, $v->year, $v->days_rented, $v->utilization_pct, $v->total_revenue]);
            }
            fclose($handle);
        }, 'vehicle-utilization-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function exportCustomersCsv(): StreamedResponse
    {
        $customers = User::role('customer')
            ->withCount('bookings')
            ->get()
            ->map(function ($u) {
                $u->total_spent = Payment::whereHas('booking', fn($b) => $b->where('user_id', $u->id))
                    ->where('status', 'verified')->sum('amount');
                $u->last_booking = $u->bookings()->latest()->value('created_at');
                return $u;
            });

        return response()->streamDownload(function () use ($customers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Name', 'Email', 'Phone', 'Status', 'Total Bookings', 'Total Spent (₱)', 'Last Booking', 'Joined']);
            foreach ($customers as $c) {
                fputcsv($handle, [
                    $c->first_name . ' ' . $c->last_name,
                    $c->email,
                    $c->phone,
                    $c->status,
                    $c->bookings_count,
                    $c->total_spent,
                    $c->last_booking ? Carbon::parse($c->last_booking)->format('Y-m-d') : 'N/A',
                    $c->created_at?->format('Y-m-d'),
                ]);
            }
            fclose($handle);
        }, 'customers-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv']);
    }
}
