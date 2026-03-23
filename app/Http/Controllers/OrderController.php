<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Reservation;
use Illuminate\Http\Request;

class OrderController
{
    public function store(Request $request) 
    {
        $sale = Sale::create([
            'branch_id' => $branch,
            'table_id' => $request->input('table_id'),
            'customer_id' => $request->user()->customer->id ?? $request->user()->id,
            'date' => now()->toDateString(),
            'time' => now()->toTimeString(),
            'status' => 'O',
        ]);
        $salesId = $sale->id;

        // Mark table as Occupied
        Table::findOrFail($request->input('table_id'))->update(['status' => 'occupied']);

        $items = $request->items;
        $reservationId = $request->reservation_id;
        
        // Check if the reservation is still valid (not expired)
        $isBuffetActive = Reservation::where('id', $reservationId)
            ->where('status', 'checked_in')
            ->exists();

        $finalTotal = 0;

        foreach ($items as $item) {
            $product = Product::find($item['id']);
            
            // Recalculate price on server side
            $price = ($isBuffetActive && $product->is_buffet_eligible) 
                ? 0 
                : $product->price;

            $finalTotal += ($price * $item['quantity']);
            
            SaleRecord::create([
                'sale_id' => $salesId,
                'item_type' => $item['item_type'], // product or package
                'item_code' => $item['item_code'],
                'quantity' => $item['quantity'],
                'item_price' => $item['item_price'],
                'item_status' => 'O',
                'item_note' => $item['item_note'] ?? '',
                'order_employee' => $request->user()->employee->id
            ]);
        }

        // Save Order and respond
        return response()->json([
            'status' => 'success', 
            'sale_id' => $salesId
        ]);
    }
}
