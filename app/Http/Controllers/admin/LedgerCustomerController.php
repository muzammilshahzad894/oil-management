<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\LedgerCustomer;
use App\Models\LedgerTransaction;
use Illuminate\Http\Request;

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
     * Export customer ledger (all transactions) as CSV.
     */
    public function export(LedgerCustomer $customer)
    {
        $transactions = $customer->transactions()->orderBy('transaction_date', 'desc')->orderBy('id', 'desc')->get();
        $runningBalance = (float) $customer->balance;

        $filename = 'ledger_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $customer->name) . '_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($customer, $transactions, &$runningBalance) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM for Excel
            fputcsv($file, ['Ledger - ' . $customer->name]);
            fputcsv($file, ['Exported on: ' . date('Y-m-d H:i:s')]);
            fputcsv($file, ['Current balance: Rs ' . number_format(abs($customer->balance), 0) . ' (' . ($customer->balance > 0 ? 'You will give' : ($customer->balance < 0 ? 'You will get' : 'Settled')) . ')']);
            fputcsv($file, []);
            fputcsv($file, ['Entry', 'Description', 'You gave', 'You get', 'Balance after']);

            foreach ($transactions as $tx) {
                $balanceAfter = $runningBalance;
                $runningBalance -= $tx->type === 'received' ? (float) $tx->amount : -(float) $tx->amount;
                $dateStr = $tx->transaction_date->format('D, d M Y · H:i');
                $desc = $tx->description ?? '';
                $youGave = $tx->type === 'gave' ? number_format($tx->amount, 0) : '';
                $youGet = $tx->type === 'received' ? number_format($tx->amount, 0) : '';
                $balStr = 'Rs ' . number_format(abs($balanceAfter), 0);
                fputcsv($file, [$dateStr, $desc, $youGave, $youGet, $balStr]);
            }

            fputcsv($file, []);
            fputcsv($file, ['Total you received (You get)', 'Rs ' . number_format($customer->total_received, 0)]);
            fputcsv($file, ['Total you gave (You gave)', 'Rs ' . number_format($customer->total_gave, 0)]);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
