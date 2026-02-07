<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\Brand;
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
            ->withTrashed()
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
            'is_paid' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Check brand has enough stock
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
            
            // Create sale
            $sale = Sale::create($request->all());
            
            // Decrease brand stock
            $brand->removeStock($request->quantity);
            
            DB::commit();
            
            // Check if save and print was clicked
            if ($request->input('action') === 'save_and_print') {
                return redirect()->route('admin.sales.receipt', ['id' => $sale->id, 'autoprint' => 1])
                    ->with('success', 'Sale recorded successfully and inventory updated.');
            }
            
            return redirect()->route('admin.sales.index')
                ->with('success', 'Sale recorded successfully and inventory updated.');
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
            'customer' => function($q) {
                $q->withTrashed();
            },
            'brand' => function($q) {
                $q->withTrashed();
            }
        ])->findOrFail($id);
        return view('admin.sales.show', compact('sale'));
    }
    
    /**
     * Display receipt for printing
     */
    public function receipt(string $id)
    {
        $sale = Sale::with([
            'customer' => function($q) {
                $q->withTrashed();
            },
            'brand' => function($q) {
                $q->withTrashed();
            }
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
            'is_paid' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        $sale = Sale::findOrFail($id);
        $oldQuantity = $sale->quantity;
        $oldBrandId = $sale->brand_id;
        
        DB::beginTransaction();
        try {
            // If brand or quantity changed, adjust stock
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
            
            $sale->update($request->all());
            
            DB::commit();
            
            return redirect()->route('admin.sales.index')
                ->with('success', 'Sale updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating sale: ' . $e->getMessage());
        }
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
