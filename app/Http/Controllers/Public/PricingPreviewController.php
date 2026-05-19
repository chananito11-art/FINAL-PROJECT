<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Services\SmartPricingService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PricingPreviewController extends Controller
{
    public function preview(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'pickup_date' => ['required', 'date'],
            'return_date' => ['required', 'date', 'after:pickup_date'],
        ]);

        $pickup = Carbon::parse($request->pickup_date);
        $return = Carbon::parse($request->return_date);

        $pricing = new SmartPricingService();
        $details = $pricing->getPricingDetails($vehicle, $pickup, $return);

        return response()->json($details);
    }
}
