<?php

use Illuminate\Support\Facades\Route;
use App\Models\Vehicle;
use Spatie\Permission\Models\Role;

use App\Http\Controllers\AuthController;

// Public namespace
use App\Http\Controllers\Public\VehicleController as PublicVehicleController;

// Customer namespace
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\BookingController as CustomerBookingController;
use App\Http\Controllers\Customer\PaymentController as CustomerPaymentController;
use App\Http\Controllers\Customer\PaymentListController as CustomerPaymentListController;
use App\Http\Controllers\Customer\TrackingController as CustomerTrackingController;
use App\Http\Controllers\Customer\TransactionController as CustomerTransactionController;
use App\Http\Controllers\Customer\ProfileController as CustomerProfileController;
use App\Http\Controllers\Customer\VerificationController;

// Admin namespace
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\BookingManagementController;
use App\Http\Controllers\Admin\VehicleManagementController;
use App\Http\Controllers\Admin\PaymentVerificationController;
use App\Http\Controllers\Admin\ReturnProcessingController;
use App\Http\Controllers\Admin\InspectionController;
use App\Http\Controllers\Admin\ActivityLogController as AdminActivityLogController;
use App\Http\Controllers\Admin\UserManagementController as AdminUserManagementController;

// Super Admin namespace
use App\Http\Controllers\SuperAdmin\TermsController;
use App\Http\Controllers\SuperAdmin\UserManagementController;
use App\Http\Controllers\SuperAdmin\EmployeeController;
use App\Http\Controllers\SuperAdmin\ReportController;

