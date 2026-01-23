<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\Inventory;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalCustomers = Customer::count();
        $totalBrands = Brand::count();
        $totalSales = Sale::count();
        $totalInventory = Inventory::sum('quantity');
        
        $recentSales = Sale::with(['customer', 'brand'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        $lowStock = Inventory::with('brand')
            ->where('quantity', '<', 10)
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
