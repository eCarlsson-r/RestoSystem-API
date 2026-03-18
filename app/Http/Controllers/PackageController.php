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
        $packages = Package::with('products', 'files')->get();
        return response()->json(['err' => 0, 'data' => $packages]);
    }

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $package = Package::updateOrCreate(
                ['id' => $request->input('id')],
                [
                    'name' => $request->input('name'),
                    'description' => $request->input('description'),
                    'price' => $request->input('price'),
                    'type' => $request->input('type', 'REGULAR')
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

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $path = $file->store('packages/gallery', 'public');
                    $package->files()->create([
                        'file_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getClientMimeType(),
                        'extension' => $file->getClientOriginalExtension(),
                        'size' => $file->getSize(),
                        'disk' => 'public',
                        'path' => $path
                    ]);
                }
            }

            return response()->json(['err' => 0, 'msg' => 'Package saved', 'data' => $package]);
        });
    }

    public function destroy($code)
    {
        Package::findOrFail($code)->delete();
        return response()->json(['err' => 0, 'msg' => 'Package removed']);
    }
}
