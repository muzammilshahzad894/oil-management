<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Sale;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function customer(Request $request)
    {
        // Customer filter - required
        $hasCustomer = $request->has('customer_id') && $request->customer_id;
        
        if (!$hasCustomer) {
            // No customer selected - return empty data
            $customers = Customer::orderBy('name')->get();
            $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', now()->format('Y-m-d'));
            
            return view('admin.reports.customer', [
                'allSales' => collect(),
                'paginatedSales' => null,
                'customers' => $customers,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'selectedCustomer' => null,
                'hasCustomer' => false,
                'totalCost' => 0,
                'totalProfit' => 0,
            ]);
        }
        
        // Customer is selected - build query
        $baseQuery = Sale::with([
            'customer' => function($q) {
                $q->withTrashed();
            },
            'brand' => function($q) {
                $q->withTrashed();
            }
        ])->where('customer_id', $request->customer_id);
        
        // Date filter - use provided dates or default to last month to current date for better coverage
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        // Apply date filter
        $baseQuery->whereBetween('sale_date', [$startDate, $endDate]);
        
        // Paid/Unpaid filter
        if ($request->has('is_paid') && isset($request->is_paid)) {
            $baseQuery->where('is_paid', $request->is_paid);
        }
        
        // Get all sales for totals calculation
        $allSales = (clone $baseQuery)->get();
        
        // Paginate for display
        $paginatedSales = (clone $baseQuery)
            ->orderBy('sale_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        $customers = Customer::orderBy('name')->get();
        $selectedCustomer = Customer::find($request->customer_id);
        $totalCost = $allSales->sum(fn($s) => $s->total_cost ?? 0);
        $totalProfit = $allSales->filter(fn($s) => $s->cost_at_sale !== null)->sum(fn($s) => $s->profit);

        return view('admin.reports.customer', compact('allSales', 'paginatedSales', 'customers', 'startDate', 'endDate', 'selectedCustomer', 'hasCustomer', 'totalCost', 'totalProfit'));
    }

    /**
     * Profit & Loss report by date range.
     * Uses actual payments received (from payments table) minus cost for actual profit/loss.
     */
    public function profitLoss(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $sales = Sale::with(['customer', 'brand'])
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->get();

        $saleIds = $sales->pluck('id')->toArray();
        $totalInvoiced = $sales->sum('price');
        $totalReceived = (float) Payment::whereIn('sale_id', $saleIds)->sum('amount');
        $totalCost = $sales->sum(fn($s) => $s->total_cost ?? 0);
        $totalProfit = $totalReceived - (float) $totalCost;

        return view('admin.reports.profit-loss', compact(
            'sales', 'startDate', 'endDate',
            'totalInvoiced', 'totalReceived', 'totalCost', 'totalProfit'
        ));
    }
    
    public function exportExcel(Request $request)
    {
        $baseQuery = Sale::with(['customer', 'brand']);
        
        // Customer filter - required
        if (!$request->has('customer_id') || !$request->customer_id) {
            return redirect()->route('admin.reports.customer')
                ->with('error', 'Please select a customer to export report.');
        }
        
        $baseQuery->where('customer_id', $request->customer_id);
        
        // Date filter
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $baseQuery->whereBetween('sale_date', [$startDate, $endDate]);
        
        // Paid/Unpaid filter
        if ($request->has('is_paid') && $request->is_paid !== '') {
            $baseQuery->where('is_paid', $request->is_paid);
        }
        
        $sales = $baseQuery->orderBy('sale_date', 'desc')->get();
        $customer = Customer::find($request->customer_id);
        
        $filename = 'customer_report_' . ($customer ? str_replace(' ', '_', $customer->name) : 'all') . '_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $sales->load('payments');
        $callback = function() use ($sales, $customer) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['Customer Report - ' . ($customer ? $customer->name : 'All Customers')]);
            fputcsv($file, ['Generated on: ' . date('Y-m-d H:i:s')]);
            fputcsv($file, []);
            fputcsv($file, ['Date', 'Customer', 'Brand', 'Quantity', 'Price', 'Cost', 'Profit', 'Payment Status']);
            $totalPaid = 0;
            $totalUnpaid = 0;
            $qtyPaid = 0;
            $qtyUnpaid = 0;
            $totalCost = 0;
            $totalProfit = 0;
            foreach ($sales as $sale) {
                $cost = $sale->cost_at_sale ?? '';
                $profit = $sale->profit !== null ? $sale->profit : '';
                fputcsv($file, [
                    $sale->sale_date->format('Y-m-d'),
                    $sale->customer->name,
                    $sale->brand->name,
                    $sale->quantity,
                    $sale->price,
                    $cost,
                    $profit,
                    $sale->is_paid ? 'Paid' : 'Unpaid'
                ]);
                if ($sale->is_paid) {
                    $totalPaid += $sale->price;
                    $qtyPaid += $sale->quantity;
                } else {
                    $totalUnpaid += $sale->price;
                    $qtyUnpaid += $sale->quantity;
                }
                if ($sale->cost_at_sale !== null) {
                    $totalCost += $sale->total_cost;
                    $totalProfit += $sale->profit;
                }
            }
            fputcsv($file, []);
            fputcsv($file, ['Total Paid', '', '', $qtyPaid, $totalPaid, '', '', $sales->where('is_paid', true)->count() . ' sales']);
            fputcsv($file, ['Total Unpaid', '', '', $qtyUnpaid, $totalUnpaid, '', '', $sales->where('is_paid', false)->count() . ' sales']);
            if ($totalCost > 0) {
                fputcsv($file, ['Total Cost', '', '', '', $totalCost, '', '', '']);
                fputcsv($file, ['Total Profit', '', '', '', $totalProfit, '', '', '']);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
