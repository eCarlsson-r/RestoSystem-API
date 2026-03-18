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
            ['id' => $request->input('id')],
            [
                'name' => $request->input('name'),
                'branch_id' => $request->input('branch_id'),
                'storage' => $request->input('storage'),
                'contact_person' => $request->input('contact'),
                'npwp' => $request->input('npwp'),
                'address' => $request->input('address'),
                'phone' => $request->input('phone'),
                'mobile' => $request->input('mobile'),
                'email' => $request->input('email'),
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
