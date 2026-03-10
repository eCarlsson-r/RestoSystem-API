<?php

namespace App\Http\Controllers;

use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UtilityController
{
    public function getCities()
    {
        // Legacy logic: select distinct city from state table
        $cities = DB::table('utilities')->distinct()->pluck('city');
        return response()->json(['err' => 0, 'data' => $cities]);
    }

    public function getStates(Request $request)
    {
        $states = DB::table('utilities')
            ->where('city', $request->city)
            ->pluck('state');
        return response()->json(['err' => 0, 'data' => $states]);
    }
}
