<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderRecord;
use App\Models\PurchaseReceive;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnRecord;
use App\Models\Stock;
use App\Models\StockLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchasingController
{
    public function purchases() {
        return PurchaseOrder::with('supplier')->get();
    }

    public function reportPurchase(Request $request) {
        $start = $request->start;
        $end = $request->end;
        $branchId = $request->branch;
        
        // Base query for sales in this period/branch
        if ($branchId != 'ALL') {
            $salesQuery = PurchaseOrder::whereBetween('date', [$start, $end])
                ->where('branch_id', $branchId);
        } else {
            $salesQuery = PurchaseOrder::whereBetween('date', [$start, $end]);
        }

        // 2. Fetch delivered sales with filtered records
        $deliveredSalesQuery = (clone $salesQuery)->where('status', 'D');

        $deliveredSales = $deliveredSalesQuery
            ->with(['items'])
            ->get();

        $allRecords = collect();
        foreach ($deliveredSales as $sale) {
            $allRecords = $allRecords->concat($sale->items);
        }

        // 4. Group items for the table
        $reportData = $allRecords->groupBy(function($record) {
            return $record->item_type . '-' . $record->item_code;
        })->map(function($group) {
            $first = $group->first();
            
            $name = $first->item_type === 'INGR' 
                    ? ($first->ingredient->name ?? 'Ingredient #'.$first->item_code)
                    : ($first->utility->name ?? 'Utility #'.$first->item_code);

            return [
                'name'     => $name,
                'quantity' => (float)$group->sum('quantity'),
                'price'    => (float)$first->price, 
                'total'    => (float)$group->sum(function($r) { 
                    return $r->quantity * $r->price; 
                })
            ];
        })->values();

        return response()->json([
            'err' => 0,
            'msg' => '',
            'data' => $reportData
        ]);
    }

    public function reportSupplierPurchase(Request $request) {
        $start = $request->start;
        $end = $request->end;
        $branchId = $request->branch;
        $supplierId = $request->supplier_id;
        
        // Base query for sales in this period/branch
        if ($branchId != 'ALL') {
            $purchasesQuery = PurchaseOrder::whereBetween('date', [$start, $end])
                ->where('branch_id', $branchId);
        } else {
            $purchasesQuery = PurchaseOrder::whereBetween('date', [$start, $end]);
        }

        if ($supplierId && $supplierId != 'ALL') {
            $purchasesQuery->where('supplier_id', $supplierId);
        }

        // 2. Fetch delivered sales with filtered records
        $deliveredPurchasesQuery = (clone $purchasesQuery)->where('status', 'D');

        $deliveredPurchases = $deliveredPurchasesQuery
            ->with(['items'])
            ->get();

        $allRecords = collect();
        foreach ($deliveredPurchases as $purchase) {
            $allRecords = $allRecords->concat($purchase->items);
        }

        // 4. Group items for the table
        $reportData = $allRecords->groupBy(function($record) {
            return $record->item_type . '-' . $record->item_code;
        })->map(function($group) {
            $first = $group->first();
            
            $name = $first->item_type === 'INGR' 
                    ? ($first->ingredient->name ?? 'Ingredient #'.$first->item_code)
                    : ($first->utility->name ?? 'Utility #'.$first->item_code);

            return [
                'name'     => $name,
                'quantity' => (float)$group->sum('quantity'),
                'price'    => (float)$first->price, 
                'total'    => (float)$group->sum(function($r) { 
                    return $r->quantity * $r->price; 
                })
            ];
        })->values();

        return response()->json([
            'err' => 0,
            'msg' => '',
            'data' => $reportData
        ]);
    }

    public function purchase($id) {
        return PurchaseOrder::with('records')->findOrFail($id);
    }

    public function returns() {
        return PurchaseReturn::with('supplier')->get();
    }

    public function return($id) {
        return PurchaseReturn::with('purchase')->findOrFail($id);
    }

    public function storeOrder(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $order = PurchaseOrder::create([
                'supplier_id' => $request->integer('supplier_id'),
                'branch_id' => $request->integer('branch_id'),
                'storage' => $request->input('storage'),
                'date' => $request->input('date'),
                'delivery_date' => $request->input('delivery_date'),
                'status' => 'P', // Pending
                'description' => $request->input('description')
            ]);

            $items = $request->input('items', []);
            foreach ($items as $item) {
                PurchaseOrderRecord::create([
                    'purchase_order_id' => $order->id,
                    'item_type' => $item['item_type'],
                    'item_code' => $item['item_code'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            return response()->json(['err' => 0, 'msg' => 'Purchase order created', 'order_id' => $order->id]);
        });
    }

    public function storeReturn(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $return = PurchaseReturn::create([
                'supplier_id' => $request->integer('supplier_id'),
                'branch_id' => $request->integer('branch_id'),
                'storage' => $request->input('storage'),
                'date' => $request->input('date'),
                'delivery_date' => $request->input('delivery_date'),
                'description' => $request->input('description')
            ]);

            $items = $request->input('items', []);
            foreach ($items as $item) {
                PurchaseReturnRecord::create([
                    'purchase_return_id' => $return->id,
                    'item_type' => $item['item_type'],
                    'item_code' => $item['item_code'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);

                $stock = Stock::firstOrCreate([
                    'item_type' => $item['item_type'],
                    'item_code' => $item['item_code'],
                    'branch_id' => $request->integer('branch_id'),
                    'storage' => $request->input('storage'),
                ], [
                    'purchase_price' => $item['price'],
                    'quantity' => 0,
                ]);

                $stock->decrement('quantity', $item['quantity']);
                $stock->update(['purchase_price' => $item['price']]);

                StockLog::create([
                    'stock_id' => $stock->id,
                    'invoice_id' => 'PRT' . str_pad($return->id, 7, '0', STR_PAD_LEFT),
                    'description' => 'Purchase Return',
                    'get_qty' => $item['quantity'],
                    'date' => now()->toDateString(),
                    'time' => now()->toTimeString(),
                ]);
            }

            return response()->json(['err' => 0, 'msg' => 'Purchase return created', 'return_id' => $return->id]);
        });
    }

    public function receiveOrder(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $order = PurchaseOrder::with('items')->findOrFail($request->order_id);
            
            $order->update(['status' => 'R']); // Received

            foreach ($order->items as $item) {
                $stock = Stock::firstOrCreate([
                    'item_type' => $item->item_type,
                    'item_code' => $item->item_code,
                    'branch_id' => $order->branch_id,
                    'storage' => $order->storage,
                ], [
                    'purchase_price' => $item->price,
                    'quantity' => 0,
                ]);

                $stock->increment('quantity', $item->quantity);
                $stock->update(['purchase_price' => $item->price]);

                StockLog::create([
                    'stock_id' => $stock->id,
                    'invoice_id' => 'PUR' . str_pad($order->id, 7, '0', STR_PAD_LEFT),
                    'description' => 'Purchase Receipt',
                    'add_qty' => $item->quantity,
                    'date' => now()->toDateString(),
                    'time' => now()->toTimeString(),
                ]);
            }

            $order->update(['status' => 'D']); // Done

            return response()->json(['err' => 0, 'msg' => 'Purchase received and stock updated']);
        });
    }
}
