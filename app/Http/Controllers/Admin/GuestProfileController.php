<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\GuestProfile;
use Illuminate\Http\Request;

class GuestProfileController extends Controller
{
    /**
     * AJAX search endpoint — returns matching guest profiles as JSON.
     * Used by the walk-in booking form live search.
     */
    public function search(Request $request)
    {
        $q = trim($request->get('q', ''));

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $results = GuestProfile::where(function ($query) use ($q) {
                $query->where('first_name', 'like', "%{$q}%")
                      ->orWhere('last_name',  'like', "%{$q}%")
                      ->orWhere('phone',       'like', "%{$q}%")
                      ->orWhere('email',       'like', "%{$q}%")
                      ->orWhereRaw("CONCAT(first_name,' ',last_name) LIKE ?", ["%{$q}%"]);
            })
            ->withCount('bookings')
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get(['id','first_name','last_name','email','phone','drivers_license_number'])
            ->map(fn($g) => [
                'id'                     => $g->id,
                'first_name'             => $g->first_name,
                'last_name'              => $g->last_name,
                'email'                  => $g->email,
                'phone'                  => $g->phone,
                'drivers_license_number' => $g->drivers_license_number,
                'bookings_count'         => $g->bookings_count,
                'label'                  => "{$g->first_name} {$g->last_name}" . ($g->phone ? " · {$g->phone}" : ''),
            ]);

        return response()->json($results);
    }

    /**
     * Show a guest profile and all their bookings.
     */
    public function show(GuestProfile $guest)
    {
        $guest->load(['bookings' => fn($q) => $q->with('vehicle')->latest()]);
        return view('admin.guests.show', compact('guest'));
    }

    /**
     * Store a new guest profile (standalone, from the directory).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name'             => ['required', 'string', 'max:80'],
            'last_name'              => ['required', 'string', 'max:80'],
            'email'                  => ['nullable', 'email', 'max:120'],
            'phone'                  => ['nullable', 'string', 'max:20'],
            'drivers_license_number' => ['nullable', 'string', 'max:50'],
            'notes'                  => ['nullable', 'string', 'max:500'],
        ]);

        $guest = GuestProfile::create($validated);

        ActivityLog::log("Guest profile created: {$guest->full_name}", GuestProfile::class, $guest->id);

        return back()->with('success', "Guest profile for {$guest->full_name} created successfully.");
    }

    /**
     * Update a guest profile.
     */
    public function update(Request $request, GuestProfile $guest)
    {
        $validated = $request->validate([
            'first_name'             => ['required', 'string', 'max:80'],
            'last_name'              => ['required', 'string', 'max:80'],
            'email'                  => ['nullable', 'email', 'max:120'],
            'phone'                  => ['nullable', 'string', 'max:20'],
            'drivers_license_number' => ['nullable', 'string', 'max:50'],
            'notes'                  => ['nullable', 'string', 'max:500'],
        ]);

        $guest->update($validated);

        ActivityLog::log("Guest profile updated: {$guest->full_name}", GuestProfile::class, $guest->id);

        return back()->with('success', 'Guest profile updated.');
    }
}
