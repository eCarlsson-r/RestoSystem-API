<?php

namespace App\Http\Controllers;

use App\Models\Prepare;
use App\Models\PrepareRecipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrepareController
{
    public function index()
    {
        $prepares = Prepare::all()->map(function($p) {
            $lastStock = \App\Models\Stock::where('item_type', 'PREP')
                ->where('item_code', $p->id)
                ->latest()
                ->first();
            
            $p->purchase_price = $lastStock ? $lastStock->purchase_price : 0;
            return $p;
        });

        return response()->json([
            'err' => 0,
            'msg' => '',
            'data' => $prepares
        ]);
    }

    public function show($id)
    {
        $prepare = Prepare::findOrFail($id)->first();

        if (!$prepare) {
            return response()->json(['err' => 1, 'msg' => 'prepare-not-exist']);
        }

        if ($prepare->soldout) {
            return response()->json(['err' => 1, 'msg' => 'prepare-soldout']);
        }

        return response()->json([
            'err' => 0,
            'msg' => '',
            'data' => [$prepare]
        ]);
    }

    public function store(Request $request) {
        return DB::transaction(function() use ($request) {
            // 1. Save or Update the Main Item
            $prepare = Prepare::updateOrCreate(
                ['id' => $request->id],
                $request->only(['name', 'cost', 'quantity', 'unit'])
            );

            // 2. Clear existing recipes
            $prepare->recipes()->delete();

            // 3. Insert new recipe rows
            if ($request->has('recipe')) {
                foreach ($request->recipe as $ingr) {
                    if (isset($ingr['item_code']) && (float)$ingr['quantity'] > 0) {
                        $prepare->recipes()->create([
                            'item_type' => $ingr['item_type'] ?? 'INGR',
                            'item_code' => $ingr['item_code'],
                            'quantity' => $ingr['quantity'],
                            'unit' => $ingr['unit'],
                            'purchase_price' => $ingr['purchase_price'] ?? 0
                        ]);
                    }
                }
            }

            return response()->json(['status' => 'success', 'data' => $prepare]);
        });
    }

    public function destroy($id)
    {
        $prepare = Prepare::findOrFail($id);
        if ($prepare) {
            $prepare->update(['category_id' => null]); // Legacy "remove" just unsets category
            return response()->json(['err' => 0, 'msg' => 'Prepare removed']);
        }
        return response()->json(['err' => 1, 'msg' => 'Prepare not found']);
    }

    public function getRecipe($prepareCode)
    {
        $recipes = PrepareRecipe::with(['item'])
            ->where('prepare_id', $prepareCode)
            ->get()
            ->map(function($r) {
                // Ensure frontend compatibility
                $r->ingredient_id = $r->item_code;
                return $r;
            });

        return response()->json([
            'err' => 0,
            'msg' => '',
            'data' => $recipes
        ]);
    }
}
