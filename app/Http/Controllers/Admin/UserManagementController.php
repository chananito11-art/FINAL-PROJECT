<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\GuestProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    // ── Index (tabbed: customers / employees) ─────────────────────────────────
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'customers');

        // Customers tab
        $customers = User::role('customer')
            ->withCount('bookings')
            ->when($request->search, fn($q) => $q->where(function ($sub) use ($request) {
                $sub->where('first_name', 'like', '%' . $request->search . '%')
                    ->orWhere('last_name',  'like', '%' . $request->search . '%')
                    ->orWhere('email',       'like', '%' . $request->search . '%');
            }))
            ->when($request->status && $tab === 'customers', fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(15, ['*'], 'cpage')
            ->withQueryString();

        // Employees tab
        $employees = User::role(['admin', 'super_admin', 'staff'])
            ->with('createdBy')
            ->when($request->search, fn($q) => $q->where(function ($sub) use ($request) {
                $sub->where('first_name', 'like', '%' . $request->search . '%')
                    ->orWhere('last_name',  'like', '%' . $request->search . '%')
                    ->orWhere('email',       'like', '%' . $request->search . '%');
            }))
            ->latest()
            ->paginate(15, ['*'], 'epage')
            ->withQueryString();

        // Walk-ins tab — now using GuestProfile model
        $walkins = GuestProfile::withCount('bookings')
            ->when($request->search, fn($q) => $q->where(function($sub) use ($request) {
                $sub->where('first_name', 'like', '%' . $request->search . '%')
                    ->orWhere('last_name',  'like', '%' . $request->search . '%')
                    ->orWhere('phone',       'like', '%' . $request->search . '%')
                    ->orWhere('email',       'like', '%' . $request->search . '%');
            }))
            ->latest()
            ->paginate(15, ['*'], 'wpage')
            ->withQueryString();

        $roles = Role::whereIn('name', ['admin', 'super_admin', 'staff'])->get();

        return view('admin.users.index', compact('tab', 'customers', 'employees', 'walkins', 'roles'));
    }

    // ── Customer actions ──────────────────────────────────────────────────────
    public function showCustomer(User $user)
    {
        abort_unless($user->hasRole('customer'), 404);
        $bookings = $user->bookings()->with('vehicle')->latest()->paginate(10);
        return view('admin.users.show-customer', compact('user', 'bookings'));
    }

    public function suspendCustomer(Request $request, User $user)
    {
        abort_unless($user->hasRole('customer'), 404);
        
        $request->validate([
            'suspension_reason' => ['required', 'string', 'max:1000']
        ]);

        $user->update([
            'status' => 'suspended',
            'suspension_reason' => $request->suspension_reason
        ]);

        ActivityLog::log("Customer #{$user->id} ({$user->email}) suspended. Reason: {$request->suspension_reason}", User::class, $user->id);
        return back()->with('success', "{$user->first_name} {$user->last_name} has been suspended.");
    }

    public function activateCustomer(User $user)
    {
        abort_unless($user->hasRole('customer'), 404);
        $user->update(['status' => 'active']);
        ActivityLog::log("Customer #{$user->id} ({$user->email}) activated", User::class, $user->id);
        return back()->with('success', "{$user->first_name} {$user->last_name} has been activated.");
    }

    // ── Employee actions ──────────────────────────────────────────────────────
    public function createEmployee()
    {
        $roles = Role::whereIn('name', ['admin', 'super_admin', 'staff'])->get();
        return view('admin.users.create-employee', compact('roles'));
    }

    public function storeEmployee(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:80'],
            'last_name'  => ['required', 'string', 'max:80'],
            'email'      => ['required', 'email', 'unique:users,email'],
            'password'   => ['required', 'min:8', 'confirmed'],
            'role'       => ['required', 'in:admin,super_admin,staff'],
        ]);

        $employee = User::create([
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'],
            'email'      => $validated['email'],
            'password'   => Hash::make($validated['password']),
            'status'     => 'active',
            'created_by' => Auth::id(),
        ]);

        $employee->assignRole($validated['role']);

        ActivityLog::log(
            "Created employee {$employee->email} with role '{$validated['role']}'",
            User::class,
            $employee->id
        );

        return redirect()->route('admin.users.index', ['tab' => 'employees'])
            ->with('success', "Employee {$employee->first_name} {$employee->last_name} created successfully.");
    }

    public function editEmployee(User $user)
    {
        abort_unless($user->hasAnyRole(['admin', 'super_admin', 'staff']), 404);
        $roles = Role::whereIn('name', ['admin', 'super_admin', 'staff'])->get();
        return view('admin.users.edit-employee', compact('user', 'roles'));
    }

    public function updateEmployee(Request $request, User $user)
    {
        abort_unless($user->hasAnyRole(['admin', 'super_admin', 'staff']), 404);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:80'],
            'last_name'  => ['required', 'string', 'max:80'],
            'email'      => ['required', 'email', 'unique:users,email,' . $user->id],
            'role'       => ['required', 'in:admin,super_admin,staff'],
            'password'   => ['nullable', 'min:8', 'confirmed'],
        ]);

        $user->update([
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'],
            'email'      => $validated['email'],
        ]);

        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        $user->syncRoles([$validated['role']]);

        ActivityLog::log("Updated employee {$user->email}", User::class, $user->id);

        return redirect()->route('admin.users.index', ['tab' => 'employees'])
            ->with('success', "Employee {$user->first_name} {$user->last_name} updated.");
    }

    public function deactivateEmployee(User $user)
    {
        abort_unless($user->hasAnyRole(['admin', 'super_admin', 'staff']), 404);
        abort_if($user->id === Auth::id(), 403, 'You cannot deactivate yourself.');

        $user->update(['status' => 'inactive']);
        ActivityLog::log("Deactivated employee {$user->email}", User::class, $user->id);

        return back()->with('success', "Employee {$user->first_name} {$user->last_name} has been deactivated.");
    }

    public function reactivateEmployee(User $user)
    {
        abort_unless($user->hasAnyRole(['admin', 'super_admin', 'staff']), 404);

        $user->update(['status' => 'active']);
        ActivityLog::log("Reactivated employee {$user->email}", User::class, $user->id);

        return back()->with('success', "Employee {$user->first_name} {$user->last_name} has been reactivated.");
    }
}
