<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    public function index()
    {
        $discounts = Discount::latest()->paginate(15);
        return view('admin.discounts.index', compact('discounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'        => ['required', 'string', 'unique:discounts,code', 'max:20'],
            'type'        => ['required', 'in:percent,fixed'],
            'value'       => ['required', 'numeric', 'min:0'],
            'starts_at'   => ['nullable', 'date'],
            'expires_at'  => ['nullable', 'date', 'after_or_equal:starts_at'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
        ]);

        $discount = Discount::create($validated);
        ActivityLog::log("Discount code created: {$discount->code}", Discount::class, $discount->id);

        return back()->with('success', 'Discount code created successfully.');
    }

    public function toggle(Discount $discount)
    {
        $discount->update(['is_active' => !$discount->is_active]);
        return back()->with('success', 'Discount status updated.');
    }

    public function destroy(Discount $discount)
    {
        $discount->delete();
        return back()->with('success', 'Discount code deleted.');
    }
}
