<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Illuminate\Http\Request;

class IngredientController
{
    public function index()
    {
        return response()->json([
            'err' => 0,
            'msg' => '',
            'data' => Ingredient::all()
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
