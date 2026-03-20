<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;
use App\Models\SaleRecord;
use App\Models\SaleInvoice;
use App\Models\Table;
use App\Events\StationNotification;

class SalesController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sale = Sale::with('branch', 'employee', 'customer', 'table', 'records.product', 'records.package')->where('status', '<>', 'X');
        if ($request->branch_id) $sale->where('branch_id', $request->branch_id); 

        return response()->json([
            'err' => 0,
            'msg' => '',
            'data' => $sale->get()
        ]);
    }

    public function getActiveCaptainOrder($salesId) {
        return Sale::find($salesId)->with('table', 'records.product', 'records.package')->get();
    }
    
    public function getCancellationReport(Request $request) {
        $start = $request->start_date;
        $end = $request->end_date . ' 23:59:59';

        $voids = Sale::where('branch_id', $request->branch_id)
            ->whereBetween('date', [$start, $end])
            ->whereIn('status', ['X'])
            ->with(['employee', 'records'])
            ->get();

        return $voids->map(function($sale) {
            return [
                'invoice' => $sale->sales_invoice,
                'time' => $sale->created_at->format('H:i'),
                'waiter' => $sale->employee->name,
                'authorized_by' => $sale->supervisor->name ?? 'N/A',
                'reason' => $sale->cancel_reason,
                'total_lost' => (float)$sale->total_final,
                'items' => $sale->records->map(fn($r) => $r->product_name)->join(', ')
            ];
        });
    }

    public function getSalesReport(Request $request) {
        $start = $request->start;
        $end = $request->end;
        $branchId = $request->branch;
        
        // 1. Identify if this is a station-specific view (e.g., Kitchen or Bar)
        $activeStation = $request->station;
        $user = $request->user();
        
        // Auto-detect station for non-Admin users based on username (e.g., BR01_KTCN) or type
        if (!$activeStation && $user && $user->type !== 'ADMIN') {
            $parts = explode('_', $user->username);
            foreach ($parts as $p) {
                if (in_array(strtoupper($p), ['KTCN', 'BART'])) {
                    $activeStation = strtoupper($p);
                    break;
                }
            }
        }

        // Base query for sales in this period/branch
        if ($branchId != 'ALL') {
            $salesQuery = Sale::whereBetween('date', [$start, $end])
                ->where('branch_id', $branchId);
        } else {
            $salesQuery = Sale::whereBetween('date', [$start, $end]);
        }

        // 2. Fetch delivered sales with filtered records
        $deliveredSalesQuery = (clone $salesQuery)->where('status', 'D');
        
        // If station filter is active, only include orders that have items for that station
        if ($activeStation) {
            $deliveredSalesQuery->whereHas('records', function($q) use ($activeStation) {
                $q->where(function($qq) use ($activeStation) {
                    $qq->whereHas('product.category', fn($cq) => $cq->where('kitchen_process', $activeStation))
                      ->orWhereHas('package.products.product.category', fn($cq) => $cq->where('kitchen_process', $activeStation));
                });
            });
        }

        $deliveredSales = $deliveredSalesQuery
            ->with(['records' => function($q) use ($activeStation) {
                if ($activeStation) {
                    $q->where(function($qq) use ($activeStation) {
                        $qq->whereHas('product.category', fn($cq) => $cq->where('kitchen_process', $activeStation))
                          ->orWhereHas('package.products.product.category', fn($cq) => $cq->where('kitchen_process', $activeStation));
                    });
                }
            }, 'records.product.category', 'records.package'])
            ->get();

        // 3. Count voided sales (also filtered by station)
        $voidSalesQuery = (clone $salesQuery)->where('status', 'X');
        if ($activeStation) {
            $voidSalesQuery->whereHas('records', function($q) use ($activeStation) {
                $q->where(function($qq) use ($activeStation) {
                    $qq->whereHas('product.category', fn($cq) => $cq->where('kitchen_process', $activeStation))
                      ->orWhereHas('package.products.product.category', fn($cq) => $cq->where('kitchen_process', $activeStation));
                });
            });
        }
        $voidCount = $voidSalesQuery->count();

        $totalSales = 0; // Gross
        $totalTax = 0;
        $allRecords = collect();

        foreach ($deliveredSales as $sale) {
            $saleGross = $sale->records->sum(fn($r) => $r->quantity * $r->item_price);
            $totalSales += $saleGross;
            $totalTax += ($saleGross * ($sale->tax / 100)); // Tax percentage from the Sale model
            $allRecords = $allRecords->concat($sale->records);
        }

        // 4. Group items for the table
        $items = $allRecords->groupBy(function($record) {
            return $record->item_type . '-' . $record->item_code;
        })->map(function($group) {
            $first = $group->first();
            
            $name = $first->item_type === 'package' 
                    ? ($first->package->name ?? 'Package #'.$first->item_code)
                    : ($first->product->name ?? 'Product #'.$first->item_code);

            return [
                'name'     => $name,
                'quantity' => (float)$group->sum('quantity'),
                'price'    => (float)$first->item_price, 
                'total'    => (float)$group->sum(function($r) { 
                    return $r->quantity * $r->item_price; 
                })
            ];
        })->values();

        return response()->json([
            'err' => 0,
            'msg' => $activeStation ? "Station Filter: $activeStation" : '',
            'data' => [
                'items' => $items,
                'summary' => [
                    'total_sales' => $totalSales,
                    'total_tax'   => $totalTax,
                    'net_revenue' => $totalSales - $totalTax,
                    'void_count'  => $voidCount,
                    'total_items' => $items->sum('quantity')
                ]
            ]
        ]);
    }

    public function getEmployeeSalesReport(Request $request) {
        $start = $request->start;
        $end = $request->end;
        $branchId = $request->branch;
        $employeeId = $request->employee_id;
        
        $user = $request->user();
        
        // Base query for sales in this period/branch
        if ($branchId != 'ALL') {
            $salesQuery = Sale::whereBetween('date', [$start, $end])
                ->where('branch_id', $branchId);
        } else {
            $salesQuery = Sale::whereBetween('date', [$start, $end]);
        }

        if ($employeeId && $employeeId != 'ALL') {
            $salesQuery->where('employee_id', $employeeId);
        }

        // 2. Fetch delivered sales with filtered records
        $deliveredSalesQuery = (clone $salesQuery)->where('status', 'D');
        
        $deliveredSales = $deliveredSalesQuery
            ->with(['records', 'records.product.category', 'records.package'])
            ->get();

        // 3. Count voided sales (also filtered by station)
        $voidSalesQuery = (clone $salesQuery)->where('status', 'X');
        $voidCount = $voidSalesQuery->count();

        $totalSales = 0; // Gross
        $totalTax = 0;
        $allRecords = collect();

        foreach ($deliveredSales as $sale) {
            $saleGross = $sale->records->sum(fn($r) => $r->quantity * $r->item_price);
            $totalSales += $saleGross;
            $totalTax += ($saleGross * ($sale->tax / 100)); // Tax percentage from the Sale model
            $allRecords = $allRecords->concat($sale->records);
        }

        // 4. Group items for the table
        $items = $allRecords->groupBy(function($record) {
            return $record->item_type . '-' . $record->item_code;
        })->map(function($group) {
            $first = $group->first();
            
            $name = $first->item_type === 'package' 
                    ? ($first->package->name ?? 'Package #'.$first->item_code)
                    : ($first->product->name ?? 'Product #'.$first->item_code);

            return [
                'name'     => $name,
                'quantity' => (float)$group->sum('quantity'),
                'price'    => (float)$first->item_price, 
                'total'    => (float)$group->sum(function($r) { 
                    return $r->quantity * $r->item_price; 
                })
            ];
        })->values();

        return response()->json([
            'err' => 0,
            'msg' => '',
            'data' => [
                'items' => $items,
                'summary' => [
                    'total_sales' => $totalSales,
                    'total_tax'   => $totalTax,
                    'net_revenue' => $totalSales - $totalTax,
                    'void_count'  => $voidCount,
                    'total_items' => $items->sum('quantity')
                ]
            ]
        ]);
    }

    public function getInvoiceReport(Request $request) {
        $start = $request->start;
        $end = $request->end;
        $branchId = $request->branch;

        // 1. Fetch sales with related records and invoices
        $salesQuery = Sale::whereBetween('date', [$start, $end])
            ->where('status', 'D')
            ->when($branchId != 'ALL', fn($q) => $q->where('branch_id', $branchId));

        $sales = $salesQuery->with(['records', 'invoices'])->get();

        // 2. Group by date and calculate daily aggregates
        $reportData = $sales->groupBy('date')->map(function ($daySales, $date) {
            $dailySubtotal = 0;
            $dailyDiscount = 0;
            $dailyTax = 0;
            $dailyTotal = 0;

            $payments = [
                'cash' => 0,
                'credit-card' => 0,
                'debit-card' => 0,
                'ovo' => 0,
                'go-pay' => 0,
                'total-payment' => 0
            ];

            foreach ($daySales as $sale) {
                // Calculate Sale Totals
                $saleGross = $sale->records->sum(fn($r) => $r->quantity * $r->item_price);
                $saleDiscount = $saleGross * ($sale->discount / 100);
                $saleAfterDiscount = $saleGross - $saleDiscount;
                $saleTax = $saleAfterDiscount * ($sale->tax / 100);
                $saleFinal = $saleAfterDiscount + $saleTax;

                $dailySubtotal += $saleGross;
                $dailyDiscount += $saleDiscount;
                $dailyTax += $saleTax;
                $dailyTotal += $saleFinal;

                // Categorize Payments
                foreach ($sale->invoices as $invoice) {
                    $amount = (float)($invoice->pay_amount - $invoice->pay_change);
                    $payments['total-payment'] += $amount;

                    if ($invoice->pay_method === 'CASH') {
                        $payments['cash'] += $amount;
                    } else if ($invoice->pay_method === 'QRIS') {
                        $payments['qris'] += $amount;
                    } else if ($invoice->pay_method === 'VOUCHER') {
                        $payments['voucher'] += $amount;
                    } else {
                        $bank = strtoupper($invoice->pay_bank ?? '');
                        $cardType = strtoupper($invoice->card_type ?? '');

                        if ($cardType === 'CR') {
                            $payments['credit-card'] += $amount;
                        } else {
                            $payments['debit-card'] += $amount;
                        }
                    }
                }
            }

            return array_merge([
                'date' => $date,
                'subtotal' => $dailySubtotal,
                'discount' => $dailyDiscount,
                'tax' => $dailyTax,
                'total-price' => $dailyTotal
            ], $payments);
        })->values();

        return response()->json([
            'err' => 0,
            'msg' => '',
            'data' => [
                'items' => $reportData
            ]
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
                    'employee_id' => $request->user()->employee->id ?? $request->user()->id,
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
                    'sale_id' => $salesId,
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
                'sale_id' => $salesId
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
        $salesId = $request["sales_id"];
        $invoiceEmployee = $request->user()->employee->id ?? $request->user()->id;
        $totalToPay = (float)$request["total"];
        $payments = $request["payments"]; // Expecting array of payment objects

        return DB::transaction(function() use ($salesId, $invoiceEmployee, $totalToPay, $payments) {
            $sale = Sale::findOrFail($salesId);
            $totalPaid = 0;

            foreach ($payments as $p) {
                $method = $p['method'];
                $amount = (float)$p['amount'];
                
                $invoiceData = [
                    'sale_id' => $salesId,
                    'pay_method' => $method,
                    'employee_id' => $invoiceEmployee,
                    'pay_amount' => $amount,
                ];

                if ($method === 'CASH') {
                    $tendered = (float)$p['tendered'];
                    $invoiceData['pay_amount'] = $tendered;
                    $invoiceData['pay_change'] = $tendered - $amount;
                    $totalPaid += $amount;
                } else if ($method === 'CARD' || $method === 'QRIS') {
                    $invoiceData['card_type'] = $p['card_type'] ?? null;
                    $invoiceData['pay_card'] = $p['card_number'] ?? null;
                    $invoiceData['pay_bank'] = $p['pay_bank'] ?? null;
                    $totalPaid += $amount;
                } else if ($method === 'VOUCHER') {
                    $invoiceData['voucher'] = $p['voucher_code'] ?? null;
                    Voucher::where('code', $p['voucher_code'])->update(['status' => 'REDEEMED']);
                    $totalPaid += $amount;
                }

                SaleInvoice::create($invoiceData);
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
            $tableId = $sale->table_id;
            $salesBranch = $sale->branch_id;

            $checkTableOrder = Sale::where('table_id', $tableId)
                ->where('branch_id', $salesBranch)
                ->where('status', '<>', 'X')
                ->whereDoesntHave('invoices')
                ->count();

            if ($checkTableOrder == 0) {
                Table::findOrFail($tableId)->update(['status' => 'available']);
            }

            // Trigger Kitchen Notification
            broadcast(new StationNotification('kitchen', [
                'title' => 'Pesanan Baru',
                'type' => 'pendingorder',
                'sales-id' => $sale->id,
                'table-number' => $sale->table_number,
                'body' => "Pesanan di Meja {$sale->table_number}"
            ]));

            // Trigger Admin Notification
            broadcast(new StationNotification('admin', [
                'title' => 'Pesanan Baru',
                'type' => 'sales',
                'body' => "Pesanan di Meja {$sale->table_number}"
            ]));

            return response()->json([
                'err' => 0, 
                'msg' => 'Success',
                'data' => Sale::with('branch', 'employee', 'customer', 'records.product', 'records.package', 'invoices')->where('id', $salesId)->get(),
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
                
                if ($record->qty > $move['quantity']) {
                    // Split the record
                    $record->decrement('quantity', $move['quantity']);
                    
                    $newRecord = $record->replicate();
                    $newRecord->sale_id = $newSale->id;
                    $newRecord->qty = $move['quantity'];
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
}
