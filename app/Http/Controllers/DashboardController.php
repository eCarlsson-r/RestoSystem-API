<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleInvoice;
use App\Models\SaleRecord;
use App\Models\Employee;
use App\Models\Product;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController
{
    public function landingPage()
    {
        return response()->json([
            'branches' => Branch::where('is_active', true)->get(),
            'featured_products' => Product::featured()->limit(6)->get(),
        ]);
    }

    public function index()
    {
        $date = now();
        
        $activeOrders = Sale::where('created_at', $date)
            ->whereNotIn('status', ['D', 'X'])
            ->count();

        $completedOrdersCount = SaleInvoice::whereHas('sale', function($query) use ($date) {
                $query->where('created_at', $date)->where('status', 'D');
            })->count();

        $todaySales = DB::table('sale_invoices')
            ->join('sales', 'sale_invoices.sale_id', '=', 'sales.id')
            ->where('sales.created_at', $date)
            ->select(DB::raw('SUM(pay_amount - pay_change) as total'))
            ->first()
            ->total ?? 0;

        $topItems = DB::table('sale_records')
            ->join('products', 'sale_records.item_code', '=', 'products.id') // item_id stores product_code
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereMonth('sale_records.created_at', now()->month)
            ->select('products.name', DB::raw('COUNT(*) as qty_sold'))
            ->groupBy('products.name')
            ->orderByDesc('qty_sold')
            ->limit(5)
            ->get();

        $topEmployee = DB::table('sale_records')
            ->join('employees', 'sale_records.order_employee', '=', 'employees.id')
            ->whereMonth('sale_records.created_at', now()->month)
            ->select('employees.name', DB::raw('COUNT(*) as count'))
            ->groupBy('employees.name')
            ->orderByDesc('count')
            ->first()
            ->name ?? '-';

        $monthlyIncome = DB::table('sales')
            ->join('sale_invoices', 'sales.id', '=', 'sale_invoices.sale_id')
            ->whereYear('sales.created_at', now()->year)
            ->select(DB::raw('MONTH(sales.created_at) as month'), DB::raw('SUM(pay_amount - pay_change) as amount'))
            ->groupBy('month')
            ->get();

        return response()->json([
            'err' => 0,
            'msg' => '',
            'data' => array([
                'active_orders' => $activeOrders,
                'completed_orders' => $completedOrdersCount,
                'today_sales' => $todaySales,
                'top_items' => $topItems,
                'top_employee' => $topEmployee,
                'monthly_income' => $monthlyIncome
            ])
        ]);
    }
}
