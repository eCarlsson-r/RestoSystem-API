<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\StockLog;
use App\Models\StockMovement;
use App\Models\StockMovementRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $query = Stock::query();
        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        
        $stocks = $query->get();

        return response()->json([
            'err' => 0,
            'msg' => '',
            'data' => $stocks
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
}
