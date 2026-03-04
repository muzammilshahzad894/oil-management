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
        return view('admin.ledger.customers.index', compact('customers'));
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
        $transactions = $customer->transactions()->orderBy('transaction_date', 'desc')->orderBy('id', 'desc')->paginate(15, ['*'], 'tx_page');
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
            'transaction_date' => 'required|date',
        ]);
        $tx = LedgerTransaction::create([
            'ledger_customer_id' => $customer->id,
            'type' => $request->type,
            'amount' => $request->amount,
            'description' => $request->description,
            'transaction_date' => $request->transaction_date,
        ]);
        if ($request->wantsJson() || $request->ajax()) {
            $customer->load('transactions');
            return response()->json([
                'success' => true,
                'message' => ($request->type === 'received' ? 'You got' : 'You gave') . ' ' . format_amount($request->amount) . ' recorded.',
                'transaction' => ['id' => $tx->id, 'type' => $tx->type, 'amount' => (float) $tx->amount, 'description' => $tx->description, 'transaction_date' => $tx->transaction_date->format('Y-m-d')],
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
            'transaction_date' => 'required|date',
        ]);
        $transaction->update($request->only(['amount', 'description', 'transaction_date']));
        $customer->load('transactions');
        return response()->json([
            'success' => true,
            'message' => 'Entry updated.',
            'transaction' => ['id' => $transaction->id, 'type' => $transaction->type, 'amount' => (float) $transaction->amount, 'description' => $transaction->description, 'transaction_date' => $transaction->transaction_date->format('Y-m-d')],
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
}
