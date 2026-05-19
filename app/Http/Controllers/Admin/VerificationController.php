<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\CustomerDocument;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function index()
    {
        $pendingUsers = User::where('verification_status', 'pending')
            ->with(['documents' => fn($q) => $q->where('status', 'pending')])
            ->paginate(15);
        return view('admin.verification.index', compact('pendingUsers'));
    }

    public function show(User $user)
    {
        $user->load('documents');
        return view('admin.verification.show', compact('user'));
    }

    public function verify(Request $request, User $user)
    {
        $request->validate([
            'status' => ['required', 'in:verified,rejected'],
            'notes'  => ['nullable', 'string', 'max:500'],
        ]);

        $user->update([
            'verification_status' => $request->status,
            'id_expiration_date'  => $request->status === 'verified' ? $user->documents()->where('status', 'pending')->latest()->first()?->expiration_date : null,
        ]);

        // Update all pending documents
        $user->documents()->where('status', 'pending')->update([
            'status'      => $request->status === 'verified' ? 'approved' : 'rejected',
            'admin_notes' => $request->notes,
        ]);

        ActivityLog::log("Customer verification updated to {$request->status} for {$user->name}", User::class, $user->id);

        try {
            $user->notify(new \App\Notifications\IdentityVerificationUpdated($request->status, $request->notes));
        } catch (\Exception $e) {}

        return redirect()->route('admin.verification.index')->with('success', "Customer has been " . ucfirst($request->status));
    }
}
