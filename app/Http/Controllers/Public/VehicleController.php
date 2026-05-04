<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Category;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::with('category')
            ->available()
            ->orderBy('name')
            ->get();

        $categories = Category::orderBy('category_name')->get();

        return view('public.vehicles.index', compact('vehicles', 'categories'));
    }

    public function show($id)
    {
        $vehicle = Vehicle::with('category')->findOrFail($id);
        return view('public.vehicles.show', compact('vehicle'));
    }
}
