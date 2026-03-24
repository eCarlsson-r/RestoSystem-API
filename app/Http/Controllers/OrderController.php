<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleRecord;
use App\Models\Table;
use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Events\StationNotification;

class OrderController
{
    public function callWaiter(Request $request) {
        $request->validate([
            'table_id' => 'required',
            'branch_id' => 'required',
            'request_type' => 'required'
        ]);

        $table = $request->table_id;
        $branch = $request->branch_id;

        broadcast(new StationNotification("waiter.{$branch}", [
            'title' => "Table {$table} called!",
            'type' => "called",
            'body' => "Table {$table} has been called."
        ]));

        return response()->json(['msg' => 'Request sent']);
    }

    public function store(Request $request) 
    {
        $branch = Branch::where('slug', $request->branch)->first();
        $customer = Customer::where('user_id', $request->user()->id)->first();

        $sale = Sale::create([
            'branch_id' => $branch->id,
            'table_id' => $request->table,
            'customer_id' => $customer->id,
            'employee_id' => '1',
            'date' => now()->toDateString(),
            'time' => now()->toTimeString(),
            'status' => 'O',
        ]);
        $salesId = $sale->id;

        // Mark table as Occupied
        Table::findOrFail($request->input('table'))->update(['status' => 'occupied']);

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
                'item_type' => $item['type'], // product or package
                'item_code' => $product->id,
                'quantity' => $item['quantity'],
                'item_price' => $item['price'],
                'item_status' => 'O',
                'item_note' => $item['item_note'] ?? '',
                'order_employee' => '1'
            ]);
        }

        // Save Order and respond
        return response()->json([
            'status' => 'success', 
            'sale_id' => $salesId
        ]);
    }
}
