<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::with('category')
            ->available()
            ->orderBy('name')
            ->get();

        $categories = Category::orderBy('category_name')->get();

        return view('public.vehicles.index', compact('vehicles', 'categories'));
    }

    public function show($id)
    {
        $vehicle = Vehicle::with('category')->findOrFail($id);
        return view('public.vehicles.show', compact('vehicle'));
    }

    public function availability(Vehicle $vehicle): JsonResponse
    {
        $bookedRanges = Booking::where('vehicle_id', $vehicle->id)
            ->whereIn('status', ['confirmed', 'awaiting_verification', 'ongoing'])
            ->get(['pickup_date', 'return_date']);

        return response()->json($bookedRanges);
    }
}
