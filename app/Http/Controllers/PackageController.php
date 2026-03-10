<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\PackageProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PackageController
{
    public function index()
    {
        $packages = Package::with('products')->get();
        return response()->json(['err' => 0, 'data' => $packages]);
    }

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $package = Package::updateOrCreate(
                ['code' => $request->input('package-code')],
                [
                    'name' => $request->input('package-name'),
                    'desc' => $request->input('package-desc'),
                    'price' => $request->input('package-price'),
                    'type' => $request->input('package-type', 'REGULAR'),
                    'img_no' => $request->input('package-img-no', 0)
                ]
            );

            if ($request->has('items')) {
                PackageProduct::where('package_id', $package->code)->delete();
                foreach ($request->input('items') as $item) {
                    PackageProduct::create([
                        'package_id' => $package->code,
                        'product_id' => $item['product_id'],
                        'qty' => $item['qty']
                    ]);
                }
            }

            return response()->json(['err' => 0, 'msg' => 'Package saved', 'data' => $package]);
        });
    }

    public function destroy($code)
    {
        Package::where('code', $code)->delete();
        return response()->json(['err' => 0, 'msg' => 'Package removed']);
    }
}
