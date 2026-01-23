<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
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
                'hasCustomer' => false
            ]);
        }
        
        // Customer is selected - build query
        $baseQuery = Sale::with(['customer', 'brand'])
            ->where('customer_id', $request->customer_id);
        
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
        
        return view('admin.reports.customer', compact('allSales', 'paginatedSales', 'customers', 'startDate', 'endDate', 'selectedCustomer', 'hasCustomer'));
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
        
        $callback = function() use ($sales, $customer) {
            $file = fopen('php://output', 'w');
            
            // BOM for Excel UTF-8 support
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header row
            fputcsv($file, ['Customer Report - ' . ($customer ? $customer->name : 'All Customers')]);
            fputcsv($file, ['Generated on: ' . date('Y-m-d H:i:s')]);
            fputcsv($file, []); // Empty row
            
            // Column headers
            fputcsv($file, ['Date', 'Customer', 'Brand', 'Quantity', 'Price', 'Payment Status']);
            
            // Data rows
            $totalPaid = 0;
            $totalUnpaid = 0;
            $qtyPaid = 0;
            $qtyUnpaid = 0;
            
            foreach ($sales as $sale) {
                fputcsv($file, [
                    $sale->sale_date->format('Y-m-d'),
                    $sale->customer->name,
                    $sale->brand->name,
                    $sale->quantity,
                    $sale->price,
                    $sale->is_paid ? 'Paid' : 'Unpaid'
                ]);
                
                if ($sale->is_paid) {
                    $totalPaid += $sale->price;
                    $qtyPaid += $sale->quantity;
                } else {
                    $totalUnpaid += $sale->price;
                    $qtyUnpaid += $sale->quantity;
                }
            }
            
            // Empty row
            fputcsv($file, []);
            
            // Totals
            fputcsv($file, ['Total Paid', '', '', $qtyPaid, number_format($totalPaid, 2), $sales->where('is_paid', true)->count() . ' sales']);
            fputcsv($file, ['Total Unpaid', '', '', $qtyUnpaid, number_format($totalUnpaid, 2), $sales->where('is_paid', false)->count() . ' sales']);
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
