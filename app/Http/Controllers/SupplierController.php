<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController
{
    public function index()
    {
        return response()->json(['err' => 0, 'msg' => 'Suppliers fetched successfully', 'data' => Supplier::all()]);
    }

    public function store(Request $request)
    {
        $supplier = Supplier::updateOrCreate(
            ['id' => $request->input('supplier-code')],
            [
                'name' => $request->input('supplier-name'),
                'branch_id' => $request->input('supply-branch'),
                'storage' => $request->input('supply-storage'),
                'contact_person' => $request->input('supplier-contact'),
                'npwp' => $request->input('supplier-npwp'),
                'address' => $request->input('supplier-address'),
                'phone' => $request->input('supplier-phone'),
                'mobile' => $request->input('supplier-cp-mobile'),
                'email' => $request->input('supplier-email'),
            ]
        );

        return response()->json(['err' => 0, 'msg' => 'Supplier saved', 'data' => $supplier]);
    }

    public function destroy($id)
    {
        Supplier::destroy($id);
        return response()->json(['err' => 0, 'msg' => 'Supplier removed']);
    }
}
