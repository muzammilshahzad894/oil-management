<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Payment;
use App\Models\Sale;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalSales = Sale::count();
        $totalInventory = Brand::sum('quantity');
        $totalReceived = (float) Payment::sum('amount');
        $totalCost = (float) Sale::selectRaw('COALESCE(SUM(cost_at_sale * quantity), 0) as total')->value('total');
        $totalProfit = $totalReceived - $totalCost;
        
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
        
        $lowStock = Brand::where('quantity', '<', 10)
            ->orderBy('quantity', 'asc')
            ->get();
        
        return view('admin.dashboard', compact(
            'totalSales',
            'totalInventory',
            'totalProfit',
            'recentSales',
            'lowStock'
        ));
    }
}
