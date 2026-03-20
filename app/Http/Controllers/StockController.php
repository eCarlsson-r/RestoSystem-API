<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\StockLog;
use App\Models\StockMovement;
use App\Models\StockMovementRecord;
use App\Models\KitchenRequest;
use App\Models\KitchenRequestItem;
use App\Events\StationNotification;
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
        $branch = $request->branch;
        $storage = $request->storage;
        $start = $request->start . ' 00:00:00';
        $end = $request->end . ' 23:59:59';

        // 1. Get the relevant stock items
        $query = Stock::with(['ingredient', 'utility']);
        
        if ($branch != 0) {
            $query->where('branch_id', $branch)->where('storage', $storage);
        }

        $stocks = $query->get();

        // 2. Group by item_type and item_code to combine across branches/storages
        $report = $stocks->groupBy(function($stock) {
            return $stock->item_type . '-' . $stock->item_code;
        })->map(function($group) use ($start, $end) {
            $first = $group->first();
            // Get all stock IDs for this specific item (can be multiple branches/storages)
            $stockIds = $group->pluck('id');

            // 3. Sum the logs for ALL stock IDs in this group
            // Opening Balance: Sum of logs BEFORE start date
            $opening = StockLog::whereIn('stock_id', $stockIds)
                ->where('created_at', '<', $start)
                ->selectRaw('SUM(add_qty - get_qty) as balance')
                ->value('balance') ?? 0;

            // Activity during period
            $activity = StockLog::whereIn('stock_id', $stockIds)
                ->whereBetween('created_at', [$start, $end])
                ->selectRaw('SUM(add_qty) as qty_in, SUM(get_qty) as qty_out')
                ->first();

            $in = $activity->qty_in ?? 0;
            $out = $activity->qty_out ?? 0;

            return [
                'item_code' => $first->item_code,
                'item_name' => $first->item_type === 'INGR' 
                            ? $first->ingredient->name 
                            : $first->utility->name,
                'opening' => (float)$opening,
                'qty_in'  => (float)$in,
                'qty_out' => (float)$out,
                'closing' => (float)($opening + $in - $out),
                'unit'    => $first->item_type === 'INGR' 
                            ? $first->ingredient->unit 
                            : 'pcs'
            ];
        })->values();

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
                'description' => $request->input('description', 'Stock Update'),
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
                    'quantity' => $item['quantity'],
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
                    $fromStock->decrement('quantity', $record->quantity);

                    $storage = match ($movement->from_storage) {
                        "KTCN" => "Kitchen",
                        "BART" => "Bartender",
                        "MAIN" => "Main Storage"
                    }; 
                    StockLog::create([
                        'stock_id' => $fromStock->id,
                        'invoice_id' => 'MOV' . str_pad($movement->id, 7, '0', STR_PAD_LEFT),
                        'description' => 'Moved to ' . $movement->to_branch->name . ' ' . $storage,
                        'get_qty' => $record->quantity,
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
                    'purchase_price' => $fromStock->purchase_price,
                    'quantity' => 0
                ]);

                $toStock->increment('quantity', $record->quantity);

                $storage = match ($movement->to_storage) {
                    "KTCN" => "Kitchen",
                    "BART" => "Bartender",
                    "MAIN" => "Main Storage"
                }; 
                StockLog::create([
                    'stock_id' => $toStock->id,
                    'invoice_id' => 'MOV' . str_pad($movement->id, 7, '0', STR_PAD_LEFT),
                    'description' => 'Received from ' . $movement->from_branch->name . ' ' . $storage,
                    'add_qty' => $record->quantity,
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
        $branchId = $request->input('branch_id');
        $storage = $request->input('storage');
        $startDate = $request->input('start');
        $endDate = $request->input('end');

        $stock = Stock::where([
            'item_type' => $itemType,
            'item_code' => $itemCode,
            'branch_id' => $branchId,
            'storage' => $storage
        ])->first();

        if (!$stock) {
            return response()->json([
                'opening_balance' => 0,
                'closing_balance' => 0,
                'data' => []
            ]);
        }
        
        $stockId = $stock->id;

        // 1. Calculate the 'Beginning Balance' (Opening Balance)
        // Sum of all (IN - OUT) before the start date
        $openingBalance = StockLog::where('stock_id', $stockId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('SUM(add_qty - get_qty) as balance')
            ->first()->balance ?? 0;

        // 2. Fetch the logs for the requested period
        $logs = StockLog::where('stock_id', $stockId)
            ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
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
        $request = KitchenRequest::with('from_branch', 'to_branch', 'items', 'items.ingredient', 'items.utility')->get();
        return response()->json([
            'err' => 0,
            'msg' => '',
            'data' => $request
        ]);
    }

    public function storeKitchenRequest(Request $request) {
        return DB::transaction(function () use ($request) {
            // 1. Rename variable to $kitchenRequest to avoid shadowing the incoming $request
            $kitchenRequest = KitchenRequest::create([
                'from_branch_id' => $request->from_branch,
                'from_storage' => $request->from_storage,
                'to_branch_id' => $request->to_branch,
                'to_storage' => $request->to_storage,
                'date' => now()->toDateString(),
                'time' => now()->toTimeString(),
                'status' => 'Q',
            ]);

            // 2. Instead of a manual loop, use the relationship to "execute" the creation
            // This is the equivalent of your L268-L273 logic
            $kitchenRequest->items()->createMany($request->items);

            // 3. Update the rest of the function to use $request for input 
            // and $kitchenRequest for the model instance
            $fromStorage = match ($request->from_storage) {
                "KTCN" => "Kitchen",
                "BART" => "Bartender",
                "MAIN" => "Main Storage"
            };

            broadcast(new StationNotification("admin", [
                'title' => "New Request",
                'type' => 'request',
                'request_id' => $kitchenRequest->id,
                'body' => "New request from {$fromBranch} {$fromStorage}"
            ]));

            return response()->json([
                'err' => 0,
                'msg' => 'Request stored successfully',
                'data' => $kitchenRequest
            ]);
        });
    }

    public function approveRequest(Request $request) {
        $id = $request->id;
        $request = KitchenRequest::with('from_branch', 'to_branch', 'items', 'items.ingredient', 'items.utility')->findOrFail($id);
        $request->update(['status' => 'R', 'respond_date' => now()->toDateString(), 'respond_time' => now()->toTimeString()]);

        $movement = StockMovement::create([
            'from_branch_id' => $request->from_branch_id,
            'from_storage' => $request->from_storage,
            'to_branch_id' => $request->to_branch_id,
            'to_storage' => $request->to_storage,
            'date' => now()->toDateString(),
            'time' => now()->toTimeString(),
            'status' => 'M', // Moving
        ]);

        $items = $request->items;
        foreach ($items as $item) {
            StockMovementRecord::create([
                'movement_id' => $movement->id,
                'item_type' => $item['item_type'],
                'item_code' => $item['item_code'],
                'quantity' => $item['quantity'],
            ]);
        }

        return response()->json([
            'err' => 0,
            'msg' => 'Request approved',
            'data' => $request
        ]);
    }

    public function rejectRequest(Request $request) {
        $id = $request->id;
        $request = KitchenRequest::with('from_branch', 'to_branch', 'items', 'items.ingredient', 'items.utility')->findOrFail($id);
        $request->update(['status' => 'C']);
        return response()->json([
            'err' => 0,
            'msg' => 'Request rejected',
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
