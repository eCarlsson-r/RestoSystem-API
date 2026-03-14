<?php

namespace App\Http\Controllers;

use App\Models\Utility;
use Illuminate\Http\Request;

class UtilityController
{
    public function index()
    {
        return response()->json([
            'err' => 0,
            'msg' => '',
            'data' => Utility::all()
        ]);
    }

    public function store(Request $request)
    {
        $utility = Utility::updateOrCreate(
            ['id' => $request->input('id')],
            [
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'unit' => $request->input('unit')
            ]
        );

        return response()->json([
            'err' => 0,
            'msg' => 'Utility saved',
            'data' => $utility
        ]);
    }

    public function destroy($id)
    {
        Utility::findOrFail($id)->delete();
        return response()->json(['err' => 0, 'msg' => 'Utility removed']);
    }
}
