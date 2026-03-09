<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Illuminate\Http\Request;

class IngredientController extends Controller
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
            ['code' => $request->input('ingr-code')],
            [
                'name' => $request->input('ingr-name'),
                'desc' => $request->input('ingr-desc'),
                'unit' => $request->input('ingr-unit'),
                'img_no' => $request->input('ingr-img-no', 0)
            ]
        );

        return response()->json([
            'err' => 0,
            'msg' => 'Ingredient saved',
            'data' => $ingredient
        ]);
    }

    public function destroy($code)
    {
        Ingredient::where('code', $code)->delete();
        return response()->json(['err' => 0, 'msg' => 'Ingredient removed']);
    }
}