// ─── Auth Routes ──────────────────────────────────────────────────────────────
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Email Verification Routes
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (\Illuminate\Foundation\Auth\EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/customer/dashboard')->with('success', 'Email verified successfully!');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (\Illuminate\Http\Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('success', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// ─── Public Routes ────────────────────────────────────────────────────────────
Route::get('/', function () {
    try {
        $vehicles = Vehicle::available()->orderBy('name')->take(6)->get();
        $customersCount = Role::where([['name', 'customer'], ['guard_name', 'web']])->exists()
            ? \App\Models\User::role('customer')->count()
            : 0;

        $stats = [
            'vehicles' => Vehicle::count(),
            'customers' => $customersCount,
            'bookings' => \App\Models\Booking::count(),
            'revenue' => \App\Models\Payment::where('status', 'verified')->sum('amount'),
        ];

        return view('welcome', compact('vehicles', 'stats'));
    } catch (\Illuminate\Database\QueryException | \PDOException $exception) {
        return response()->view('errors.db-unavailable', [], 503);
    }

});

Route::get('/vehicles', [PublicVehicleController::class, 'index'])->name('vehicles.index');
Route::get('/vehicles/{id}', [PublicVehicleController::class, 'show'])->name('vehicles.show');
Route::get('/vehicles/{vehicle}/availability', [PublicVehicleController::class, 'availability'])->name('vehicles.availability');
Route::get('/vehicles/{vehicle}/pricing-preview', [\App\Http\Controllers\Public\PricingPreviewController::class, 'preview'])->name('vehicles.pricing-preview');

// ─── Customer Routes (Verification Required) ──────────────────────────────────
Route::middleware(['auth', 'role:customer|admin|super_admin'])->group(function () {
    Route::get('/customer/dashboard', [CustomerDashboardController::class, 'index'])->name('customer.dashboard');
    Route::get('/customer/booking/create', [CustomerBookingController::class, 'create'])->name('customer.booking.create');
    Route::post('/customer/booking', [CustomerBookingController::class, 'store'])->name('customer.booking.store');
    Route::post('/customer/bookings/{booking}/cancel', [CustomerBookingController::class, 'cancel'])->name('customer.bookings.cancel');

    Route::get('/customer/payment/{booking}', [CustomerPaymentController::class, 'show'])->name('customer.payment.show');
    Route::post('/customer/payment/{booking}', [CustomerPaymentController::class, 'store'])->name('customer.payment.store');
    Route::get('/customer/payments', [CustomerPaymentListController::class, 'index'])->name('customer.payments.index');
    Route::get('/customer/transactions', [CustomerTransactionController::class, 'index'])->name('customer.transactions.index');
    Route::get('/customer/profile', [CustomerProfileController::class, 'edit'])->name('customer.profile.edit');
    Route::put('/customer/profile', [CustomerProfileController::class, 'update'])->name('customer.profile.update');

    Route::get('/customer/verification', [VerificationController::class, 'show'])->name('customer.verification.show');
    Route::post('/customer/verification', [VerificationController::class, 'store'])->name('customer.verification.store');

    Route::get('/customer/tracking', [CustomerTrackingController::class, 'index'])->name('customer.tracking.index');
    Route::get('/customer/tracking/{booking}', [CustomerTrackingController::class, 'show'])->name('customer.tracking.show');
});

// ─── Admin Routes ─────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:admin|super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dispatch', [\App\Http\Controllers\Admin\DispatchCalendarController::class, 'index'])->name('dispatch.index');
    Route::get('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');
    Route::get('/rentals', [\App\Http\Controllers\Admin\OngoingRentalController::class, 'index'])->name('rentals.index');

    // Dashboard chart endpoints
    Route::get('/dashboard/revenue-chart', [AdminDashboardController::class, 'revenueChart'])->name('dashboard.revenue-chart');
    Route::get('/dashboard/booking-chart', [AdminDashboardController::class, 'bookingChart'])->name('dashboard.booking-chart');
    Route::get('/dashboard/vehicle-chart', [AdminDashboardController::class, 'vehicleChart'])->name('dashboard.vehicle-chart');
    Route::get('/dashboard/booking-timeline', [AdminDashboardController::class, 'bookingTimeline'])->name('dashboard.booking-timeline');

    // Bookings
    Route::get('/bookings', [BookingManagementController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/walk-in', [BookingManagementController::class, 'createWalkIn'])->name('bookings.walk-in.create');
    Route::post('/bookings/walk-in', [BookingManagementController::class, 'storeWalkIn'])->name('bookings.walk-in.store');
    Route::get('/bookings/{booking}', [BookingManagementController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking}/approve', [BookingManagementController::class, 'approve'])->name('bookings.approve');
    Route::post('/bookings/{booking}/reject', [BookingManagementController::class, 'reject'])->name('bookings.reject');
    Route::post('/bookings/{booking}/confirm', [BookingManagementController::class, 'confirm'])->name('bookings.confirm');
    Route::post('/bookings/{booking}/record-payment', [BookingManagementController::class, 'recordPayment'])->name('bookings.record-payment');
    Route::post('/bookings/{booking}/ongoing', [BookingManagementController::class, 'markOngoing'])->name('bookings.ongoing');
    Route::post('/bookings/{booking}/no-show', [BookingManagementController::class, 'markNoShow'])->name('bookings.no-show');
    Route::post('/bookings/{booking}/settle-deposit', [BookingManagementController::class, 'settleDeposit'])->name('bookings.settle-deposit');
    Route::post('/bookings/{booking}/cancel', [BookingManagementController::class, 'cancel'])->name('bookings.cancel');

    // Guest Profiles (Walk-in Customer Directory)
    Route::get('/guests/search', [\App\Http\Controllers\Admin\GuestProfileController::class, 'search'])->name('guests.search');
    Route::get('/guests/{guest}', [\App\Http\Controllers\Admin\GuestProfileController::class, 'show'])->name('guests.show');
    Route::post('/guests', [\App\Http\Controllers\Admin\GuestProfileController::class, 'store'])->name('guests.store');
    Route::put('/guests/{guest}', [\App\Http\Controllers\Admin\GuestProfileController::class, 'update'])->name('guests.update');

    // Unified User Management (customers + employees)
    Route::get('/users', [AdminUserManagementController::class, 'index'])->name('users.index');
    Route::get('/users/employees/create', [AdminUserManagementController::class, 'createEmployee'])->name('users.employees.create');
    Route::post('/users/employees', [AdminUserManagementController::class, 'storeEmployee'])->name('users.employees.store');
    Route::get('/users/employees/{user}/edit', [AdminUserManagementController::class, 'editEmployee'])->name('users.employees.edit');
    Route::put('/users/employees/{user}', [AdminUserManagementController::class, 'updateEmployee'])->name('users.employees.update');
    Route::put('/users/employees/{user}/deactivate', [AdminUserManagementController::class, 'deactivateEmployee'])->name('users.employees.deactivate');
    Route::put('/users/employees/{user}/reactivate', [AdminUserManagementController::class, 'reactivateEmployee'])->name('users.employees.reactivate');
    Route::get('/users/customers/{user}', [AdminUserManagementController::class, 'showCustomer'])->name('users.customers.show');
    Route::put('/users/customers/{user}/suspend', [AdminUserManagementController::class, 'suspendCustomer'])->name('users.customers.suspend');
    Route::put('/users/customers/{user}/activate', [AdminUserManagementController::class, 'activateCustomer'])->name('users.customers.activate');

    // Verification
    Route::get('/verification', [\App\Http\Controllers\Admin\VerificationController::class, 'index'])->name('verification.index');
    Route::get('/verification/{user}', [\App\Http\Controllers\Admin\VerificationController::class, 'show'])->name('verification.show');
    Route::post('/verification/{user}', [\App\Http\Controllers\Admin\VerificationController::class, 'verify'])->name('verification.verify');

    // Vehicles
    Route::get('/vehicles', [VehicleManagementController::class, 'index'])->name('vehicles.index');
    Route::post('/vehicles', [VehicleManagementController::class, 'store'])->name('vehicles.store');
    Route::put('/vehicles/{vehicle}', [VehicleManagementController::class, 'update'])->name('vehicles.update');
    Route::delete('/vehicles/{vehicle}', [VehicleManagementController::class, 'destroy'])->name('vehicles.destroy');



    // Payments — order matters: specific routes before wildcard
    Route::get('/payments/history', [PaymentVerificationController::class, 'history'])->name('payments.history');
    Route::get('/payments', [PaymentVerificationController::class, 'index'])->name('payments.index');
    Route::get('/payments/{payment}', [PaymentVerificationController::class, 'show'])->name('payments.show');
    Route::post('/payments/{payment}/verify', [PaymentVerificationController::class, 'verify'])->name('payments.verify');
    Route::post('/payments/{payment}/reject', [PaymentVerificationController::class, 'reject'])->name('payments.reject');
    Route::post('/payments/{payment}/refund', [PaymentVerificationController::class, 'recordRefund'])->name('payments.refund');

    // Returns
    Route::get('/returns', [ReturnProcessingController::class, 'index'])->name('returns.index');
    Route::post('/returns/{rental}/process', [ReturnProcessingController::class, 'process'])->name('returns.process');

    // Inspections
    Route::get('/bookings/{booking}/inspection', [InspectionController::class, 'create'])->name('bookings.inspection.create');
    Route::post('/bookings/{booking}/inspection', [InspectionController::class, 'store'])->name('bookings.inspection.store');

    // Maintenance
    Route::get('/maintenance', [\App\Http\Controllers\Admin\MaintenanceController::class, 'index'])->name('maintenance.index');
    Route::post('/maintenance', [\App\Http\Controllers\Admin\MaintenanceController::class, 'store'])->name('maintenance.store');
    Route::get('/maintenance/{vehicle}', [\App\Http\Controllers\Admin\MaintenanceController::class, 'show'])->name('maintenance.show');
    Route::post('/maintenance/{vehicle}/send', [\App\Http\Controllers\Admin\MaintenanceController::class, 'sendToMaintenance'])->name('maintenance.send');
    Route::post('/maintenance/{vehicle}/release', [\App\Http\Controllers\Admin\MaintenanceController::class, 'releaseFromMaintenance'])->name('maintenance.release');

    // Discounts
    Route::get('/discounts', [\App\Http\Controllers\Admin\DiscountController::class, 'index'])->name('discounts.index');
    Route::post('/discounts', [\App\Http\Controllers\Admin\DiscountController::class, 'store'])->name('discounts.store');
    Route::post('/discounts/{discount}/toggle', [\App\Http\Controllers\Admin\DiscountController::class, 'toggle'])->name('discounts.toggle');
    Route::delete('/discounts/{discount}', [\App\Http\Controllers\Admin\DiscountController::class, 'destroy'])->name('discounts.destroy');

    // Reports (Accessible to both Admin & Super Admin)
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/revenue', [ReportController::class, 'revenue'])->name('reports.revenue');
    Route::get('/reports/bookings', [ReportController::class, 'bookings'])->name('reports.bookings');
    Route::get('/reports/vehicles', [ReportController::class, 'vehicleUtilization'])->name('reports.vehicles');
    Route::get('/reports/customers', [ReportController::class, 'customers'])->name('reports.customers');
    Route::get('/reports/revenue/export', [ReportController::class, 'exportRevenuePdf'])->name('reports.revenue.pdf');
    Route::get('/reports/revenue/csv', [ReportController::class, 'exportRevenueCsv'])->name('reports.revenue.csv');
    Route::get('/reports/bookings/export', [ReportController::class, 'exportBookingsPdf'])->name('reports.bookings.pdf');
    Route::get('/reports/bookings/csv', [ReportController::class, 'exportBookingsCsv'])->name('reports.bookings.csv');
    Route::get('/reports/vehicles/csv', [ReportController::class, 'exportVehiclesCsv'])->name('reports.vehicles.csv');
    Route::get('/reports/customers/csv', [ReportController::class, 'exportCustomersCsv'])->name('reports.customers.csv');
});

// ─── Super Admin Routes ───────────────────────────────────────────────────────
Route::middleware(['auth', 'role:super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/terms', [TermsController::class, 'edit'])->name('terms.edit');
    Route::post('/terms', [TermsController::class, 'update'])->name('terms.update');

    // Legacy user management (kept for backward compat)
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::put('/users/{user}/role', [UserManagementController::class, 'updateRole'])->name('users.role');
    Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');

    // Employee Management
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('/employees/{user}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('/employees/{user}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::put('/employees/{user}/deactivate', [EmployeeController::class, 'deactivate'])->name('employees.deactivate');

    // Audit Logs (Super Admin only)
    Route::get('/logs', [AdminActivityLogController::class, 'index'])->name('logs.index');
});
