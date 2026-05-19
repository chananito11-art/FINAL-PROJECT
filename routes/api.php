<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\VehicleApiController;
use App\Http\Controllers\Api\BookingApiController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthApiController::class, 'login']);
Route::get('/vehicles', [VehicleApiController::class, 'index']);
Route::get('/vehicles/{vehicle}/pricing-preview', function (\Illuminate\Http\Request $request, \App\Models\Vehicle $vehicle) {
    $request->validate([
        'pickup_date' => ['required', 'date'],
        'return_date' => ['required', 'date', 'after:pickup_date'],
    ]);
    $pricing = new \App\Services\SmartPricingService();
    return response()->json($pricing->getPricingDetails(
        $vehicle,
        \Carbon\Carbon::parse($request->pickup_date),
        \Carbon\Carbon::parse($request->return_date)
    ));
});

// Protected routes (requires login)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthApiController::class, 'userProfile']);
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::post('/user/verify', [AuthApiController::class, 'submitVerification']);

    // Dashboard & Transactions (Customer web-parity modules)
    Route::get('/dashboard', [BookingApiController::class, 'dashboard']);
    Route::get('/transactions', [BookingApiController::class, 'transactions']);

    // Bookings
    Route::get('/bookings', [BookingApiController::class, 'myBookings']);
    Route::post('/bookings', [BookingApiController::class, 'store']);
    Route::get('/bookings/{booking}', [BookingApiController::class, 'show']);
    Route::post('/bookings/{booking}/pay', [BookingApiController::class, 'submitPayment']);
});
