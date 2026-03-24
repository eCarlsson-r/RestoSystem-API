<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController
{
    public function index(Request $request)
    {
        $query = $request->get('q');

        // If no query, return empty or recent customers
        if (!$query) {
            return Customer::all();
        }

        $customers = Customer::query()
            ->where('name', 'LIKE', "%{$query}%")
            ->orWhere('mobile', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->select('id', 'name', 'mobile', 'discount') // Only return what the UI needs
            ->limit(10)
            ->get();

        return response()->json($customers);
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
    
    public function history(Request $request) {
        $user = $request->user();
        $customer = Customer::where('user_id', $user->id)->first();
        
        // Ensure user has a customer profile
        if (!$customer) {
            return response()->json(['err' => 1, 'msg' => 'Customer profile not found'], 404);
        }

        $history = $customer->sales()
            ->with(['invoices', 'records.product', 'branch']) 
            ->latest()
            ->get();

        return response()->json([
            'err' => 0,
            'msg' => 'Success',
            'data' => $history
        ]);
    }
}
