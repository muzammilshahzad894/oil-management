<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

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

        $totalInvoiced = $sales->sum('price'); // Selling price (total invoiced)
        $totalCost = $sales->sum(fn($s) => $s->total_cost ?? 0); // Purchase price (total cost)
        $profitOnSales = $totalInvoiced - (float) $totalCost; // Profit = Selling - Purchase (not based on actual received)
        $totalReceived = (float) Payment::whereIn('sale_id', $sales->pluck('id')->toArray())->sum('amount');
        $pendingAmount = $totalInvoiced - $totalReceived; // Pending from customers

        return view('admin.reports.profit-loss', compact(
            'sales', 'startDate', 'endDate',
            'totalInvoiced', 'totalReceived', 'totalCost', 'profitOnSales', 'pendingAmount'
        ));
    }
    
    /**
     * Export customer report as Excel (.xlsx) with styled header and auto-sized columns.
     */
    public function exportExcel(Request $request)
    {
        $sales = $this->getCustomerReportSales($request);
        if ($sales === null) {
            return redirect()->route('admin.reports.customer')
                ->with('error', 'Please select a customer to export report.');
        }
        [$sales, $customer, $startDate, $endDate] = $sales;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Customer Report');

        $row = 1;
        $sheet->setCellValue('A' . $row, 'Customer Report - ' . $customer->name);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $row++;
        $sheet->setCellValue('A' . $row, 'Period: ' . $startDate . ' to ' . $endDate);
        $row++;
        $sheet->setCellValue('A' . $row, 'Generated on: ' . date('Y-m-d H:i:s'));
        $row += 2;

        $headerRow = $row;
        $sheet->setCellValue('A' . $row, 'Date');
        $sheet->setCellValue('B' . $row, 'Customer');
        $sheet->setCellValue('C' . $row, 'Brand');
        $sheet->setCellValue('D' . $row, 'Quantity');
        $sheet->setCellValue('E' . $row, 'Total Amount');
        $sheet->setCellValue('F' . $row, 'Remaining Amount');
        $sheet->setCellValue('G' . $row, 'Status');
        $sheet->getStyle('A' . $headerRow . ':G' . $headerRow)->getFont()->setBold(true)->setColor(new Color('FFFFFFFF'));
        $sheet->getStyle('A' . $headerRow . ':G' . $headerRow)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF2F5496');
        $sheet->getStyle('A' . $headerRow . ':G' . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $row++;

        foreach ($sales as $sale) {
            $balanceDue = max(0, (float) $sale->price - $sale->total_paid);
            $status = $sale->total_paid >= (float) $sale->price ? 'Paid' : ($sale->total_paid > 0 ? 'Partial' : 'Unpaid');
            $sheet->setCellValue('A' . $row, $sale->sale_date->format('Y-m-d'));
            $sheet->setCellValue('B' . $row, $sale->customer->name);
            $sheet->setCellValue('C' . $row, $sale->brand->name);
            $sheet->setCellValue('D' . $row, $sale->quantity);
            $sheet->setCellValue('E' . $row, $sale->price);
            $sheet->setCellValue('F' . $row, $balanceDue);
            $sheet->setCellValue('G' . $row, $status);
            $row++;
        }

        $row += 2;
        $totalPaid = $sales->filter(fn($s) => $s->total_paid >= (float) $s->price)->sum('price');
        $totalUnpaid = $sales->filter(fn($s) => $s->total_paid < (float) $s->price)->sum('price');
        $sheet->setCellValue('A' . $row, 'Total Paid');
        $sheet->setCellValue('B' . $row, $totalPaid);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;
        $sheet->setCellValue('A' . $row, 'Total Unpaid');
        $sheet->setCellValue('B' . $row, $totalUnpaid);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);

        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'customer_report_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $customer->name) . '_' . date('Y-m-d') . '.xlsx';
        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Export customer report as PDF.
     */
    public function exportPdf(Request $request)
    {
        $result = $this->getCustomerReportSales($request);
        if ($result === null) {
            return redirect()->route('admin.reports.customer')
                ->with('error', 'Please select a customer to export report.');
        }
        [$sales, $customer, $startDate, $endDate] = $result;

        $rows = [];
        foreach ($sales as $sale) {
            $balanceDue = max(0, (float) $sale->price - $sale->total_paid);
            $status = $sale->total_paid >= (float) $sale->price ? 'Paid' : ($sale->total_paid > 0 ? 'Partial' : 'Unpaid');
            $rows[] = [
                'date' => $sale->sale_date->format('M d, Y'),
                'customer' => $sale->customer->name,
                'brand' => $sale->brand->name,
                'quantity' => $sale->quantity,
                'total_amount' => number_format($sale->price, 0),
                'remaining' => number_format($balanceDue, 0),
                'status' => $status,
            ];
        }
        $totalPaid = $sales->filter(fn($s) => $s->total_paid >= (float) $s->price)->sum('price');
        $totalUnpaid = $sales->filter(fn($s) => $s->total_paid < (float) $s->price)->sum('price');

        $pdf = Pdf::loadView('admin.reports.customer-pdf', [
            'customer' => $customer,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'rows' => $rows,
            'totalPaid' => $totalPaid,
            'totalUnpaid' => $totalUnpaid,
        ]);
        $filename = 'customer_report_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $customer->name) . '_' . date('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * @return array{0: \Illuminate\Support\Collection, 1: \App\Models\Customer, 2: string, 3: string}|null
     */
    private function getCustomerReportSales(Request $request): ?array
    {
        if (!$request->filled('customer_id')) {
            return null;
        }
        $customer = Customer::find($request->customer_id);
        if (!$customer) {
            return null;
        }
        $baseQuery = Sale::with(['customer', 'brand'])
            ->where('customer_id', $request->customer_id);
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $baseQuery->whereBetween('sale_date', [$startDate, $endDate]);
        if ($request->has('is_paid') && $request->is_paid !== '') {
            $baseQuery->where('is_paid', $request->is_paid);
        }
        $sales = $baseQuery->orderBy('sale_date', 'desc')->get();
        return [$sales, $customer, $startDate, $endDate];
    }
}
