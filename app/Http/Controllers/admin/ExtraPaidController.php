<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerExtraPayment;
use App\Models\Payment;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExtraPaidController extends Controller
{
    /**
     * Get extra paid balance for a customer (JSON).
     */
    public function balance(Customer $customer)
    {
        $balance = CustomerExtraPayment::balanceForCustomer($customer->id);
        return response()->json(['balance' => $balance, 'formatted' => format_amount($balance)]);
    }

    /**
     * Store a deposit (add extra paid for customer).
     */
    public function store(Request $request, Customer $customer)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:255',
        ]);

        CustomerExtraPayment::create([
            'customer_id' => $customer->id,
            'amount' => $request->amount,
            'note' => $request->note,
        ]);

        $balance = CustomerExtraPayment::balanceForCustomer($customer->id);
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Extra paid amount added successfully.',
                'balance' => $balance,
                'formatted' => format_amount($balance),
            ]);
        }
        return redirect()->back()->with('success', 'Extra paid amount added. Balance: ' . format_amount($balance));
    }

    /**
     * Use extra paid as payment for a sale (withdraw from balance, create payment).
     */
    public function useForSalePayment(Request $request, Sale $sale)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $customerId = $sale->customer_id;
        $balance = CustomerExtraPayment::balanceForCustomer($customerId);
        $amount = (float) $request->amount;
        $maxAllowed = min($balance, max(0, (float) $sale->price - $sale->total_paid));

        if ($amount > $balance) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Amount exceeds extra paid balance (' . format_amount($balance) . ').');
        }
        if ($amount > $maxAllowed) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Amount cannot exceed balance due (' . format_amount($maxAllowed) . ') or extra paid balance (' . format_amount($balance) . ').');
        }

        DB::beginTransaction();
        try {
            $payment = Payment::create([
                'sale_id' => $sale->id,
                'amount' => $amount,
                'payment_date' => $request->payment_date,
                'method' => Payment::METHOD_EXTRA_PAID,
                'notes' => $request->notes ?: 'From extra paid balance',
            ]);

            CustomerExtraPayment::create([
                'customer_id' => $customerId,
                'amount' => -$amount,
                'sale_id' => $sale->id,
                'payment_id' => $payment->id,
                'note' => 'Used for Sale #' . $sale->id,
            ]);

            $sale->refreshIsPaid();
            DB::commit();

            return redirect()->route('admin.sales.show', $sale->id)
                ->with('success', 'Payment of ' . format_amount($amount) . ' applied from extra paid balance.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
