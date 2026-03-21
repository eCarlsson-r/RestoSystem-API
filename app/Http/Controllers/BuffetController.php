<?php

namespace App\Http\Controllers;

use App\Models\Buffet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BuffetController
{
    public function index()
    {
        $packages = Buffet::get();
        return response()->json(['err' => 0, 'data' => $packages]);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string',
            'price_adult' => 'required|numeric',
            'price_child' => 'required|numeric',
            'duration_minutes' => 'required|integer',
            'description' => 'string'
        ]);

        $buffet = Buffet::create($validated);

        if ($request->has('product_ids')) {
            $buffet->products()->sync($request->product_ids);
        }

        return response()->json(['err' => 0, 'data' => $buffet]);
    }

    public function update(Request $request, $id) {
        $package = BuffetPackage::findOrFail($id);
        $package->update($request->except('product_ids'));

        if ($request->has('product_ids')) {
            // This uses the pivot table we discussed earlier
            $package->products()->sync($request->product_ids);
        }
    }

    public function syncItems(Request $request, $id)
    {
        $package = Buffet::findOrFail($id);
        $package->products()->sync($request->product_ids);

        return response()->json(['message' => 'Items synced successfully']);
    }
}
