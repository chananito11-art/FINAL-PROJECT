<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $users  = User::with('roles')
            ->when($search, fn($q) => $q->where('email', 'like', "%{$search}%")
                ->orWhere('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%"))
            ->latest()
            ->paginate(20);

        $roles = Role::orderBy('name')->get();

        return view('super-admin.users.index', compact('users', 'roles', 'search'));
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate(['role' => ['required', 'in:customer,admin,super_admin']]);

        $oldRole = $user->getRoleNames()->first() ?? 'none';
        $user->syncRoles([$request->role]);

        ActivityLog::log(
            "User role changed: {$user->email} from {$oldRole} to {$request->role}",
            User::class,
            $user->id
        );

        return back()->with('success', "Role updated to {$request->role}.");
    }

    public function destroy(User $user)
    {
        ActivityLog::log("User deleted: {$user->email}", User::class, $user->id);
        $user->delete();
        return back()->with('success', 'User deleted.');
    }
}
