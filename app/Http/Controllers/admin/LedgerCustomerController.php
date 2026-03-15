<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\LedgerCustomer;
use App\Models\LedgerTransaction;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class LedgerCustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = LedgerCustomer::withSum(['transactions as total_received_sum' => function ($q) {
            $q->where('type', 'received');
        }], 'amount')
            ->withSum(['transactions as total_gave_sum' => function ($q) {
                $q->where('type', 'gave');
            }], 'amount')
            ->withMax('transactions as last_transaction_at', 'transaction_date')
            ->orderBy('name');
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', '%' . $s . '%')
                    ->orWhere('phone', 'like', '%' . $s . '%')
                    ->orWhere('address', 'like', '%' . $s . '%');
            });
        }
        $customers = $query->paginate(15)->withQueryString();

        $allForTotals = LedgerCustomer::withSum(['transactions as total_received_sum' => fn($q) => $q->where('type', 'received')], 'amount')
            ->withSum(['transactions as total_gave_sum' => fn($q) => $q->where('type', 'gave')], 'amount')
            ->get();
        $overallYouWillGive = $allForTotals->sum(fn($c) => max(0, (float)($c->total_received_sum ?? 0) - (float)($c->total_gave_sum ?? 0)));
        $overallYouWillGet = $allForTotals->sum(fn($c) => max(0, (float)($c->total_gave_sum ?? 0) - (float)($c->total_received_sum ?? 0)));

        $totalCustomers = $customers->total();
        return view('admin.ledger.customers.index', compact('customers', 'overallYouWillGive', 'overallYouWillGet', 'totalCustomers'));
    }

    public function create()
    {
        return view('admin.ledger.customers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
        ]);
        LedgerCustomer::create($request->only(['name', 'phone', 'address']));
        return redirect()->route('admin.ledger.customers.index')
            ->with('success', 'Ledger customer added successfully.');
    }

    public function show(Request $request, LedgerCustomer $customer)
    {
        $txQuery = $customer->transactions()->orderBy('transaction_date', 'desc')->orderBy('id', 'desc');
        if ($request->filled('history_search')) {
            $term = $request->history_search;
            $txQuery->where(function ($q) use ($term) {
                $q->where('description', 'like', '%' . $term . '%')
                    ->orWhereRaw('CONVERT(amount, CHAR) LIKE ?', ['%' . $term . '%']);
            });
        }
        $transactions = $txQuery->paginate(15, ['*'], 'tx_page')->withQueryString();
        $runningBalance = (float) $customer->balance;
        $withBalance = !$request->filled('history_search');
        $transactionsWithBalance = $transactions->getCollection()->map(function ($tx) use (&$runningBalance, $withBalance) {
            $balanceAfter = $withBalance ? $runningBalance : null;
            if ($withBalance) {
                $runningBalance -= $tx->type === 'received' ? (float) $tx->amount : -(float) $tx->amount;
            }
            return (object) ['tx' => $tx, 'balance_after' => $balanceAfter];
        });
        $transactions->setCollection($transactionsWithBalance);

        if ($request->ajax()) {
            return view('admin.ledger.customers.partials.history', compact('customer', 'transactions'));
        }
        return view('admin.ledger.customers.show', compact('customer', 'transactions'));
    }

    public function edit(LedgerCustomer $customer)
    {
        return view('admin.ledger.customers.edit', compact('customer'));
    }

    public function update(Request $request, LedgerCustomer $customer)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
        ]);
        $customer->update($request->only(['name', 'phone', 'address']));
        return redirect()->route('admin.ledger.customers.show', $customer)
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(LedgerCustomer $customer)
    {
        $customer->delete();
        return redirect()->route('admin.ledger.customers.index')
            ->with('success', 'Customer removed from ledger.');
    }

    public function storeTransaction(Request $request, LedgerCustomer $customer)
    {
        $request->validate([
            'type' => 'required|in:received,gave',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
            'transaction_date' => 'nullable|date',
        ]);
        $txDate = $request->filled('transaction_date')
            ? $request->transaction_date
            : now()->format('Y-m-d H:i:s');
        $tx = LedgerTransaction::create([
            'ledger_customer_id' => $customer->id,
            'type' => $request->type,
            'amount' => $request->amount,
            'description' => $request->description,
            'transaction_date' => $txDate,
        ]);
        if ($request->wantsJson() || $request->ajax()) {
            $customer->load('transactions');
            return response()->json([
                'success' => true,
                'message' => ($request->type === 'received' ? 'You got' : 'You gave') . ' ' . format_amount($request->amount) . ' recorded.',
                'transaction' => ['id' => $tx->id, 'type' => $tx->type, 'amount' => (float) $tx->amount, 'description' => $tx->description, 'transaction_date' => $tx->transaction_date->format('Y-m-d H:i')],
                'totals' => ['total_received' => $customer->total_received, 'total_gave' => $customer->total_gave, 'balance' => $customer->balance],
            ]);
        }
        return redirect()->route('admin.ledger.customers.show', $customer)
            ->with('success', ($request->type === 'received' ? 'You got' : 'You gave') . ' ' . format_amount($request->amount) . ' recorded.');
    }

    public function updateTransaction(Request $request, LedgerCustomer $customer, LedgerTransaction $transaction)
    {
        if ($transaction->ledger_customer_id != $customer->id) {
            abort(404);
        }
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
            'transaction_date' => 'nullable|date',
        ]);
        $updateData = $request->only(['amount', 'description']);
        if ($request->filled('transaction_date')) {
            $updateData['transaction_date'] = $request->transaction_date;
        }
        $transaction->update($updateData);
        $customer->load('transactions');
        return response()->json([
            'success' => true,
            'message' => 'Entry updated.',
            'transaction' => ['id' => $transaction->id, 'type' => $transaction->type, 'amount' => (float) $transaction->amount, 'description' => $transaction->description, 'transaction_date' => $transaction->transaction_date->format('Y-m-d H:i')],
            'totals' => ['total_received' => $customer->total_received, 'total_gave' => $customer->total_gave, 'balance' => $customer->balance],
        ]);
    }

    public function destroyTransaction(LedgerCustomer $customer, LedgerTransaction $transaction)
    {
        if ($transaction->ledger_customer_id != $customer->id) {
            abort(404);
        }
        $transaction->delete();
        $customer->load('transactions');
        return response()->json([
            'success' => true,
            'message' => 'Entry deleted.',
            'totals' => ['total_received' => $customer->total_received, 'total_gave' => $customer->total_gave, 'balance' => $customer->balance],
        ]);
    }

    /**
     * Export customer ledger (all transactions) as Excel (.xlsx) with auto-sized columns.
     */
    public function export(LedgerCustomer $customer)
    {
        $transactions = $customer->transactions()->orderBy('transaction_date', 'desc')->orderBy('id', 'desc')->get();
        $runningBalance = (float) $customer->balance;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Ledger');

        $row = 1;
        $sheet->setCellValue('A' . $row, 'Ledger - ' . $customer->name);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $row++;
        $sheet->setCellValue('A' . $row, 'Exported on: ' . date('Y-m-d H:i:s'));
        $row++;
        $sheet->setCellValue('A' . $row, 'Current balance: Rs ' . number_format(abs($customer->balance), 0) . ' (' . ($customer->balance > 0 ? 'You will give' : ($customer->balance < 0 ? 'You will get' : 'Settled')) . ')');
        $row += 2;
        $headerRow = $row;
        $sheet->setCellValue('A' . $row, 'Entry');
        $sheet->setCellValue('B' . $row, 'Description');
        $sheet->setCellValue('C' . $row, 'You gave');
        $sheet->setCellValue('D' . $row, 'You get');
        $sheet->setCellValue('E' . $row, 'Balance after');
        // Highlight column header row: bold white text on dark blue background
        $sheet->getStyle('A' . $headerRow . ':E' . $headerRow)->getFont()->setBold(true)->setColor(new Color('FFFFFFFF'));
        $sheet->getStyle('A' . $headerRow . ':E' . $headerRow)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF2F5496');
        $sheet->getStyle('A' . $headerRow . ':E' . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $row++;

        foreach ($transactions as $tx) {
            $balanceAfter = $runningBalance;
            $runningBalance -= $tx->type === 'received' ? (float) $tx->amount : -(float) $tx->amount;
            $dateStr = $tx->transaction_date->format('D, d M Y · H:i');
            $desc = $tx->description ?? '';
            $youGave = $tx->type === 'gave' ? number_format($tx->amount, 0) : '';
            $youGet = $tx->type === 'received' ? number_format($tx->amount, 0) : '';
            $balStr = 'Rs ' . number_format(abs($balanceAfter), 0);
            $sheet->setCellValue('A' . $row, $dateStr);
            $sheet->setCellValue('B' . $row, $desc);
            $sheet->setCellValue('C' . $row, $youGave);
            $sheet->setCellValue('D' . $row, $youGet);
            $sheet->setCellValue('E' . $row, $balStr);
            $row++;
        }

        $row += 2;
        $sheet->setCellValue('A' . $row, 'Total you received (You get)');
        $sheet->setCellValue('B' . $row, 'Rs ' . number_format($customer->total_received, 0));
        $row++;
        $sheet->setCellValue('A' . $row, 'Total you gave (You gave)');
        $sheet->setCellValue('B' . $row, 'Rs ' . number_format($customer->total_gave, 0));

        // Auto-size columns A to E so Excel opens with readable column widths
        foreach (['A', 'B', 'C', 'D', 'E'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'ledger_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $customer->name) . '_' . date('Y-m-d') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Export customer ledger as PDF.
     */
    public function exportPdf(LedgerCustomer $customer)
    {
        $transactions = $customer->transactions()->orderBy('transaction_date', 'desc')->orderBy('id', 'desc')->get();
        $runningBalance = (float) $customer->balance;
        $rows = [];
        foreach ($transactions as $tx) {
            $balanceAfter = $runningBalance;
            $runningBalance -= $tx->type === 'received' ? (float) $tx->amount : -(float) $tx->amount;
            $rows[] = [
                'date' => $tx->transaction_date->format('D, d M Y · H:i'),
                'description' => $tx->description ?? '—',
                'you_gave' => $tx->type === 'gave' ? number_format($tx->amount, 0) : '',
                'you_get' => $tx->type === 'received' ? number_format($tx->amount, 0) : '',
                'balance' => 'Rs ' . number_format(abs($balanceAfter), 0),
            ];
        }
        $balanceLabel = $customer->balance > 0 ? 'You will give' : ($customer->balance < 0 ? 'You will get' : 'Settled');
        $pdf = Pdf::loadView('admin.ledger.customers.pdf', [
            'customer' => $customer,
            'rows' => $rows,
            'balanceLabel' => $balanceLabel,
        ]);
        $filename = 'ledger_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $customer->name) . '_' . date('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }
}
