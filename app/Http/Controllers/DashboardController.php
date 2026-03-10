<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleInvoice;
use App\Models\SaleRecord;
use App\Models\Employee;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController
{
    public function index()
    {
        $date = now()->toDateString();
        
        $activeOrders = Sale::where('date', $date)
            ->whereNotIn('status', ['D', 'X'])
            ->count();

        $completedOrdersCount = SaleInvoice::whereHas('sale', function($query) use ($date) {
                $query->where('date', $date)->where('status', 'D');
            })->count();

        $todaySales = DB::table('sale_invoices')
            ->join('sales', 'sale_invoices.sale_id', '=', 'sales.id')
            ->where('sales.date', $date)
            ->select(DB::raw('SUM(pay_amount - pay_change) as total'))
            ->first()
            ->total ?? 0;

        $topFood = DB::table('sale_records')
            ->join('products', 'sale_records.item_id', '=', 'products.code') // item_id stores product_code
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereMonth('sale_records.date', now()->month)
            ->where('categories.kitchen_process', 'KTCN')
            ->select('products.name', DB::raw('COUNT(*) as count'))
            ->groupBy('products.name')
            ->orderByDesc('count')
            ->first()
            ->name ?? '-';

        $topBeverage = DB::table('sale_records')
            ->join('products', 'sale_records.item_id', '=', 'products.code')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereMonth('sale_records.date', now()->month)
            ->where('categories.kitchen_process', 'BART')
            ->select('products.name', DB::raw('COUNT(*) as count'))
            ->groupBy('products.name')
            ->orderByDesc('count')
            ->first()
            ->name ?? '-';

        $topEmployee = DB::table('sale_records')
            ->join('employees', 'sale_records.employee_id', '=', 'employees.id')
            ->whereMonth('sale_records.date', now()->month)
            ->select('employees.name', DB::raw('COUNT(*) as count'))
            ->groupBy('employees.name')
            ->orderByDesc('count')
            ->first()
            ->name ?? '-';

        $monthlyIncome = DB::table('sales')
            ->join('sale_invoices', 'sales.id', '=', 'sale_invoices.sale_id')
            ->whereYear('sales.date', now()->year)
            ->select(DB::raw('MONTH(sales.date) as month'), DB::raw('SUM(pay_amount - pay_change) as amount'))
            ->groupBy('month')
            ->get();

        return response()->json([
            'err' => 0,
            'msg' => '',
            'data' => [
                'active-orders' => $activeOrders,
                'completed-orders' => $completedOrdersCount,
                'today-sales' => $todaySales,
                'top-food' => $topFood,
                'top-beverage' => $topBeverage,
                'top-employee' => $topEmployee,
                'monthly-income' => $monthlyIncome
            ]
        ]);
    }
}
