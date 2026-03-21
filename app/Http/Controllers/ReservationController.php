<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Sale;
use App\Models\Table;
use App\Models\Buffet;
use Illuminate\Http\Request;

class ReservationController
{
    /**
     * Display a listing of reservations with Customer and Package details.
     */
    public function index(Request $request)
    {
        return Reservation::with(['customer', 'buffet'])
            ->when($request->date, function ($query, $date) {
                return $query->whereDate('event_date', $date);
            })
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->orderBy('event_date', 'asc')
            ->orderBy('event_time', 'asc')
            ->paginate(20);
    }

    /**
     * Store a new reservation.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id'       => 'required|exists:branches,id',
            'customer_id'       => 'required|exists:customers,id',
            'event_date'        => 'required|date|after_or_equal:today',
            'event_time'        => 'required',
            'buffet_id' => 'required|exists:buffets,id',
            'guaranteed_pax'    => 'required|integer|min:1',
            'deposit_amount'    => 'nullable|numeric',
            'notes'             => 'nullable|string',
        ]);

        // Auto-assign the branch and user from the authenticated session
        $user = $request->user()->load('employee');
        $validated['employee_id'] = $user->employee->id ?? 0;
        $validated['status']    = 'confirmed'; // Default for staff-entry

        $reservation = Reservation::create($validated);

        return response()->json($reservation->load(['customer', 'buffet']), 201);
    }

    /**
     * Convert Reservation to an Active Sale (Check-In)
     */
    public function checkIn($id, Request $request)
    {
        $res = Reservation::findOrFail($id);

        if ($res->status === 'checked_in') {
            return response()->json(['message' => 'Already checked in'], 422);
        }

        // 1. Create the Sale
        $sale = Sale::create([
            'customer_id'       => $res->customer_id,
            'customer_name'     => $res->customer->name,
            'buffet_id'         => $res->buffet_id,
            'table_id'          => $request->table_id,
            'adult_count'       => $res->guaranteed_pax,
            'adult_price'       => $res->buffet->price_adult,
            'child_price'       => $res->buffet->price_child,
            'duration_minutes'  => $res->buffet->duration_minutes,
            'branch_id'         => $res->branch_id,
            'user_id'           => auth()->id(),
            'employee_id'       => auth()->user()?->employee->id ?? 0,
            'status'            => 'O', // Open
            'buffet_start_at'   => now(),
            'buffet_end_at'     => now()->addMinutes($res->buffet->duration_minutes),
            'date'              => now()->toDateString(),
            'time'              => now()->toTimeString(),
            'reservation_id'    => $res->id
        ]);

        // 2. Update Reservation Status
        $res->update([
            'status' => 'checked_in',
            'sale_id' => $sale->id
        ]);

        $table = Table::findOrFail($request->table_id);
        $table->update([
            'status' => 'occupied',
        ]);

        return response()->json([
            'message' => 'Check-in successful',
            'sale_id' => $sale->id
        ]);
    }
}