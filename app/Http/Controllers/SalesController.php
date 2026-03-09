<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleRecord;
use App\Models\SaleInvoice;
use App\Models\Table;
use Illuminate\Support\Facades\DB;

class SalesController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sale = Sale::with('branch', 'employee', 'customer')->where('status', '<>', 'X');
        if ($request->branch_id) $sale->where('branch_id', $request->branch_id); 
        return $sale->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    // app/Http/Controllers/Api/SalesController.php

    public function store(Request $request) {
        // 1. Create the Sales Header
        $sale = Sale::create([
            'branch_id' => $request->branch_id,
            'table_number' => $request->table_number,
            'floor_number' => $request->floor_number,
            'employee_id' => $request->user()->id,
            'customer_id' => $request->customer_id, // Default guest
            'date' => now()->format('Y-m-d'),
            'time' => now()->format('H:i:s'),
            'discount' => $request->discount,
            'tax' => $request->tax,
            'status' => 'O', // Order Open
        ]);

        // 2. Loop through ticket items
        foreach($request->items as $item) {
            SaleRecord::create([
                'sale_id' => $sale->id,
                'item_type' => 'product',
                'item_code' => $item['product-code'],
                'item_price' => $item['product-price'],
                'discount_pcnt' => 0,
                'discount_amnt' => 0,
                'item_note' => $item['note'],
                'item_status' => 'O', // Open
                'order_employee' => $request->user()->id,
                'order_date' => now()->format('Y-m-d'),
                'order_time' => now()->format('H:i:s')
            ]);
        }

        $table = Table::where('table_number', $request->table_number)->where('floor_number', $request->floor_number)->first();
        $table->update(['table_status' => 'O']);

        return response()->json(['message' => 'Order sent to kitchen', 'sale_id' => $sale->id]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Sale::with('branch', 'employee', 'customer')->findOrFail($request->sales_id);
    }
    
    public function checkout(Request $request) {
        $salesId = $request["sales-id"];
        $invoiceEmployee = $request["invoice_employee"];
        $salesTotal = (float)$request["payment-total"];
        $paymentTendered = isset($request["payment-cash"]) ? (float)$request["payment-cash"] : null;
        $cardEdc = $request["card-edc"] ?? null;
        $cardType = $request["card-type"] ?? null;
        $cardNumber = $request["no-kartu"] ?? null;
        $qrEdc = $request["qr-edc"] ?? null;

        return DB::transaction(function() use ($salesId, $invoiceEmployee, $salesTotal, $paymentTendered, $cardEdc, $cardType, $cardNumber, $qrEdc) {
            $sale = Sale::findOrFail($salesId);

            // 1. Create Invoice
            if (isset($cardEdc) && isset($cardNumber) && isset($cardType)) {
                SaleInvoice::create([
                    'sale_id' => $salesId,
                    'cardtype' => $cardType,
                    'paycard' => $cardNumber,
                    'paybank' => $cardEdc,
                    'payamount' => $salesTotal,
                    'employee_id' => $invoiceEmployee
                ]);
            } else if (isset($paymentTendered) && ($paymentTendered > 0 || ($paymentTendered == 0 && $salesTotal == 0))) {
                $paymentChange = $paymentTendered - $salesTotal;
                SaleInvoice::create([
                    'sale_id' => $salesId,
                    'payamount' => $paymentTendered,
                    'paychange' => $paymentChange,
                    'employee_id' => $invoiceEmployee
                ]);
            } else {
                return response()->json([
                    'err' => 1,
                    'msg' => "Payment not made due to missing details."
                ]);
            }

            // 2. Update Sale Status
            $sale->update(['status' => 'D']);

            // 3. Update Sale Items Status
            SaleRecord::where('sale_id', $salesId)
                ->where('item_status', '<>', 'X')
                ->update([
                    'item_status' => 'D',
                    'deliver_employee' => $invoiceEmployee
                ]);

            // 4. Release Table if no other unpaid orders
            $tableNumber = $sale->table_number;
            $floorNumber = $sale->floor_number;
            $salesBranch = $sale->branch_id;

            // The legacy logic checks for other sales on the same table that don't have an invoice yet
            $checkTableOrder = Sale::where('table_number', $tableNumber)
                ->where('floor_number', $floorNumber)
                ->where('branch_id', $salesBranch)
                ->where('status', '<>', 'X')
                ->whereDoesntHave('invoice')
                ->count();

            if ($checkTableOrder == 0) {
                Table::where('number', $tableNumber)
                    ->update(['status' => 'available', 'table_status' => 'V']);
            }

            return response()->json([
                'err' => 0, 
                'msg' => 'Success',
                'unpaid-order' => $checkTableOrder
            ]);
        });
    }

    public function splitSales(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $originalSale = Sale::findOrFail($request->sales_id);
            
            // Create new sale
            $newSale = Sale::create([
                'branch_id' => $originalSale->branch_id,
                'table_number' => $originalSale->table_number,
                'floor_number' => $originalSale->floor_number,
                'employee_id' => $request->employee_id,
                'customer_id' => $originalSale->customer_id,
                'date' => now()->toDateString(),
                'time' => now()->toTimeString(),
                'status' => 'O',
            ]);

            $itemsToMove = $request->input('items', []); // Array of record IDs and qtys
            foreach ($itemsToMove as $move) {
                $record = SaleRecord::findOrFail($move['id']);
                
                if ($record->qty > $move['qty']) {
                    // Split the record
                    $record->decrement('qty', $move['qty']);
                    
                    $newRecord = $record->replicate();
                    $newRecord->sale_id = $newSale->id;
                    $newRecord->qty = $move['qty'];
                    $newRecord->save();
                } else {
                    // Move entire record
                    $record->update(['sale_id' => $newSale->id]);
                }
            }

            return response()->json(['err' => 0, 'msg' => 'Order split successfully', 'new_sale_id' => $newSale->id]);
        });
    }

    public function mergeSales(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $sale1 = Sale::findOrFail($request->sale1_id);
            $sale2 = Sale::findOrFail($request->sale2_id);

            SaleRecord::where('sale_id', $sale2->id)->update(['sale_id' => $sale1->id]);
            $sale2->delete();

            return response()->json(['err' => 0, 'msg' => 'Orders merged successfully']);
        });
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
