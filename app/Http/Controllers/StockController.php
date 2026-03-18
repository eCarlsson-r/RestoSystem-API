<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\StockLog;
use App\Models\StockMovement;
use App\Models\StockMovementRecord;
use App\Models\KitchenRequest;
use App\Models\KitchenRequestItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController
{
    public function index(Request $request)
    {
        $query = Stock::query();
        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->has('storage')) {
            $query->where('storage', $request->storage);
        }
        $stocks = $query->with(['branch', 'ingredient', 'utility'])->get();

        return response()->json([
            'err' => 0,
            'msg' => '',
            'data' => $stocks
        ]);
    }
    
    public function getStockMutation(Request $request) {
        $branch = $request->branch_code;
        $storage = $request->storage_code;
        $start = $request->start_date;
        $end = $request->end_date . ' 23:59:59';

        // 1. Get all stock items for this branch/storage
        $stocks = Stock::where('branch_id', $branch)
            ->where('storage', $storage)
            ->with(['ingredient', 'utility']) // Eager load names
            ->get();

        $report = $stocks->map(function($stock) use ($start, $end) {
            // 2. Opening Balance: Sum of logs BEFORE start date
            $opening = StockLog::where('stock_id', $stock->id)
                ->where('created_at', '<', $start)
                ->selectRaw('SUM(add_qty - get_qty) as balance')
                ->value('balance') ?? 0;

            // 3. Activity during period
            $activity = StockLog::where('stock_id', $stock->id)
                ->whereBetween('created_at', [$start, $end])
                ->selectRaw('SUM(add_qty) as qty_in, SUM(get_qty) as qty_out')
                ->first();

            $in = $activity->qty_in ?? 0;
            $out = $activity->qty_out ?? 0;

            return [
                'item_code' => $stock->item_code,
                'item_name' => $stock->item_type === 'INGR' 
                            ? $stock->ingredient->name 
                            : $stock->utility->name,
                'opening' => (float)$opening,
                'qty_in'  => (float)$in,
                'qty_out' => (float)$out,
                'closing' => (float)($opening + $in - $out),
                'unit'    => $stock->item_type === 'INGR' 
                            ? $stock->ingredient->unit 
                            : 'pcs'
            ];
        });

        return response()->json([
            'err' => 0,
            'msg' => '',
            'data' => $report
        ]);
    }

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $stock = Stock::updateOrCreate(
                [
                    'item_type' => $request->item_type,
                    'item_code' => $request->item_code,
                    'branch_id' => $request->branch_id,
                    'storage' => $request->storage,
                ],
                [
                    'purchase_price' => $request->purchase_price,
                    'quantity' => DB::raw("quantity + " . $request->quantity),
                ]
            );

            $stock->refresh();

            StockLog::create([
                'stock_id' => $stock->id,
                'desc' => $request->input('desc', 'Stock Update'),
                'add_qty' => $request->quantity,
                'date' => now()->toDateString(),
                'time' => now()->toTimeString(),
            ]);

            return response()->json(['err' => 0, 'msg' => 'Stock updated', 'data' => $stock]);
        });
    }

    public function move(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $movement = StockMovement::create([
                'from_branch_id' => $request->from_branch_id,
                'from_storage' => $request->from_storage,
                'to_branch_id' => $request->to_branch_id,
                'to_storage' => $request->to_storage,
                'date' => now()->toDateString(),
                'time' => now()->toTimeString(),
                'status' => 'M', // Moving
            ]);

            $items = $request->input('items', []);
            foreach ($items as $item) {
                StockMovementRecord::create([
                    'movement_id' => $movement->id,
                    'item_type' => $item['item_type'],
                    'item_code' => $item['item_code'],
                    'qty' => $item['qty'],
                ]);
            }

            return response()->json(['err' => 0, 'msg' => 'Movement created', 'movement_id' => $movement->id]);
        });
    }

    public function receive($id)
    {
        return DB::transaction(function () use ($id) {
            $movement = StockMovement::with('records')->findOrFail($id);

            if ($movement->status === 'R') {
                return response()->json(['err' => 1, 'msg' => 'Already received']);
            }

            foreach ($movement->records as $record) {
                // Deduct from origin
                $fromStock = Stock::where([
                    'item_type' => $record->item_type,
                    'item_code' => $record->item_code,
                    'branch_id' => $movement->from_branch_id,
                    'storage' => $movement->from_storage,
                ])->first();

                if ($fromStock) {
                    $fromStock->decrement('quantity', $record->qty);
                    StockLog::create([
                        'stock_id' => $fromStock->id,
                        'invoice_id' => 'MOV' . str_pad($movement->id, 7, '0', STR_PAD_LEFT),
                        'desc' => 'Moved to ' . $movement->to_branch_id,
                        'get_qty' => $record->qty,
                        'date' => now()->toDateString(),
                        'time' => now()->toTimeString(),
                    ]);
                }

                // Add to destination
                $toStock = Stock::firstOrCreate([
                    'item_type' => $record->item_type,
                    'item_code' => $record->item_code,
                    'branch_id' => $movement->to_branch_id,
                    'storage' => $movement->to_storage,
                ]);

                $toStock->increment('quantity', $record->qty);
                StockLog::create([
                    'stock_id' => $toStock->id,
                    'invoice_id' => 'MOV' . str_pad($movement->id, 7, '0', STR_PAD_LEFT),
                    'desc' => 'Received from ' . $movement->from_branch_id,
                    'add_qty' => $record->qty,
                    'date' => now()->toDateString(),
                    'time' => now()->toTimeString(),
                ]);
            }

            $movement->update(['status' => 'R']);

            return response()->json(['err' => 0, 'msg' => 'Stock received successfully']);
        });
    }

    public function getStockCard(Request $request) 
    {
        $itemType = $request->input('item_type');
        $itemCode = $request->input('item_code');
        $startDate = $request->input('start');
        $endDate = $request->input('end');

        $stockId = Stock::where([
            'item_type' => $itemType,
            'item_code' => $itemCode,
        ])->first()->id;

        // 1. Calculate the 'Beginning Balance' (Opening Balance)
        // Sum of all (IN - OUT) before the start date
        $openingBalance = StockLog::where('stock_id', $stockId)
            ->where('created_at', '<', $startDate)
            ->selectRaw('SUM(add_qty - get_qty) as balance')
            ->first()->balance ?? 0;

        // 2. Fetch the logs for the requested period
        $logs = StockLog::where('stock_id', $stockId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'asc')
            ->get();

        // 3. Compute running balance
        $runningBalance = $openingBalance;
        $reportData = $logs->map(function ($log) use (&$runningBalance) {
            $runningBalance += ($log->add_qty - $log->get_qty);
            
            return [
                'date' => $log->created_at->format('Y-m-d H:i'),
                'reference' => $log->reference,
                'description' => $log->description,
                'in' => $log->add_qty,
                'out' => $log->get_qty,
                'balance' => $runningBalance,
            ];
        });

        return response()->json([
            'opening_balance' => $openingBalance,
            'closing_balance' => $runningBalance,
            'data' => $reportData
        ]);
    }

    public function kitchenRequest(Request $request)
    {
        $request = KitchenRequest::with('from_branch', 'to_branch')->get();
        return response()->json([
            'err' => 0,
            'msg' => '',
            'data' => $request
        ]);
    }

    public function transfers()
    {
        $transfers = StockMovement::with([
            'from_branch',
            'to_branch',
            'records'
        ])->get();

        return response()->json([
            'err' => 0,
            'msg' => '',
            'data' => $transfers
        ]);
    }

    // Laravel Logic for Variance Report
    public function getVariance(Request $request) {
        $stock = Stock::where('item_type', $request->item_type)
            ->where('item_code', $request->item_code)
            ->where('branch_id', $request->branch_id)
            ->where('storage', $request->storage)->latest();

        if (!$stock) {
            return response()->json([
                'err' => 1,
                'msg' => 'Stock not found',
                'data' => null
            ]);
        }

        $expected = StockLog::where('stock_id', $stock->id)->sum('qty_change');
        $actual = $stock->first()->qty;
        
        return response()->json([
            'err' => 0,
            'msg' => '',
            'data' => [
                'item' => $stock->item_code,
                'expected' => $expected,
                'actual' => $actual,
                'loss' => $expected - $actual,
                'loss_value' => ($expected - $actual) * $stock->purchase_price
            ]
        ]);
    }
}
