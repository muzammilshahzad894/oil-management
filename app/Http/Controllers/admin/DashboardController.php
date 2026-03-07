<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Sale;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Today: sales and profit
        $todaySalesCollection = Sale::whereDate('sale_date', Carbon::today())->get();
        $todaySales = $todaySalesCollection->sum('price');
        $todayCost = $todaySalesCollection->sum(fn($s) => $s->total_cost ?? 0);
        $todayProfit = $todaySales - (float) $todayCost;

        // Current month: sales and profit
        $monthStart = Carbon::now()->startOfMonth()->format('Y-m-d');
        $monthEnd = Carbon::now()->format('Y-m-d');
        $monthlySalesCollection = Sale::whereBetween('sale_date', [$monthStart, $monthEnd])->get();
        $monthlySale = $monthlySalesCollection->sum('price');
        $monthlyCost = $monthlySalesCollection->sum(fn($s) => $s->total_cost ?? 0);
        $monthlyProfit = $monthlySale - (float) $monthlyCost;

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
            'todayProfit',
            'monthlySale',
            'monthlyProfit',
            'recentSales',
            'lowStock'
        ));
    }
}
