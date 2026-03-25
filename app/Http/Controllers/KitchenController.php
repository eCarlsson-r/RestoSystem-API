<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PrepareLog;
use App\Models\Prepare;
use App\Models\Sale;
use App\Models\SaleRecord;
use App\Models\Stock;
use App\Models\StockLog;
use App\Models\StockMovement;
use App\Events\StationNotification;

class KitchenController
{
    /**
     * Display a listing of the resource.
     */
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
                    'customer_name' => $header->customer->{'name'},
                    'created_at' => $header->created_at,
                    'status' => $header->status,
                    'time_elapsed' => round(now()->diffInMinutes($items->first()->{'order_time'})),
                    'items' => $items->map(fn($i) => [
                        'id' => $i->{'id'},
                        'name' => $i->product->{'name'},
                        'quantity' => $i->{'quantity'} ?? 1,
                        'item_status' => $i->{'item_status'},
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

    public function approvedRequests(Request $request) {
        $branch = $request->branch;
        $station = $request->station;

        $transfers = StockMovement::with([
            'from_branch',
            'to_branch',
            'records',
            'records.ingredient',
            'records.utility'
        ])->where('to_branch_id', $branch)->where('to_storage', $station)->where('status', 'M')->get();

        return response()->json([
            'err' => 0,
            'msg' => '',
            'data' => $transfers
        ]);
    }

    public function getCaptainTicket($salesId)
    {
        return DB::transaction(function () use ($salesId) {
            $order = Sale::with(['branch', 'table'])->findOrFail($salesId);

            // Fetch items that haven't been printed yet
            $newItems = $order->items()
                ->whereNull('printed_at')
                ->with(['product', 'package'])
                ->get();

            if ($newItems->isEmpty()) {
                return response()->json(['message' => 'No new items to print'], 404);
            }

            // Mark them as printed immediately
            $order->items()
                ->whereNull('printed_at')
                ->update(['printed_at' => now()]);

            // Attach only the new items to the order object for the frontend
            $order->setRelation('records', $newItems);

            return response()->json(['data' => $order]);
        });
    }

    public function prepare(Request $request)
    {
        $request->validate([
            "prepare_code" => "required",
            "branch_code" => "required",
            "storage" => "required",
            "qty" => "required",
            "note" => "nullable"
        ]);

        $prepare = Prepare::findOrFail($request->prepare_code);
        $qty = $request->qty;
        $note = $request->note;

        foreach($prepare->recipe as $item) {
            $stock = Stock::where([
                'item_type' => $item->item_type,
                'item_code' => $item->item_code,
                'branch_id' => $request->branch_code,
                'storage' => $request->storage,
            ])->first();

            if (!$stock) {
                return response()->json([
                    'err' => 1,
                    'msg' => 'Stock not found'
                ]);
            }

            if ($stock->quantity < $item->quantity * $qty) {
                return response()->json([
                    'err' => 1,
                    'msg' => 'Stock not enough'
                ]);
            }
            
            $stock->decrement('quantity', $item->quantity * $qty);
            StockLog::create([
                'stock_id' => $stock->id,
                'invoice_id' => 'PRP' . now()->toDateString(),
                'description' => $note,
                'get_qty' => $item->quantity * $qty,
                'date' => now()->toDateString(),
                'time' => now()->toTimeString(),
            ]);
        }

        $prepareLog = PrepareLog::create([
            'prepare_id' => $prepare->{'id'},
            'qty' => $qty,
            'note' => $note,
            'branch_id' => $request->branch_code,
            'storage' => $request->storage,
            'date' => now()->toDateString(),
            'time' => now()->toTimeString()
        ]);

        return response()->json([
            'err' => 0,
            'msg' => '',
            'data' => $prepareLog
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateStatus(Request $request, string $id)
    {
        $sale = Sale::findOrFail($id);
        $sale->status = $request->status;
        $sale->save();

        // Broadcast to the Waiters
        StationNotification::notifySubscribers("branch.{$sale->branch_id}", [
            'title' => 'Pesanan Siap Dihidangkan',
            'type' => 'sales',
            'sales-id' => $id,
            'body' => "Meja {$sale->table->table_number} Lantai {$sale->table->floor_number} siap!"
        ]);

        return response()->json([
            'err' => 0,
            'msg' => '',
            'data' => $sale
        ]);
    }
}
