<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\TermsAndCondition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TermsController extends Controller
{
    public function edit()
    {
        $terms = TermsAndCondition::current();
        return view('super-admin.terms.edit', compact('terms'));
    }

    public function update(Request $request)
    {
        $request->validate(['content' => ['required', 'string']]);

        TermsAndCondition::create([
            'content'    => $request->content,
            'updated_by' => Auth::id(),
        ]);

        ActivityLog::log('Terms and Conditions updated', TermsAndCondition::class);

        return back()->with('success', 'Terms & Conditions updated successfully.');
    }
}
