<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\CustomerDocument;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthApiController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid email or password.'
            ], 401);
        }

        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'verification_status' => $user->verification_status,
                'is_verified' => $user->isVerified(),
                'license_number' => $user->documents()->where('document_type', "Driver's License")->where('status', 'approved')->first()?->file_path ?? '',
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Session logged out successfully.'
        ]);
    }

    public function userProfile(Request $request)
    {
        $user = $request->user();
        $isVerified = $user->isVerified();

        return response()->json([
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'verification_status' => $user->verification_status,
            'is_verified' => $isVerified,
            'license_number' => $user->documents()->where('document_type', "Driver's License")->where('status', 'approved')->first()?->file_path ?? '',
        ]);
    }

    public function submitVerification(Request $request)
    {
        $request->validate([
            'license_number' => ['required', 'string', 'max:50'],
            'expiration_date' => ['required', 'date', 'after:today'],
            'file' => ['required', 'image', 'max:5120'],
        ]);

        $user = $request->user();

        // Store file
        $path = $request->file('file')->store('customer_documents', 'public');

        CustomerDocument::create([
            'user_id' => $user->id,
            'document_type' => "Driver's License",
            'file_path' => $path,
            'expiration_date' => $request->expiration_date,
            'status' => 'pending',
        ]);

        $user->update([
            'verification_status' => 'pending'
        ]);

        ActivityLog::log("Customer submitted Driver's License via mobile API: {$request->license_number}", User::class, $user->id);

        return response()->json([
            'message' => 'Driver\'s License submitted successfully. Please wait for admin approval.',
            'verification_status' => 'pending',
        ]);
    }
}
