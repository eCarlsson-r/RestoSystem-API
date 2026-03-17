<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SaleRecord;
use App\Models\Sale;

class KitchenController
{
    private $notificationService;

    public function __construct(NotificationService $notificationService) {
        $this->notificationService = $notificationService;
    }
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
    public function updateStatus(Request $request, string $id)
    {
        $sale = Sale::findOrFail($id);
        $sale->status = $request->status;
        $sale->save();

        // Broadcast to the Waiters
        $this->notificationService->notifyOrderReady($sale);

        return response()->json([
            'err' => 0,
            'msg' => '',
            'data' => $sale
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
