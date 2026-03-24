<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Customer;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController
{
    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');
        $user = User::where('username', $request->username)->first();

        if (auth()->attempt($credentials)) {
            $origin = $request->header('Origin');
            if ($origin == env('STORE_URL') && $user->type == 'CUSTOMER') {
                $customer = Customer::with('sales')->where('user_id', $user->id)->first();
                $token = auth()->user()->createToken('member_token')->plainTextToken;
                if ($customer) $user->customer = $customer;
                return response()->json(['user' => $user, 'token' => $token], 200);
            } else if ($origin == env('POS_URL') && in_array($user->type, ['ADMIN', 'PURCHASING', 'KITCHEN', 'CASHIER', 'WAITER'])) {
                $employee = Employee::where('user_id', $user->id)->with(['branch'])->first();
                $token = auth()->user()->createToken('auth_token')->plainTextToken;
                if ($employee) $user->employee = $employee;
                return response()->json(['user' => $user, 'token' => $token], 200);
            } else {
                auth()->logout();
                return response()->json(['origin' => $origin, 'type' => $user->type, 'message' => 'Username or Password is invalid'], 401);
            }
        } else if (!$user) {
            return response()->json(['message' => 'No account exist with the given username.'], 401);
        } else if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Password is incorrect.'], 401);
        } else {
            return response()->json(['message' => 'Username or Password is invalid'], 401);
        }
    }

    public function userProfile(Request $request)
    {
        $origin = $request->header('Origin');
        $user = $request->user();
        if ($origin == env('STORE_URL') && $user->type == 'CUSTOMER') {
            $customer = Customer::with('sales')->where('user_id', $user->id)->first();
            return response()->json([
                'err' => 0,
                'msg' => '',
                'data' => compact('user', 'customer')
            ]);
        } else if ($origin == env('POS_URL') && in_array($user->type, ['ADMIN', 'PURCHASING', 'KITCHEN', 'CASHIER', 'WAITER'])) {
            $employee = Employee::where('user_id', $user->id)->with(['branch'])->first();
            return response()->json([
                'err' => 0,
                'msg' => '',
                'data' => compact('user', 'employee')
            ]);
        }
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'dob' => 'required|date',
            'phone' => 'required|string',
            'email' => 'required|email',
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password,
            'type' => 'CUSTOMER',
        ]);

        $customer = Customer::create([
            'name' => $request->name,
            'dob' => $request->dob,
            'phone' => $request->phone,
            'email' => $request->email,
            'user_id' => $user->id
        ]);

        $token = $user->createToken('member_token')->plainTextToken;

        return response()->json([
            'err' => 0,
            'msg' => 'Register successful',
            'data' => [$user],
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'err' => 0,
            'msg' => 'Logout successful',
            'data' => []
        ]);
    }
}
