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
        $sale = Sale::with('branch', 'employee', 'customer', 'records.product', 'records.package')->where('status', '<>', 'X');
        if ($request->branch_id) $sale->where('branch_id', $request->branch_id); 

        return response()->json([
            'err' => 0,
            'msg' => '',
            'data' => $sale->get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        return DB::transaction(function() use ($request) {
            $salesId = $request->input('sales_id');
            $branch = $request->input('branch_id');
            
            // 1. If no sales_id, create the header (First order for this table)
            if (!$salesId) {
                $sale = Sale::create([
                    'branch_id' => $branch,
                    'table_id' => $request->input('table_id'),
                    'employee_id' => $request->user()->id,
                    'customer_id' => $request->input('customer_id', 1),
                    'date' => now()->toDateString(),
                    'time' => now()->toTimeString(),
                    'status' => 'O',
                ]);
                $salesId = $sale->id;

                // Mark table as Occupied
                Table::findOrFail($request->input('table_id'))->update(['status' => 'occupied']);
            }

            // 2. Add the items (The "Incremental" part)
            $kitchenItems = 0;
            $barItems = 0;

            foreach ($request->items as $item) {
                // Note: Each item in the loop is a single sales-record
                SaleRecord::create([
                    'sales_id' => $salesId,
                    'item_type' => $item['item_type'], // product or package
                    'item-code' => $item['item_code'],
                    'item_note' => $item['item_note'] ?? '',
                    'item_status' => 'O',
                    'item_price' => $item['price'],
                    'order_employee' => $request->user()->id
                ]);

                // Check routing for notifications
                if ($item['kitchen_process'] === 'KTCN') $kitchenItems++;
                if ($item['kitchen_process'] === 'BART') $barItems++;
            }

            // 3. Trigger Notifications (Laravel Reverb or WebPush)
            // This is where you notify the KDS we built earlier
            $this->notifyStations($branch, $kitchenItems, $barItems, $salesId);

            return response()->json([
                'status' => 'success', 
                'sales_id' => $salesId
            ]);
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Sale::with('branch', 'employee', 'customer', 'records.product', 'records.package')->findOrFail($id);
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
            $originalSale = Sale::findOrFail($request->original_sales_id);
            
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

            $itemsToMove = $request->input('record_ids', []); // Array of record IDs and qtys
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

    // app/Http/Controllers/Api/SalesController.php

    public function moveTable(Request $request) {
        return DB::transaction(function() use ($request) {
            $oldTable = $request->old_table;
            $newTable = $request->new_table;

            // 1. Find active sale
            $sale = Sale::where('table_number', $oldTable)->where('status', 'O')->first();
            
            // 2. Update Sale record
            $sale->update(['table_number' => $newTable]);

            // 3. Update Table Statuses
            DB::table('tables')->where('table_number', $oldTable)->update(['status' => 'V']);
            DB::table('tables')->where('table_number', $newTable)->update(['status' => 'O']);

            return response()->json(['message' => 'Table moved successfully']);
        });
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    public function updateCustomer(Request $request) {
        $sale = Sale::findOrFail($request->sales_id);
        $customer = Customer::findOrFail($request->customer_id);

        $sale->update([
            'sales-customer' => $customer->id,
            // We can store a snapshot of tax at the time of sale
            'sales-tax-percent' => $customer->{'tax'} 
        ]);

        return response()->json(['message' => 'Customer linked', 'tax' => $customer->{'tax'}]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
