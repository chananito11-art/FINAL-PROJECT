<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function index()
    {
        // Get all payments made by this user that are verified
        $transactions = Payment::whereHas('booking', function($q) {
                $q->where('user_id', Auth::id());
            })
            ->with('booking.vehicle')
            ->latest()
            ->get();

        return view('customer.transactions.index', compact('transactions'));
    }
}
