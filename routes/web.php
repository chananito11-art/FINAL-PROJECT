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
use App\Http\Controllers\Admin\VehicleManagementController;
use App\Http\Controllers\Admin\PaymentVerificationController;
use App\Http\Controllers\Admin\ReturnProcessingController;
use App\Http\Controllers\Admin\ActivityLogController as AdminActivityLogController;

// Super Admin namespace
use App\Http\Controllers\SuperAdmin\TermsController;
use App\Http\Controllers\SuperAdmin\UserManagementController;

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
        'vehicles'     => Vehicle::count(),
        'customers'    => \App\Models\User::role('customer')->count(),
        'bookings'     => \App\Models\Booking::count(),
        'revenue'      => \App\Models\Payment::where('status','verified')->sum('amount'),
    ];
    return view('welcome', compact('vehicles', 'categories', 'stats'));
});

Route::get('/vehicles',     [PublicVehicleController::class, 'index'])->name('vehicles.index');
Route::get('/vehicles/{id}',[PublicVehicleController::class, 'show'])->name('vehicles.show');

// ─── Customer Routes (role: customer or admin) ────────────────────────────────
Route::middleware(['auth', 'role:customer|admin|super_admin'])->group(function () {
    Route::get('/customer/booking/create',   [CustomerBookingController::class, 'create'])->name('customer.booking.create');
    Route::post('/customer/booking',         [CustomerBookingController::class, 'store'])->name('customer.booking.store');

    Route::get('/customer/payment/{booking}', [CustomerPaymentController::class, 'show'])->name('customer.payment.show');
    Route::post('/customer/payment/{booking}',[CustomerPaymentController::class, 'store'])->name('customer.payment.store');

    Route::get('/customer/tracking',          [CustomerTrackingController::class, 'index'])->name('customer.tracking.index');
    Route::get('/customer/tracking/{booking}',[CustomerTrackingController::class, 'show'])->name('customer.tracking.show');
});

// ─── Admin Routes ─────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:admin|super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Bookings
    Route::get('/bookings',                   [BookingManagementController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}',          [BookingManagementController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking}/approve', [BookingManagementController::class, 'approve'])->name('bookings.approve');
    Route::post('/bookings/{booking}/reject',  [BookingManagementController::class, 'reject'])->name('bookings.reject');
    Route::post('/bookings/{booking}/ongoing', [BookingManagementController::class, 'markOngoing'])->name('bookings.ongoing');

    // Vehicles
    Route::get('/vehicles',              [VehicleManagementController::class, 'index'])->name('vehicles.index');
    Route::post('/vehicles',             [VehicleManagementController::class, 'store'])->name('vehicles.store');
    Route::put('/vehicles/{vehicle}',    [VehicleManagementController::class, 'update'])->name('vehicles.update');
    Route::delete('/vehicles/{vehicle}', [VehicleManagementController::class, 'destroy'])->name('vehicles.destroy');

    // Payments
    Route::get('/payments',                    [PaymentVerificationController::class, 'index'])->name('payments.index');
    Route::post('/payments/{payment}/verify',  [PaymentVerificationController::class, 'verify'])->name('payments.verify');
    Route::post('/payments/{payment}/reject',  [PaymentVerificationController::class, 'reject'])->name('payments.reject');

    // Returns
    Route::get('/returns',                    [ReturnProcessingController::class, 'index'])->name('returns.index');
    Route::post('/returns/{booking}/process', [ReturnProcessingController::class, 'process'])->name('returns.process');
    
    // Audit Logs
    Route::get('/logs',                       [AdminActivityLogController::class, 'index'])->name('logs.index');
});

// ─── Super Admin Routes ───────────────────────────────────────────────────────
Route::middleware(['auth', 'role:super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/terms',                     [TermsController::class, 'edit'])->name('terms.edit');
    Route::post('/terms',                    [TermsController::class, 'update'])->name('terms.update');
    Route::get('/users',                     [UserManagementController::class, 'index'])->name('users.index');
    Route::put('/users/{user}/role',         [UserManagementController::class, 'updateRole'])->name('users.role');
    Route::delete('/users/{user}',           [UserManagementController::class, 'destroy'])->name('users.destroy');
});
