<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)
            ->whereIn('type', ['ADMIN', 'PURCHASING', 'KITCHEN', 'CASHIER', 'WAITER'])
            ->with(['employee.branch'])
            ->first();

        if (!$user) {
            return response()->json([
                'err' => 1,
                'msg' => 'No account exist with the given username.'
            ]);
        }

        // Note: In legacy it was plain text. In Laravel we should use Hash.
        // For migration purposes, you might want to check both if you haven't hashed existing passwords yet.
        if ($request->password !== $user->password && !Hash::check($request->password, $user->password)) {
                 return response()->json([
                    'err' => 1,
                    'msg' => 'Password is incorrect.'
                ]);
        }

        // Format response to match legacy expectation if needed, or return standard Laravel response
        $data = $user->toArray();

        return response()->json([
            'err' => 0,
            'msg' => 'Login successful',
            'data' => [$data]
        ]);
    }
}
