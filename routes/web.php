<?php

use Illuminate\Support\Facades\Route;
use App\Models\Vehicle;
use App\Models\Category;

use App\Http\Controllers\AuthController;

// Public namespace
use App\Http\Controllers\Public\VehicleController as PublicVehicleController;

// Customer namespace
use App\Http\Controllers\Customer\BookingController as CustomerBookingController;
use App\Http\Controllers\Customer\PaymentController as CustomerPaymentController;
use App\Http\Controllers\Customer\TrackingController as CustomerTrackingController;

// Admin namespace
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\BookingManagementController;
use App\Http\Controllers\Admin\CustomerManagementController;
use App\Http\Controllers\Admin\VehicleManagementController;
use App\Http\Controllers\Admin\PaymentVerificationController;
use App\Http\Controllers\Admin\ReturnProcessingController;
use App\Http\Controllers\Admin\ActivityLogController as AdminActivityLogController;
use App\Http\Controllers\Admin\UserManagementController as AdminUserManagementController;

// Super Admin namespace
use App\Http\Controllers\SuperAdmin\TermsController;
use App\Http\Controllers\SuperAdmin\UserManagementController;
use App\Http\Controllers\SuperAdmin\EmployeeController;
use App\Http\Controllers\SuperAdmin\ReportController;

// ─── Auth Routes ──────────────────────────────────────────────────────────────
Route::get('/login',    [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login',   [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register',[AuthController::class, 'register']);
Route::post('/logout',  [AuthController::class, 'logout'])->name('logout');

// ─── Public Routes ────────────────────────────────────────────────────────────
Route::get('/', function () {
    $vehicles   = Vehicle::with('category')->available()->orderBy('name')->take(6)->get();
    $categories = Category::orderBy('category_name')->get();
    $stats = [
        'vehicles'  => Vehicle::count(),
        'customers' => \App\Models\User::role('customer')->count(),
        'bookings'  => \App\Models\Booking::count(),
        'revenue'   => \App\Models\Payment::where('status','verified')->sum('amount'),
    ];
    return view('welcome', compact('vehicles', 'categories', 'stats'));
});

Route::get('/vehicles',                          [PublicVehicleController::class, 'index'])->name('vehicles.index');
Route::get('/vehicles/{id}',                     [PublicVehicleController::class, 'show'])->name('vehicles.show');
Route::get('/vehicles/{vehicle}/availability',   [PublicVehicleController::class, 'availability'])->name('vehicles.availability');

// ─── Customer Routes ──────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:customer|admin|super_admin'])->group(function () {
    Route::get('/customer/booking/create',          [CustomerBookingController::class, 'create'])->name('customer.booking.create');
    Route::post('/customer/booking',                [CustomerBookingController::class, 'store'])->name('customer.booking.store');
    Route::post('/customer/bookings/{booking}/cancel', [CustomerBookingController::class, 'cancel'])->name('customer.bookings.cancel');

    Route::get('/customer/payment/{booking}',       [CustomerPaymentController::class, 'show'])->name('customer.payment.show');
    Route::post('/customer/payment/{booking}',      [CustomerPaymentController::class, 'store'])->name('customer.payment.store');

    Route::get('/customer/tracking',                [CustomerTrackingController::class, 'index'])->name('customer.tracking.index');
    Route::get('/customer/tracking/{booking}',      [CustomerTrackingController::class, 'show'])->name('customer.tracking.show');
});

