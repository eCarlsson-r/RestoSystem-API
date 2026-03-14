<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseRecord;
use App\Models\PurchaseReceive;
use App\Models\PurchaseReturn;
use App\Models\Stock;
use App\Models\StockLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchasingController
{
    public function purchases() {
        return PurchaseOrder::all();
    }

    public function purchase($id) {
        return PurchaseOrder::findOrFail($id);
    }

    public function returns() {
        return PurchaseReturn::all();
    }

    public function return($id) {
        return PurchaseReturn::findOrFail($id);
    }

    public function storeOrder(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $order = PurchaseOrder::create([
                'supplier_id' => $request->integer('supplier_id'),
                'branch_id' => $request->integer('branch_id'),
                'storage' => $request->input('storage'),
                'date' => now()->toDateString(),
                'time' => now()->toTimeString(),
                'status' => 'P', // Pending
            ]);

            $items = $request->input('items', []);
            foreach ($items as $item) {
                PurchaseRecord::create([
                    'purchase_id' => $order->id,
                    'item_type' => $item['item_type'],
                    'item_code' => $item['item_code'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                ]);
            }

            return response()->json(['err' => 0, 'msg' => 'Purchase order created', 'order_id' => $order->id]);
        });
    }

    public function receiveOrder(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $order = PurchaseOrder::with('records')->findOrFail($request->order_id);
            
            $receive = PurchaseReceive::create([
                'purchase_id' => $order->id,
                'date' => now()->toDateString(),
                'time' => now()->toTimeString(),
            ]);

            foreach ($order->records as $record) {
                $stock = Stock::firstOrCreate([
                    'item_type' => $record->item_type,
                    'item_code' => $record->item_code,
                    'branch_id' => $order->branch_id,
                    'storage' => $order->storage,
                ]);

                $stock->increment('quantity', $record->qty);
                $stock->update(['purchase_price' => $record->price]);

                StockLog::create([
                    'stock_id' => $stock->id,
                    'invoice_id' => 'PUR' . str_pad($order->id, 7, '0', STR_PAD_LEFT),
                    'desc' => 'Purchase Receipt',
                    'add_qty' => $record->qty,
                    'date' => now()->toDateString(),
                    'time' => now()->toTimeString(),
                ]);
            }

            $order->update(['status' => 'D']); // Done

            return response()->json(['err' => 0, 'msg' => 'Purchase received and stock updated']);
        });
    }
}
