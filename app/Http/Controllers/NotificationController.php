<?php

namespace App\Http\Controllers;

use App\Models\NotificationSubscription;
use App\Models\User;
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

        $user = User::find($request->input('login-id'));

        $user->updatePushSubscription(
            $request->input('endpoint'), 
            $request->input('publicKey'), 
            $request->input('authToken'), 
            $request->input('contentEncoding')
        );

        return response()->json([
            'err' => 0,
            'msg' => 'Notification subscribed successfully',
            'data' => $user
        ]);
    }

    public function unsubscribe(Request $request)
    {
        $request->validate([
            'login-id' => 'required',
            'endpoint' => 'required|string',
        ]);

        $user = User::find($request->input('login-id'));

        $user->deletePushSubscription($request->input('endpoint'));

        return response()->json([
            'err' => 0,
            'msg' => 'Notification unsubscribed successfully'
        ]);
    }
}
