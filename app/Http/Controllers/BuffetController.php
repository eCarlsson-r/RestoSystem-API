<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\PackageProduct;
use App\Models\Sale;
use App\Models\SaleRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BuffetController
{
    public function index()
    {
        $packages = Package::where('type', 'BUFFET')->with('products')->get();
        return response()->json(['err' => 0, 'data' => $packages]);
    }

    public function storePackage(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $package = Package::updateOrCreate(
                ['code' => $request->input('package_id')],
                [
                    'name' => $request->input('package_name'),
                    'desc' => $request->input('package_desc'),
                    'price' => $request->input('package_price'),
                    'type' => 'BUFFET',
                    'img_no' => $request->input('package_img_no', 0)
                ]
            );

            if ($request->has('products')) {
                PackageProduct::where('package_id', $package->code)->delete();
                foreach ($request->input('products') as $product) {
                    PackageProduct::create([
                        'package_id' => $package->code,
                        'product_id' => $product['product_id'],
                        'qty' => $product['qty'] ?? 1
                    ]);
                }
            }

            return response()->json(['err' => 0, 'msg' => 'Buffet package saved', 'data' => $package]);
        });
    }

    public function storeOrder(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $sale = Sale::create([
                'table_number' => $request->table_number,
                'branch_id' => $request->branch_id,
                'customer_id' => $request->customer_id,
                'employee_id' => $request->employee_id,
                'date' => now()->toDateString(),
                'time' => now()->toTimeString(),
                'status' => 'P', // Pending
                'type' => 'BUFFET'
            ]);

            $packages = $request->input('packages', []);
            foreach ($packages as $pkg) {
                SaleRecord::create([
                    'sale_id' => $sale->id,
                    'item_id' => $pkg['package_id'], // In legacy, item_id can be product or package
                    'item_type' => 'PACKAGE',
                    'qty' => $pkg['qty'],
                    'price' => $pkg['price'],
                    'discount' => 0,
                    'employee_id' => $request->employee_id,
                    'date' => now()->toDateString(),
                    'time' => now()->toTimeString(),
                ]);
            }

            return response()->json(['err' => 0, 'msg' => 'Buffet order created', 'sale_id' => $sale->id]);
        });
    }
}
