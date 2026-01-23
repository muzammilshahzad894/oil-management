<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\Brand;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Sale::with(['customer', 'brand']);
        
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
    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $brands = Brand::with('inventory')->get();
        return view('admin.sales.create', compact('customers', 'brands'));
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
            // Check if inventory exists and has enough stock
            $inventory = Inventory::where('brand_id', $request->brand_id)->first();
            
            if (!$inventory) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Inventory not found for this brand. Please add inventory first.');
            }
            
            if ($inventory->quantity < $request->quantity) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Insufficient stock. Available: ' . $inventory->quantity);
            }
            
            // Create sale
            $sale = Sale::create($request->all());
            
            // Decrease inventory
            $inventory->removeStock($request->quantity);
            
            DB::commit();
            
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
        $sale = Sale::with(['customer', 'brand'])->findOrFail($id);
        return view('admin.sales.show', compact('sale'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $sale = Sale::findOrFail($id);
        $brands = Brand::with('inventory')->get();
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
            // If brand or quantity changed, adjust inventory
            if ($oldBrandId != $request->brand_id || $oldQuantity != $request->quantity) {
                // Restore old inventory
                $oldInventory = Inventory::where('brand_id', $oldBrandId)->first();
                if ($oldInventory) {
                    $oldInventory->addStock($oldQuantity);
                }
                
                // Check new inventory
                $newInventory = Inventory::where('brand_id', $request->brand_id)->first();
                if (!$newInventory) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Inventory not found for this brand.');
                }
                
                $availableStock = $newInventory->quantity + ($oldBrandId == $request->brand_id ? $oldQuantity : 0);
                if ($availableStock < $request->quantity) {
                    // Restore old inventory if we're reverting
                    if ($oldInventory && $oldBrandId != $request->brand_id) {
                        $oldInventory->removeStock($oldQuantity);
                    }
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Insufficient stock. Available: ' . $availableStock);
                }
                
                // Update new inventory
                if ($oldBrandId == $request->brand_id) {
                    $newInventory->quantity = $availableStock - $request->quantity;
                    $newInventory->save();
                } else {
                    $newInventory->removeStock($request->quantity);
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
            // Restore inventory
            $inventory = Inventory::where('brand_id', $sale->brand_id)->first();
            if ($inventory) {
                $inventory->addStock($sale->quantity);
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