// ─── Admin Routes ─────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:admin|super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Dashboard chart endpoints
    Route::get('/dashboard/revenue-chart',    [AdminDashboardController::class, 'revenueChart'])->name('dashboard.revenue-chart');
    Route::get('/dashboard/booking-chart',    [AdminDashboardController::class, 'bookingChart'])->name('dashboard.booking-chart');
    Route::get('/dashboard/vehicle-chart',    [AdminDashboardController::class, 'vehicleChart'])->name('dashboard.vehicle-chart');
    Route::get('/dashboard/booking-timeline', [AdminDashboardController::class, 'bookingTimeline'])->name('dashboard.booking-timeline');

    // Bookings
    Route::get('/bookings',                     [BookingManagementController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}',            [BookingManagementController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking}/approve',   [BookingManagementController::class, 'approve'])->name('bookings.approve');
    Route::post('/bookings/{booking}/reject',    [BookingManagementController::class, 'reject'])->name('bookings.reject');
    Route::post('/bookings/{booking}/ongoing',   [BookingManagementController::class, 'markOngoing'])->name('bookings.ongoing');
    Route::post('/bookings/{booking}/cancel',    [BookingManagementController::class, 'cancel'])->name('bookings.cancel');

    // Customer Management (legacy routes kept)
    Route::get('/customers',                     [CustomerManagementController::class, 'index'])->name('customers.index');
    Route::get('/customers/{user}',              [CustomerManagementController::class, 'show'])->name('customers.show');
    Route::put('/customers/{user}/suspend',      [CustomerManagementController::class, 'suspend'])->name('customers.suspend');
    Route::put('/customers/{user}/activate',     [CustomerManagementController::class, 'activate'])->name('customers.activate');

    // Unified User Management (customers + employees)
    Route::get('/users',                                [AdminUserManagementController::class, 'index'])->name('users.index');
    Route::get('/users/employees/create',               [AdminUserManagementController::class, 'createEmployee'])->name('users.employees.create');
    Route::post('/users/employees',                     [AdminUserManagementController::class, 'storeEmployee'])->name('users.employees.store');
    Route::get('/users/employees/{user}/edit',          [AdminUserManagementController::class, 'editEmployee'])->name('users.employees.edit');
    Route::put('/users/employees/{user}',               [AdminUserManagementController::class, 'updateEmployee'])->name('users.employees.update');
    Route::put('/users/employees/{user}/deactivate',    [AdminUserManagementController::class, 'deactivateEmployee'])->name('users.employees.deactivate');
    Route::put('/users/employees/{user}/reactivate',    [AdminUserManagementController::class, 'reactivateEmployee'])->name('users.employees.reactivate');
    Route::get('/users/customers/{user}',               [AdminUserManagementController::class, 'showCustomer'])->name('users.customers.show');
    Route::put('/users/customers/{user}/suspend',       [AdminUserManagementController::class, 'suspendCustomer'])->name('users.customers.suspend');
    Route::put('/users/customers/{user}/activate',      [AdminUserManagementController::class, 'activateCustomer'])->name('users.customers.activate');

    // Vehicles
    Route::get('/vehicles',               [VehicleManagementController::class, 'index'])->name('vehicles.index');
    Route::post('/vehicles',              [VehicleManagementController::class, 'store'])->name('vehicles.store');
    Route::put('/vehicles/{vehicle}',     [VehicleManagementController::class, 'update'])->name('vehicles.update');
    Route::delete('/vehicles/{vehicle}',  [VehicleManagementController::class, 'destroy'])->name('vehicles.destroy');

    // Payments — order matters: specific routes before wildcard
    Route::get('/payments/history',               [PaymentVerificationController::class, 'history'])->name('payments.history');
    Route::get('/payments',                        [PaymentVerificationController::class, 'index'])->name('payments.index');
    Route::get('/payments/{payment}',              [PaymentVerificationController::class, 'show'])->name('payments.show');
    Route::post('/payments/{payment}/verify',      [PaymentVerificationController::class, 'verify'])->name('payments.verify');
    Route::post('/payments/{payment}/reject',      [PaymentVerificationController::class, 'reject'])->name('payments.reject');
    Route::post('/payments/{payment}/refund',      [PaymentVerificationController::class, 'recordRefund'])->name('payments.refund');

    // Returns
    Route::get('/returns',                        [ReturnProcessingController::class, 'index'])->name('returns.index');
    Route::post('/returns/{booking}/process',     [ReturnProcessingController::class, 'process'])->name('returns.process');

    // Reports (Accessible to both Admin & Super Admin)
    Route::get('/reports',                  [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/revenue',          [ReportController::class, 'revenue'])->name('reports.revenue');
    Route::get('/reports/bookings',         [ReportController::class, 'bookings'])->name('reports.bookings');
    Route::get('/reports/vehicles',         [ReportController::class, 'vehicleUtilization'])->name('reports.vehicles');
    Route::get('/reports/customers',        [ReportController::class, 'customers'])->name('reports.customers');
    Route::get('/reports/revenue/export',   [ReportController::class, 'exportRevenuePdf'])->name('reports.revenue.pdf');
    Route::get('/reports/revenue/csv',      [ReportController::class, 'exportRevenueCsv'])->name('reports.revenue.csv');
    Route::get('/reports/bookings/export',  [ReportController::class, 'exportBookingsPdf'])->name('reports.bookings.pdf');
    Route::get('/reports/bookings/csv',     [ReportController::class, 'exportBookingsCsv'])->name('reports.bookings.csv');
    Route::get('/reports/vehicles/csv',     [ReportController::class, 'exportVehiclesCsv'])->name('reports.vehicles.csv');
    Route::get('/reports/customers/csv',    [ReportController::class, 'exportCustomersCsv'])->name('reports.customers.csv');
});

// ─── Super Admin Routes ───────────────────────────────────────────────────────
Route::middleware(['auth', 'role:super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/terms',              [TermsController::class, 'edit'])->name('terms.edit');
    Route::post('/terms',             [TermsController::class, 'update'])->name('terms.update');

    // Legacy user management (kept for backward compat)
    Route::get('/users',              [UserManagementController::class, 'index'])->name('users.index');
    Route::put('/users/{user}/role',  [UserManagementController::class, 'updateRole'])->name('users.role');
    Route::delete('/users/{user}',    [UserManagementController::class, 'destroy'])->name('users.destroy');

    // Employee Management
    Route::get('/employees',                    [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/create',             [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('/employees',                   [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('/employees/{user}/edit',        [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('/employees/{user}',             [EmployeeController::class, 'update'])->name('employees.update');
    Route::put('/employees/{user}/deactivate',  [EmployeeController::class, 'deactivate'])->name('employees.deactivate');

    // Audit Logs (Super Admin only)
    Route::get('/logs', [AdminActivityLogController::class, 'index'])->name('logs.index');
});
