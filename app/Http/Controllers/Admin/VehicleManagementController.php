<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleManagementController extends Controller
{
    public function index()
    {
        $vehicles   = Vehicle::with('category')->latest()->paginate(15);
        $categories = Category::orderBy('category_name')->get();
        return view('admin.vehicles.index', compact('vehicles', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:100'],
            'category_id'   => ['nullable', 'exists:categories,id'],
            'brand'         => ['nullable', 'string', 'max:60'],
            'model'         => ['nullable', 'string', 'max:60'],
            'year'          => ['nullable', 'integer', 'min:1990', 'max:2030'],
            'plate_number'  => ['nullable', 'string', 'max:20', 'unique:vehicles,plate_number'],
            'type'          => ['required', 'in:Sedan,SUV,Pickup Truck,Van,Hatchback,Crossover'],
            'transmission'  => ['required', 'in:Automatic,Manual'],
            'fuel'          => ['required', 'in:Gasoline,Diesel,Electric,Hybrid'],
            'capacity'      => ['required', 'integer', 'min:1', 'max:20'],
            'price_per_day' => ['required', 'numeric', 'min:0'],
            'status'        => ['required', 'in:available,rented,maintenance,unavailable'],
            'description'   => ['nullable', 'string'],
            'image'         => ['nullable', 'image', 'max:3072'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('vehicles', 'public');
        }

        $vehicle = Vehicle::create($validated);
        ActivityLog::log("Vehicle added: {$vehicle->name}", Vehicle::class, $vehicle->id);

        return back()->with('success', 'Vehicle added successfully.');
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:100'],
            'category_id'   => ['nullable', 'exists:categories,id'],
            'brand'         => ['nullable', 'string', 'max:60'],
            'model'         => ['nullable', 'string', 'max:60'],
            'year'          => ['nullable', 'integer', 'min:1990', 'max:2030'],
            'plate_number'  => ['nullable', 'string', 'max:20', "unique:vehicles,plate_number,{$vehicle->id}"],
            'type'          => ['required', 'in:Sedan,SUV,Pickup Truck,Van,Hatchback,Crossover'],
            'transmission'  => ['required', 'in:Automatic,Manual'],
            'fuel'          => ['required', 'in:Gasoline,Diesel,Electric,Hybrid'],
            'capacity'      => ['required', 'integer', 'min:1', 'max:20'],
            'price_per_day' => ['required', 'numeric', 'min:0'],
            'status'        => ['required', 'in:available,rented,maintenance,unavailable'],
            'description'   => ['nullable', 'string'],
            'image'         => ['nullable', 'image', 'max:3072'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('vehicles', 'public');
        }

        $vehicle->update($validated);
        ActivityLog::log("Vehicle updated: {$vehicle->name}", Vehicle::class, $vehicle->id);

        return back()->with('success', 'Vehicle updated.');
    }

    public function destroy(Vehicle $vehicle)
    {
        ActivityLog::log("Vehicle deleted: {$vehicle->name}", Vehicle::class, $vehicle->id);
        $vehicle->delete();
        return back()->with('success', 'Vehicle deleted.');
    }
}
