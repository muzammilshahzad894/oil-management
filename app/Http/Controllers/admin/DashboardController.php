<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Customer;
use App\Models\Sale;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $totalCustomers = Customer::count();

        // Today's Sales: total invoiced (sum of price) for sales where sale_date is today
        $todaySales = Sale::whereDate('sale_date', Carbon::today())->sum('price');

        // Monthly Profit: same logic as profitLoss - sales for current month, profit = totalInvoiced - totalCost
        $monthStart = Carbon::now()->startOfMonth()->format('Y-m-d');
        $monthEnd = Carbon::now()->format('Y-m-d');
        $monthlySales = Sale::whereBetween('sale_date', [$monthStart, $monthEnd])->get();
        $totalInvoiced = $monthlySales->sum('price');
        $totalCost = $monthlySales->sum(fn($s) => $s->total_cost ?? 0);
        $monthlyProfit = $totalInvoiced - (float) $totalCost;

        $recentSales = Sale::with([
            'customer' => function($q) {
                $q->withTrashed();
            },
            'brand' => function($q) {
                $q->withTrashed();
            }
        ])->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $lowStock = Brand::withSum('inventoryBatches', 'quantity_remaining')
            ->get()
            ->filter(fn($b) => (int) ($b->inventory_batches_sum_quantity_remaining ?? 0) < 10)
            ->sortBy('inventory_batches_sum_quantity_remaining')
            ->values();

        return view('admin.dashboard', compact(
            'todaySales',
            'monthlyProfit',
            'totalCustomers',
            'recentSales',
            'lowStock'
        ));
    }
}
