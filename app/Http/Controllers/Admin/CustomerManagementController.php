<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = User::role('customer')
            ->withCount('bookings')
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('first_name', 'like', '%' . $request->search . '%')
                        ->orWhere('last_name',  'like', '%' . $request->search . '%')
                        ->orWhere('email',       'like', '%' . $request->search . '%');
                });
            })
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.customers.index', compact('query'));
    }

    public function show(User $user)
    {
        abort_unless($user->hasRole('customer'), 404);

        $bookings = $user->bookings()
            ->with('vehicle')
            ->latest()
            ->paginate(10);

        return view('admin.customers.show', compact('user', 'bookings'));
    }

    public function suspend(User $user)
    {
        abort_unless($user->hasRole('customer'), 404);

        $user->update(['status' => 'suspended']);
        ActivityLog::log("Customer #{$user->id} ({$user->email}) suspended", User::class, $user->id);

        return back()->with('success', "Customer {$user->first_name} {$user->last_name} has been suspended.");
    }

    public function activate(User $user)
    {
        abort_unless($user->hasRole('customer'), 404);

        $user->update(['status' => 'active']);
        ActivityLog::log("Customer #{$user->id} ({$user->email}) activated", User::class, $user->id);

        return back()->with('success', "Customer {$user->first_name} {$user->last_name} has been activated.");
    }
}
