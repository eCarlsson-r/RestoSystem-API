<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\Stock;
use Illuminate\Http\Request;

class IngredientController
{
    public function index()
    {
        $ingredients = Ingredient::all()->map(function($ingr) {
            $lastStock = Stock::where('item_type', 'INGR')
                ->where('item_code', $ingr->id)
                ->latest()
                ->first();
            
            $ingr->purchase_price = $lastStock ? $lastStock->purchase_price : 0;
            return $ingr;
        });

        return response()->json([
            'err' => 0,
            'msg' => '',
            'data' => $ingredients
        ]);
    }

    public function store(Request $request)
    {
        $ingredient = Ingredient::updateOrCreate(
            ['id' => $request->input('id')],
            [
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'unit' => $request->input('unit')
            ]
        );

        return response()->json([
            'err' => 0,
            'msg' => 'Ingredient saved',
            'data' => $ingredient
        ]);
    }

    public function destroy($id)
    {
        Ingredient::findOrFail($id)->delete();
        return response()->json(['err' => 0, 'msg' => 'Ingredient removed']);
    }
}
