<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\WaiterCalled;

class CustomerMenuController
{
    public function getMenu($branchCode) {
        $categories = DB::table('category')->get();
        
        // Only show products available in this branch that aren't sold out
        $products = DB::table('product')
            ->where('product-soldout', 0)
            ->select('product-code', 'product-name', 'product-desc', 'product-price', 'category-code', 'product-img-no')
            ->get();

        return response()->json([
            'categories' => $categories,
            'products' => $products
        ]);
    }
    
    public function callWaiter(Request $request) {
        $table = $request->table;
        $branch = $request->branch;

        // Optional: Log the request for performance analytics
        // Notification::send(Staff::atBranch($branch), new WaiterCalled($table));

        // Broadcast the event to the Nuxt POS
        broadcast(new WaiterCalled($table, $branch))->toOthers();

        return response()->json(['message' => 'Staff notified']);
    }

    public function getOrderStatus(Request $request) {
        $table = $request->table_number;
        $branch = $request->branch_id;

        // Find the latest 'O' (Open) sale for this table
        $sale = Sale::where('table-number', $table)
                    ->where('sales-branch', $branch)
                    ->where('sales-status', 'O')
                    ->latest()
                    ->first();

        if (!$sale) return response()->json([]);

        return SalesRecord::where('sales-id', $sale->id)
            ->with('product')
            ->get()
            ->map(fn($item) => [
                'name' => $item->product->{'product-name'},
                'status' => $item->{'item-status'},
                'qty' => 1
            ]);
    }
}
