<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalCustomers = Customer::count();
        $totalBrands = Brand::count();
        $totalSales = Sale::count();
        $totalInventory = Brand::sum('quantity');
        
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
            'totalCustomers',
            'totalBrands',
            'totalSales',
            'totalInventory',
            'recentSales',
            'lowStock'
        ));
    }
}
