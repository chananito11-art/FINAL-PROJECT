<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\ActivityLog;

class AuthController extends Controller
{
    // ── Login ─────────────────────────────────────────────────────────────────
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $defaultAdminEmail = env('DEFAULT_ADMIN_EMAIL', 'admin@orangecrush.com');
        $defaultAdminPassword = env('DEFAULT_ADMIN_PASSWORD', 'password');

        if ((!$this->hasUsers() || !User::where('email', $defaultAdminEmail)->exists())
            && $credentials['email'] === $defaultAdminEmail
            && $credentials['password'] === $defaultAdminPassword) {
            $this->ensureDefaultRoles();

            $user = User::firstOrCreate(
                ['email' => $defaultAdminEmail],
                [
                    'first_name' => 'Admin',
                    'last_name' => 'User',
                    'password' => Hash::make($defaultAdminPassword),
                    'phone' => '09000000002',
                    'status' => 'active',
                ]
            );

            $user->assignRole('admin');
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            $user->update(['last_login_at' => now()]);
            ActivityLog::log("Default admin signed in: {$user->email}", User::class, $user->id);

            return $this->redirectByRole($user);
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            if ($user->getRoleNames()->isEmpty()) {
                $this->ensureDefaultRoles();
                $user->assignRole('customer');
            }

            // Block suspended customers
            if ($user->hasRole('customer') && $user->status === 'suspended') {
                $reason = $user->suspension_reason ? " Reason: {$user->suspension_reason}" : "";
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors(['email' => "Your account has been suspended.{$reason} Please contact support."])->onlyInput('email');
            }

            // Block inactive employees
            if ($user->hasAnyRole(['admin', 'super_admin']) && $user->status === 'inactive') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors(['email' => 'Your employee account has been deactivated.'])->onlyInput('email');
            }

            $user->update(['last_login_at' => now()]);
            ActivityLog::log("User logged in: {$user->email}", User::class, $user->id);
            return $this->redirectByRole($user);
        }

        return back()
            ->withErrors(['email' => 'These credentials do not match our records.'])
            ->onlyInput('email');
    }

    // ── Register ──────────────────────────────────────────────────────────────
    public function showRegisterForm()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:80'],
            'last_name'  => ['required', 'string', 'max:80'],
            'email'      => ['required', 'email', 'unique:users,email'],
            'phone'      => ['nullable', 'string', 'max:20'],
            'password'   => ['required', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'],
            'email'      => $validated['email'],
            'phone'      => $validated['phone'] ?? null,
            'password'   => Hash::make($validated['password']),
        ]);

        $this->ensureDefaultRoles();
        $user->assignRole('customer');

        Auth::login($user);
        $request->session()->regenerate();

        ActivityLog::log("New customer registered: {$user->email}", User::class, $user->id);

        // Redirect to intended booking page or home
        return redirect()->intended(route('vehicles.index'));
    }

    // ── Logout ────────────────────────────────────────────────────────────────
    public function logout(Request $request)
    {
        ActivityLog::log("User logged out: " . Auth::user()?->email);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    private function hasUsers(): bool
    {
        return User::query()->exists();
    }

    private function ensureDefaultRoles(): void
    {
        Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    }

    // ── Role redirect ─────────────────────────────────────────────────────────
    private function redirectByRole($user)
    {
        if ($user->isSuperAdmin()) {
            return redirect()->intended(route('super-admin.users.index'));
        }
        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }
        // customer
        return redirect()->intended(route('customer.dashboard'));
    }
}
