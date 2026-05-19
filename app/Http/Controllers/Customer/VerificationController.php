<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerDocument;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerificationController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $documents = $user->documents()->latest()->get();
        return view('customer.verification.show', compact('user', 'documents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'document_type'   => ['required', 'in:Driver\'s License'],
            'expiration_date' => ['required', 'date', 'after:today'],
            'file'            => ['required', 'image', 'max:5120'],
        ]);

        $path = $request->file('file')->store('customer_documents', 'public');

        CustomerDocument::create([
            'user_id'         => Auth::id(),
            'document_type'   => $request->document_type,
            'file_path'       => $path,
            'expiration_date' => $request->expiration_date,
            'status'          => 'pending',
        ]);

        Auth::user()->update(['verification_status' => 'pending']);

        ActivityLog::log("Customer submitted document for verification: {$request->document_type}", \App\Models\User::class, Auth::id());

        return back()->with('success', 'Document submitted successfully. Please wait for admin verification.');
    }
}
