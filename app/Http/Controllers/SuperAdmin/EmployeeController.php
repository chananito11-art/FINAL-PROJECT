<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = User::role(['admin', 'super_admin'])
            ->with('createdBy')
            ->latest()
            ->paginate(20);

        return view('super-admin.employees.index', compact('employees'));
    }

    public function create()
    {
        $roles = Role::whereIn('name', ['admin', 'super_admin'])->get();
        return view('super-admin.employees.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:80'],
            'last_name'  => ['required', 'string', 'max:80'],
            'email'      => ['required', 'email', 'unique:users,email'],
            'password'   => ['required', 'min:8', 'confirmed'],
            'role'       => ['required', 'in:admin,super_admin'],
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

        return redirect()->route('super-admin.employees.index')
            ->with('success', "Employee {$employee->first_name} {$employee->last_name} created.");
    }

    public function edit(User $user)
    {
        abort_unless($user->hasAnyRole(['admin', 'super_admin']), 404);
        $roles = Role::whereIn('name', ['admin', 'super_admin'])->get();
        return view('super-admin.employees.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        abort_unless($user->hasAnyRole(['admin', 'super_admin']), 404);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:80'],
            'last_name'  => ['required', 'string', 'max:80'],
            'email'      => ['required', 'email', 'unique:users,email,' . $user->id],
            'role'       => ['required', 'in:admin,super_admin'],
        ]);

        $user->update([
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'],
            'email'      => $validated['email'],
        ]);

        $user->syncRoles([$validated['role']]);

        ActivityLog::log("Updated employee {$user->email}", User::class, $user->id);

        return redirect()->route('super-admin.employees.index')
            ->with('success', "Employee {$user->first_name} {$user->last_name} updated.");
    }

    public function deactivate(User $user)
    {
        abort_unless($user->hasAnyRole(['admin', 'super_admin']), 404);
        abort_if($user->id === Auth::id(), 403, 'You cannot deactivate yourself.');

        $user->update(['status' => 'inactive']);

        ActivityLog::log("Deactivated employee {$user->email}", User::class, $user->id);

        return back()->with('success', "Employee {$user->first_name} {$user->last_name} deactivated.");
    }
}
