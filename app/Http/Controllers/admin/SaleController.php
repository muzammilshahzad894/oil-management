<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\Brand;
use App\Models\Payment;
use App\Models\CustomerExtraPayment;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Sale::with([
            'customer' => function($q) {
                $q->withTrashed();
            },
            'brand' => function($q) {
                $q->withTrashed();
            }
        ]);
        
        // Date filter - default to start of month to current date
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        
        $query->whereBetween('sale_date', [$startDate, $endDate]);
        
        $sales = $query->orderBy('sale_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('admin.sales.index', compact('sales', 'startDate', 'endDate'));
    }
    
    public function searchCustomers(Request $request)
    {
        $search = $request->input('search', '');

        $customers = Customer::where('name', 'like', '%' . $search . '%')
            ->orWhere('phone', 'like', '%' . $search . '%')
            ->orWhere('email', 'like', '%' . $search . '%')
            ->limit(10)
            ->get(['id', 'name', 'phone', 'email']);

        return response()->json($customers);
    }

    /**
     * Suggested sale amount for brand + quantity (FIFO from inventory batches' sale_price).
     */
    public function suggestedPrice(Request $request)
    {
        $brandId = $request->input('brand_id');
        $quantity = (int) $request->input('quantity', 0);
        if (!$brandId || $quantity < 1) {
            return response()->json(['suggested_price' => null]);
        }
        $brand = Brand::find($brandId);
        if (!$brand) {
            return response()->json(['suggested_price' => null]);
        }
        $price = InventoryService::suggestedSalePrice($brand, $quantity);
        return response()->json(['suggested_price' => $price]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $customers = Customer::orderBy('name')->get();
        $brands = Brand::withSum('inventoryBatches', 'quantity_remaining')->get();
        $selectedCustomerId = $request->input('customer_id');
        return view('admin.sales.create', compact('customers', 'brands', 'selectedCustomerId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'brand_id' => 'required|exists:brands,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'sale_date' => 'required|date',
            'initial_payment' => 'nullable|numeric|min:0',
            'initial_extra_paid_amount' => 'nullable|numeric|min:0',
            'initial_payment_date' => 'nullable|date',
            'initial_payment_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $price = (float) $request->price;
        $initialExtraPaid = (float) ($request->input('initial_extra_paid_amount') ?? 0);
        if ($initialExtraPaid > 0) {
            $extraBalance = CustomerExtraPayment::balanceForCustomer($request->customer_id);
            if ($initialExtraPaid > $extraBalance) {
                return $this->backWithBrand($request)
                    ->with('error', 'Extra paid amount exceeds customer balance (' . format_amount($extraBalance) . ').');
            }
            if ($initialExtraPaid > $price) {
                return $this->backWithBrand($request)
                    ->with('error', 'Extra paid amount cannot exceed sale amount.');
            }
        }

        DB::beginTransaction();
        try {
            $brand = Brand::find($request->brand_id);
            if (!$brand) {
                return $this->backWithBrand($request)->with('error', 'Brand not found.');
            }
            $availableStock = InventoryService::availableStock($brand);
            if ($availableStock < $request->quantity) {
                return $this->backWithBrand($request)
                    ->with('error', 'Insufficient stock. Available: ' . $availableStock);
            }

            $saleData = $request->only(['customer_id', 'brand_id', 'quantity', 'price', 'sale_date', 'notes']);
            $saleData['is_paid'] = false;
            $sale = Sale::create($saleData);
            InventoryService::allocateForSale($sale, $brand, (int) $request->quantity);

            $initialAmount = (float) ($request->input('initial_payment') ?? 0);
            $amountFromExtra = $initialExtraPaid;
            $amountFromCash = min($initialAmount, max(0, $price - $amountFromExtra));
            $excessToExtraPaid = $initialAmount - $amountFromCash;

            if ($amountFromCash > 0) {
                Payment::create([
                    'sale_id' => $sale->id,
                    'amount' => $amountFromCash,
                    'payment_date' => $request->input('initial_payment_date') ?: $request->sale_date,
                    'method' => $request->input('initial_payment_method') ?: Payment::METHOD_CASH,
                    'reference' => null,
                    'notes' => 'Initial payment',
                ]);
            }

            if ($amountFromExtra > 0) {
                $payment = Payment::create([
                    'sale_id' => $sale->id,
                    'amount' => $amountFromExtra,
                    'payment_date' => $request->input('initial_payment_date') ?: $request->sale_date,
                    'method' => Payment::METHOD_EXTRA_PAID,
                    'reference' => null,
                    'notes' => 'From extra paid',
                ]);
                CustomerExtraPayment::create([
                    'customer_id' => $sale->customer_id,
                    'amount' => -$amountFromExtra,
                    'sale_id' => $sale->id,
                    'payment_id' => $payment->id,
                    'note' => 'Used for Sale #' . $sale->id,
                ]);
            }

            if ($excessToExtraPaid > 0) {
                CustomerExtraPayment::create([
                    'customer_id' => $sale->customer_id,
                    'amount' => $excessToExtraPaid,
                    'sale_id' => $sale->id,
                    'payment_id' => null,
                    'note' => 'Overpayment on Sale #' . $sale->id,
                ]);
            }

            $sale->refreshIsPaid();
            DB::commit();

            if ($request->input('action') === 'save_and_print') {
                return redirect()->route('admin.sales.receipt', ['id' => $sale->id, 'autoprint' => 1])
                    ->with('success', 'Sale recorded successfully. You can add more payments from the sale details page.');
            }

            return redirect()->route('admin.sales.show', $sale->id)
                ->with('success', 'Sale created successfully. Add payments from this page or later.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->backWithBrand($request)->with('error', 'Error recording sale: ' . $e->getMessage());
        }
    }

    /** Redirect back with input and old brand/customer names (for validation error restore). */
    private function backWithBrand(Request $request)
    {
        $brandName = $request->brand_id ? (Brand::find($request->brand_id)?->name) : null;
        $customerName = $request->customer_id ? (Customer::find($request->customer_id)?->name) : null;
        return redirect()->back()->withInput()
            ->with('old_brand_name', $brandName)
            ->with('old_customer_name', $customerName);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $sale = Sale::with([
            'customer' => fn($q) => $q->withTrashed(),
            'brand' => fn($q) => $q->withTrashed(),
            'payments',
        ])->findOrFail($id);
        return view('admin.sales.show', compact('sale'));
    }
    
    /**
     * Display receipt for printing
     */
    public function receipt(string $id)
    {
        $sale = Sale::with([
            'customer' => fn($q) => $q->withTrashed(),
            'brand' => fn($q) => $q->withTrashed(),
            'payments',
        ])->findOrFail($id);
        return view('admin.sales.receipt', compact('sale'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $sale = Sale::findOrFail($id);
        $brands = Brand::withSum('inventoryBatches', 'quantity_remaining')->get();
        return view('admin.sales.edit', compact('sale', 'brands'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'brand_id' => 'required|exists:brands,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'sale_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $sale = Sale::findOrFail($id);
        $oldQuantity = $sale->quantity;
        $oldBrandId = $sale->brand_id;

        DB::beginTransaction();
        try {
            $newBrand = Brand::find($request->brand_id);
            if (!$newBrand) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Brand not found.');
            }

            if ($oldBrandId != $request->brand_id || $oldQuantity != (int) $request->quantity) {
                InventoryService::returnAllocation($sale);
                $availableStock = InventoryService::availableStock($newBrand);
                $needed = (int) $request->quantity;
                if ($availableStock < $needed) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Insufficient stock. Available: ' . $availableStock);
                }
                $sale->update($request->only(['customer_id', 'brand_id', 'quantity', 'price', 'sale_date', 'notes']));
                InventoryService::allocateForSale($sale, $newBrand, $needed);
            } else {
                $sale->update($request->only(['customer_id', 'brand_id', 'quantity', 'price', 'sale_date', 'notes']));
            }

            DB::commit();

            return redirect()->route('admin.sales.show', $sale->id)
                ->with('success', 'Sale updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating sale: ' . $e->getMessage());
        }
    }

    /**
     * Store a payment for a sale (from sale details page).
     * If sale is fully paid, the full amount goes to customer's extra paid (wallet).
     * If amount exceeds balance due, only balance_due is applied to the sale; the rest goes to wallet.
     */
    public function storePayment(Request $request, string $id)
    {
        $sale = Sale::findOrFail($id);
        $balanceDue = max(0, (float) $sale->price - $sale->total_paid);

        $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => 'required|date',
            'method' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $amount = (float) $request->amount;

        if ($balanceDue <= 0) {
            // Sale already fully paid: add full amount to customer's wallet
            CustomerExtraPayment::create([
                'customer_id' => $sale->customer_id,
                'amount' => $amount,
                'sale_id' => $sale->id,
                'payment_id' => null,
                'note' => 'Added from Sale #' . $sale->id . ' (sale already fully paid)',
            ]);
            return redirect()->route('admin.sales.show', $sale->id)
                ->with('success', 'Sale is already fully paid. ' . format_amount($amount) . ' has been added to the customer\'s wallet.');
        }

        $amountAppliedToSale = min($amount, $balanceDue);
        $excessToWallet = $amount - $amountAppliedToSale;

        Payment::create([
            'sale_id' => $sale->id,
            'amount' => $amountAppliedToSale,
            'payment_date' => $request->payment_date,
            'method' => $request->input('method') ?: Payment::METHOD_CASH,
            'notes' => $request->notes,
        ]);
        $sale->refreshIsPaid();

        if ($excessToWallet > 0) {
            CustomerExtraPayment::create([
                'customer_id' => $sale->customer_id,
                'amount' => $excessToWallet,
                'sale_id' => $sale->id,
                'payment_id' => null,
                'note' => 'Overpayment on Sale #' . $sale->id,
            ]);
        }

        $msg = $excessToWallet > 0
            ? 'Payment of ' . format_amount($amountAppliedToSale) . ' added. ' . format_amount($excessToWallet) . ' added to customer wallet.'
            : 'Payment added successfully.';

        return redirect()->route('admin.sales.show', $sale->id)
            ->with('success', $msg);
    }

    /**
     * Remove a payment (from sale details page).
     */
    public function destroyPayment(string $saleId, string $paymentId)
    {
        $payment = Payment::where('sale_id', $saleId)->findOrFail($paymentId);
        $payment->delete();
        Sale::findOrFail($saleId)->refreshIsPaid();

        return redirect()->route('admin.sales.show', $saleId)
            ->with('success', 'Payment removed.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $sale = Sale::findOrFail($id);

        DB::beginTransaction();
        try {
            InventoryService::returnAllocation($sale);
            $sale->delete();

            DB::commit();

            return redirect()->route('admin.sales.index')
                ->with('success', 'Sale deleted successfully and inventory restored.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error deleting sale: ' . $e->getMessage());
        }
    }
}
