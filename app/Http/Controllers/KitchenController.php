<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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

        return SalesRecord::where('item-status', 'O')
            ->whereHas('product.category', function($q) use ($station) {
                $q->where('kitchen-process', $station);
            })
            ->with(['sales', 'product'])
            ->get()
            ->groupBy('sales-id')
            ->map(function($items) {
                $header = $items->first()->sales;
                return [
                    'sales_id' => $header->id,
                    'table_number' => $header->{'table-number'},
                    'time_elapsed' => now()->diffInMinutes($items->first()->{'order-time'}),
                    'items' => $items->map(fn($i) => [
                        'id' => $i->{'sales-record-id'},
                        'name' => $i->product->{'product-name'},
                        'qty' => 1, // Based on your schema, each row is an item
                        'note' => $i->{'item-note'}
                    ])
                ];
            })->values();
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
