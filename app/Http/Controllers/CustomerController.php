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
            ['id' => $request->input('id')],
            [
                'name' => $request->input('name'),
                'gender' => $request->input('gender'),
                'address' => $request->input('address'),
                'pob' => $request->input('pob'),
                'dob' => $request->input('dob'),
                'mobile' => $request->input('mobile'),
                'email' => $request->input('email'),
                'discount' => $request->input('discount', 0),
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
