<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\PackageProduct;
use App\Models\Sale;
use App\Models\SaleRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BuffetController
{
    public function index()
    {
        $packages = Package::where('type', 'BUFFET')->with('products')->get();
        return response()->json(['err' => 0, 'data' => $packages]);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string',
            'price_adult' => 'required|numeric',
            'price_child' => 'required|numeric',
            'duration_minutes' => 'required|integer',
        ]);

        return BuffetPackage::create($validated);
    }

    public function startBuffet(Request $request) {
        $package = BuffetPackage::findOrFail($request->package_id);
        
        $sale = Sale::create([
            'table_id' => $request->table_id,
            'buffet_package_id' => $package->id,
            'adult_count' => $request->adult_count,
            'child_count' => $request->child_count,
            'buffet_start_at' => now(),
            'buffet_end_at' => now()->addMinutes($package->duration_minutes),
            'status' => 'O', // Open
        ]);

        // Automatically add the "Cover Charge" items to the sales_items
        $sale->items()->createMany([
            [
                'name' => "Buffet Adult ({$package->name})",
                'quantity' => $request->adult_count,
                'price' => $package->price_adult,
                'is_buffet_base' => true
            ],
            [
                'name' => "Buffet Child ({$package->name})",
                'quantity' => $request->child_count,
                'price' => $package->price_child,
                'is_buffet_base' => true
            ]
        ]);

        return $sale;
    }
}
