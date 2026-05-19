<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Booking;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DispatchCalendarController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->has('start') ? Carbon::parse($request->start) : now()->startOfWeek();
        $end = $start->copy()->addDays(14); // Show 2 weeks by default

        $days = [];
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $days[] = $date->copy();
        }

        $vehicles = Vehicle::with(['bookings' => function($q) use ($start, $end) {
            $q->where(function($sub) use ($start, $end) {
                $sub->whereBetween('pickup_date', [$start, $end])
                    ->orWhereBetween('return_date', [$start, $end])
                    ->orWhere(function($ss) use ($start, $end) {
                        $ss->where('pickup_date', '<', $start)
                           ->where('return_date', '>', $end);
                    });
            })->whereNotIn('status', ['cancelled', 'rejected']);
        }])->get();

        return view('admin.dispatch.index', compact('vehicles', 'days', 'start', 'end'));
    }
}
