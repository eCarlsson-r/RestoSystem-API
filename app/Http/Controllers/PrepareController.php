<?php

namespace App\Http\Controllers;

use App\Models\Prepare;
use App\Models\PrepareRecipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrepareController
{
    public function index(Request $request)
    {
        $query = Prepare::where('category_id', $request->category)->where('soldout', 0);
        
        /*if ($request->has('branch')) {
            $query->where('soldout', 0); // Simplified branch logic for now
        }*/

        if ($request->has('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        $prepares = $query->get();

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
                $request->only(['name', 'category_id', 'price', 'soldout'])
            );

            // 2. Clear existing recipe
            $prepare->ingredients()->delete();

            // 3. Insert new recipe rows
            if ($request->has('recipe')) {
                foreach ($request->recipe as $ingr) {
                    if ($ingr['ingredient_id'] && $ingr['qty'] > 0) {
                        $prepare->ingredients()->create([
                            'ingredient_code' => $ingr['ingredient_id'],
                            'quantity' => $ingr['qty'],
                            'unit' => $ingr['unit']
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
        $recipes = PrepareRecipe::with('ingredient')
            ->where('prepare_id', $prepareCode)
            ->get();

        return response()->json([
            'err' => 0,
            'msg' => '',
            'data' => $recipes
        ]);
    }
}
