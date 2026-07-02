<?php

namespace App\Http\Controllers;

use App\Ai\Agents\ReservationAgent;
use Illuminate\Http\Request;

class ReservationAgentController
{
    public function chat(Request $request)
    {
        $request->validate([
            'message'         => 'required|string|max:1000',
            'branch_id'       => 'required|integer',
            'date'            => 'required|date',
            'conversation_id' => 'nullable|string',
        ]);

        $agent = new ReservationAgent(
            branchId: $request->branch_id,
            date: $request->date,
        );

        // Continue existing conversation or start new one
        if ($request->conversation_id) {
            $response = $agent
                ->continue($request->conversation_id, as: $request->user())
                ->prompt($request->message);
        } else {
            $response = $agent
                ->forUser($request->user())
                ->prompt($request->message);
        }

        return response()->json([
            'reply'           => (string) $response,
            'conversation_id' => $response->conversationId,
        ]);
    }

    public function stream(Request $request)
    {
        $request->validate([
            'message'   => 'required|string|max:1000',
            'branch_id' => 'required|integer',
            'date'      => 'required|date',
        ]);

        $agent = new ReservationAgent(
            branchId: $request->branch_id,
            date: $request->date,
        );

        return $agent->stream($request->message);
    }
}