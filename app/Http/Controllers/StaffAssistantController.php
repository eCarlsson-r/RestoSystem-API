<?php

namespace App\Http\Controllers;

use App\Ai\Agents\StaffAssistant;
use Illuminate\Http\Request;

class StaffAssistantController
{
    public function chat(Request $request)
    {
        $request->validate([
            'message'         => 'required|string|max:1000',
            'branch_id'       => 'required|integer',
            'date'            => 'required|date',
            'conversation_id' => 'nullable|string',
        ]);

        $agent = new StaffAssistant(
            branchId: $request->branch_id,
            date: $request->date,
        );

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
}
