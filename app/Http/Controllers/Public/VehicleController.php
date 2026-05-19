<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Vehicle;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehicle::available();

        // Date Filter
        if ($request->filled(['pickup_date', 'return_date'])) {
            try {
                $pickup = \Carbon\Carbon::parse($request->pickup_date);
                $return = \Carbon\Carbon::parse($request->return_date);

                if ($pickup->lte($return)) {
                    // Filter vehicles that are available for these dates
                    $query->whereDoesntHave('bookings', function ($q) use ($pickup, $return) {
                        $q->whereIn('status', ['awaiting_approval', 'pending_payment', 'awaiting_verification', 'confirmed', 'ongoing'])
                          ->where(function($sub) use ($pickup, $return) {
                              $sub->where('pickup_date', '<=', $return)
                                  ->whereRaw('DATE_ADD(return_date, INTERVAL 1 DAY) >= ?', [$pickup->toDateString()]);
                          });
                    });
                }
            } catch (\Exception $e) {
                // Ignore invalid dates
            }
        }

        // Type Filter
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Capacity Filter
        if ($request->filled('capacity')) {
            $query->where('capacity', '>=', $request->capacity);
        }

        $vehicles = $query->orderBy('name')->get();

        return view('public.vehicles.index', compact('vehicles'));
    }

    public function show($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        return view('public.vehicles.show', compact('vehicle'));
    }

    public function availability(Vehicle $vehicle): JsonResponse
    {
        $bookings = Booking::where('vehicle_id', $vehicle->id)
            ->whereIn('status', ['awaiting_approval', 'pending_payment', 'awaiting_verification', 'confirmed', 'ongoing'])
            ->get(['pickup_date', 'return_date', 'status']);
 
        $ranges = $bookings->map(function($b) {
            $realReturn = \Carbon\Carbon::parse($b->return_date);
            return [
                'pickup_date' => $b->pickup_date->toDateString(),
                'return_date' => $realReturn->toDateString(),
                'buffer_date' => $realReturn->copy()->addDay()->toDateString(),
                'status' => $b->status
            ];
        });
 
        return response()->json($ranges);
    }
}
