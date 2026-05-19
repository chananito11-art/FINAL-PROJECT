<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\MaintenanceLog;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $upcomingPM = Vehicle::where('status', '!=', 'unavailable')
            ->where(function($q) {
                $q->whereHas('maintenanceLogs', function($sub) {
                    $sub->whereNotNull('next_service_due_mileage')
                        ->whereRaw('next_service_due_mileage <= odometer + 500');
                })
                ->orWhereHas('maintenanceLogs', function($sub) {
                    $sub->whereNotNull('next_service_due_date')
                        ->where('next_service_due_date', '<=', now()->addDays(30));
                });
            })->with('maintenanceLogs')->get();

        $vehicles = Vehicle::orderBy('name')->get(); // All statuses for status control panel
        $logs = MaintenanceLog::with('vehicle')->latest('service_date')->paginate(15);

        return view('admin.maintenance.index', compact('upcomingPM', 'vehicles', 'logs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id'               => ['required', 'exists:vehicles,id'],
            'service_type'             => ['required', 'string', 'max:100'],
            'description'              => ['nullable', 'string'],
            'mileage_at_service'       => ['required', 'integer', 'min:0'],
            'service_date'             => ['required', 'date'],
            'cost'                     => ['nullable', 'numeric', 'min:0'],
            'next_service_due_mileage' => ['nullable', 'integer', 'gt:mileage_at_service'],
            'next_service_due_date'    => ['nullable', 'date', 'after:service_date'],
            'performed_by'             => ['nullable', 'string', 'max:100'],
            'checklist'                => ['nullable', 'array'],
            'checklist.*'              => ['string'],
        ]);

        // Append checklist items to description
        if (!empty($validated['checklist'])) {
            $checklistText = 'PM Checklist: ' . implode(', ', $validated['checklist']);
            $validated['description'] = trim(($validated['description'] ?? '') . "\n" . $checklistText);
        }
        unset($validated['checklist']);

        $log = MaintenanceLog::create($validated);

        // Update vehicle odometer if the service mileage is higher
        $vehicle = Vehicle::find($validated['vehicle_id']);
        if ($validated['mileage_at_service'] > $vehicle->odometer) {
            $vehicle->update(['odometer' => $validated['mileage_at_service']]);
        }

        ActivityLog::log("Maintenance logged for {$vehicle->name}: {$validated['service_type']}", MaintenanceLog::class, $log->id);

        return back()->with('success', 'Maintenance record added successfully.');
    }

    public function show(Vehicle $vehicle)
    {
        $vehicle->load(['maintenanceLogs' => fn($q) => $q->latest('service_date')]);
        return view('admin.maintenance.show', compact('vehicle'));
    }

    public function sendToMaintenance(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        if ($vehicle->status === 'rented') {
            return back()->with('error', 'Cannot send a currently rented vehicle to maintenance.');
        }

        $vehicle->update(['status' => 'unavailable']);

        ActivityLog::log(
            "Vehicle {$vehicle->name} sent to maintenance. Reason: {$request->reason}",
            Vehicle::class,
            $vehicle->id
        );

        return back()->with('success', "{$vehicle->name} is now marked as Under Maintenance. It will not appear in booking searches.");
    }

    public function releaseFromMaintenance(Request $request, Vehicle $vehicle)
    {
        if ($vehicle->status !== 'unavailable') {
            return back()->with('error', 'Vehicle is not currently under maintenance.');
        }

        $vehicle->update(['status' => 'available']);

        ActivityLog::log(
            "Vehicle {$vehicle->name} released from maintenance and marked available.",
            Vehicle::class,
            $vehicle->id
        );

        return back()->with('success', "{$vehicle->name} is now available for booking.");
    }
}
