<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class RoomController
{
    public function index()
    {
        return response()->json(['err' => 0, 'data' => Room::all()]);
    }

    public function store(Request $request)
    {
        $room = Room::updateOrCreate(
            ['id' => $request->input('room-id')],
            [
                'name' => $request->input('room-name'),
                'branch_id' => $request->input('room-branch'),
                'capacity' => $request->input('room-capacity'),
                'description' => $request->input('room-desc'),
            ]
        );

        return response()->json(['err' => 0, 'msg' => 'Room saved', 'data' => $room]);
    }

    public function destroy($id)
    {
        Room::destroy($id);
        return response()->json(['err' => 0, 'msg' => 'Room removed']);
    }
}
