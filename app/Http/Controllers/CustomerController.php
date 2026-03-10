<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController
{
    public function index()
    {
        return Customer::all();
    }

    public function store(Request $request)
    {
        $customer = Customer::updateOrCreate(
            ['id' => $request->input('customer-code')],
            [
                'name' => $request->input('customer-name'),
                'gender' => $request->input('customer-gender'),
                'address' => $request->input('customer-address'),
                'pob' => $request->input('customer-pob'),
                'dob' => $request->input('customer-dob'),
                'mobile' => $request->input('customer-mobile'),
                'email' => $request->input('customer-email'),
                'discount' => $request->input('customer-discount', 0),
                'tax' => $request->input('customer-tax', 10),
            ]
        );

        return response()->json(['err' => 0, 'msg' => 'Customer saved', 'data' => $customer]);
    }

    public function destroy($id)
    {
        Customer::destroy($id);
        return response()->json(['err' => 0, 'msg' => 'Customer removed']);
    }
}
