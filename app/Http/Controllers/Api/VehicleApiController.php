<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehicle::query();

        // Check if availability status is 'available' by default (or let it query from available vehicles)
        $query->where('status', 'available');

        // Date Filter
        if ($request->filled(['pickup_date', 'return_date'])) {
            try {
                $pickup = \Carbon\Carbon::parse($request->pickup_date);
                $return = \Carbon\Carbon::parse($request->return_date);

                if ($pickup->lte($return)) {
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
        if ($request->filled('type') && $request->type !== 'All') {
            $query->where('type', $request->type);
        }

        // Capacity Filter
        if ($request->filled('capacity')) {
            $query->where('capacity', '>=', $request->capacity);
        }

        $vehicles = $query->orderBy('name')->get()->map(function ($vehicle) {
            return [
                'id' => $vehicle->id,
                'name' => $vehicle->name,
                'brand' => $vehicle->brand,
                'model' => $vehicle->model,
                'year' => $vehicle->year,
                'type' => $vehicle->type,
                'transmission' => $vehicle->transmission,
                'fuel' => $vehicle->fuel,
                'capacity' => $vehicle->capacity,
                'price_per_day' => (float)$vehicle->price_per_day,
                'image_url' => $vehicle->image_url,
                'odometer' => $vehicle->odometer,
                'status' => $vehicle->status,
            ];
        });

        return response()->json($vehicles);
    }
}
