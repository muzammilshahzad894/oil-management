<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\Brand;
use App\Models\Payment;
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
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $customers = Customer::orderBy('name')->get();
        $brands = Brand::all();
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
            'initial_payment_date' => 'nullable|date',
            'initial_payment_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $brand = Brand::find($request->brand_id);
            if (!$brand) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Brand not found.');
            }
            if (($brand->quantity ?? 0) < $request->quantity) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Insufficient stock. Available: ' . ($brand->quantity ?? 0));
            }

            $costAtSale = null;
            if ($brand->cost_price !== null) {
                $costAtSale = (float) $brand->cost_price;
            }

            $saleData = $request->only(['customer_id', 'brand_id', 'quantity', 'price', 'sale_date', 'notes']);
            $saleData['cost_at_sale'] = $costAtSale;
            $saleData['is_paid'] = false;

            $sale = Sale::create($saleData);
            $brand->removeStock($request->quantity);

            $initialAmount = (float) ($request->input('initial_payment') ?? 0);
            if ($initialAmount > 0) {
                Payment::create([
                    'sale_id' => $sale->id,
                    'amount' => $initialAmount,
                    'payment_date' => $request->input('initial_payment_date') ?: $request->sale_date,
                    'method' => $request->input('initial_payment_method') ?: Payment::METHOD_CASH,
                    'reference' => null,
                    'notes' => 'Initial payment',
                ]);
                $sale->refreshIsPaid();
            }

            DB::commit();

            if ($request->input('action') === 'save_and_print') {
                return redirect()->route('admin.sales.receipt', ['id' => $sale->id, 'autoprint' => 1])
                    ->with('success', 'Sale recorded successfully. You can add more payments from the sale details page.');
            }

            return redirect()->route('admin.sales.show', $sale->id)
                ->with('success', 'Sale created successfully. Add payments from this page or later.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error recording sale: ' . $e->getMessage());
        }
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
        $brands = Brand::all();
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
            if ($oldBrandId != $request->brand_id || $oldQuantity != $request->quantity) {
                $oldBrand = Brand::find($oldBrandId);
                if ($oldBrand) {
                    $oldBrand->addStock($oldQuantity);
                }

                $newBrand = Brand::find($request->brand_id);
                if (!$newBrand) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Brand not found.');
                }

                $availableStock = $newBrand->quantity + ($oldBrandId == $request->brand_id ? $oldQuantity : 0);
                if ($availableStock < $request->quantity) {
                    if ($oldBrand && $oldBrandId != $request->brand_id) {
                        $oldBrand->removeStock($oldQuantity);
                    }
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Insufficient stock. Available: ' . $availableStock);
                }

                if ($oldBrandId == $request->brand_id) {
                    $newBrand->quantity = $availableStock - $request->quantity;
                    $newBrand->save();
                } else {
                    $newBrand->removeStock($request->quantity);
                }
            }

            $updateData = $request->only(['customer_id', 'brand_id', 'quantity', 'price', 'sale_date', 'notes']);
            $newBrand = Brand::find($request->brand_id);
            if ($newBrand && $newBrand->cost_price !== null) {
                $updateData['cost_at_sale'] = (float) $newBrand->cost_price;
            }
            $sale->update($updateData);

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
     */
    public function storePayment(Request $request, string $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'method' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $sale = Sale::findOrFail($id);
        Payment::create([
            'sale_id' => $sale->id,
            'amount' => $request->amount,
            'payment_date' => $request->payment_date,
            'method' => $request->input('method') ?: Payment::METHOD_CASH,
            'notes' => $request->notes,
        ]);
        $sale->refreshIsPaid();

        return redirect()->route('admin.sales.show', $sale->id)
            ->with('success', 'Payment added successfully.');
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
            // Restore brand stock
            $brand = Brand::find($sale->brand_id);
            if ($brand) {
                $brand->addStock($sale->quantity);
            }
            
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
