<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SaleRecord;

class KitchenController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }
    
    public function getTickets(Request $request) {
        $station = $request->station; // e.g., 'KTCN'

        $tickets = SaleRecord::where('item_status', 'O')
            ->whereHas('product.category', function($q) use ($station) {
                $q->where('kitchen_process', $station);
            })
            ->with(['sale', 'sale.table', 'product'])
            ->get()
            ->groupBy('sale_id')
            ->map(function($items) {
                $header = $items->first()->sale;
                return [
                    'sales_id' => $header->{'id'},
                    'table_number' => $header->table->{'table_number'},
                    'floor_number' => $header->table->{'floor_number'},
                    'time_elapsed' => round(now()->diffInMinutes($items->first()->{'order_time'})),
                    'items' => $items->map(fn($i) => [
                        'id' => $i->{'id'},
                        'name' => $i->product->{'name'},
                        'qty' => 1, // Based on your schema, each row is an item
                        'note' => $i->{'item_note'}
                    ])
                ];
            })->values();

        return response()->json([
            'err' => 0,
            'msg' => '',
            'data' => $tickets
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
