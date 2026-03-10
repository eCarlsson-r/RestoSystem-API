<?php

namespace App\Http\Controllers;

use App\Models\NotificationSubscription;
use Illuminate\Http\Request;

class NotificationController
{
    public function subscribe(Request $request)
    {
        $request->validate([
            'login-id' => 'required',
            'endpoint' => 'required|string',
            'publicKey' => 'required|string',
            'authToken' => 'required|string',
        ]);

        $subscription = NotificationSubscription::updateOrCreate(
            [
                'user_id' => $request->input('login-id'),
                'endpoint' => $request->input('endpoint')
            ],
            [
                'public_key' => $request->input('publicKey'),
                'auth_token' => $request->input('authToken')
            ]
        );

        return response()->json([
            'err' => 0,
            'msg' => 'Notification subscribed successfully',
            'data' => $subscription
        ]);
    }

    public function unsubscribe(Request $request)
    {
        $request->validate([
            'login-id' => 'required',
            'endpoint' => 'required|string',
        ]);

        NotificationSubscription::where('user_id', $request->input('login-id'))
            ->where('endpoint', $request->input('endpoint'))
            ->delete();

        return response()->json([
            'err' => 0,
            'msg' => 'Notification unsubscribed successfully'
        ]);
    }
}
