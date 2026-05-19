<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Inspection;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InspectionController extends Controller
{
    public function create(Booking $booking, Request $request)
    {
        $type = $request->query('type', 'pickup');
        return view('admin.inspections.create', compact('booking', 'type'));
    }

    public function store(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'type'               => ['required', 'in:pickup,return'],
            'odometer_reading'   => ['required', 'integer', 'min:0'],
            'fuel_level'         => ['required', 'integer', 'min:0', 'max:100'],
            'exterior_condition' => ['required', 'string'],
            'interior_condition' => ['required', 'string'],
            'notes'              => ['nullable', 'string'],
            'images.*'           => ['nullable', 'image', 'max:5120'],
        ]);

        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $images[] = $file->store('inspections', 'public');
            }
        }

        $inspection = Inspection::create([
            'booking_id'         => $booking->id,
            'type'               => $validated['type'],
            'odometer_reading'   => $validated['odometer_reading'],
            'fuel_level'         => $validated['fuel_level'],
            'exterior_condition' => $validated['exterior_condition'],
            'interior_condition' => $validated['interior_condition'],
            'notes'              => $validated['notes'],
            'images_paths'       => $images,
            'recorded_by'        => Auth::id(),
        ]);

        // Update vehicle odometer
        $booking->vehicle->update(['odometer' => $validated['odometer_reading']]);

        ActivityLog::log("Inspection ({$validated['type']}) recorded for booking #{$booking->id}", Inspection::class, $inspection->id);

        return redirect()->route('admin.bookings.show', $booking)->with('success', ucfirst($validated['type']) . ' inspection recorded.');
    }
}
