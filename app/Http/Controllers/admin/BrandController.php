<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\InventoryBatch;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Brand::query();

        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        $brands = $query->withSum(['sales' => fn($q) => $q->withoutTrashed()], 'quantity')
            ->withSum('inventoryBatches', 'quantity_remaining')
            ->orderBy('name')
            ->paginate(15);
        return view('admin.brands.index', compact('brands'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.brands.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Brand::create($request->only(['name', 'description']));

        return redirect()->route('admin.brands.index')
            ->with('success', 'Brand created successfully. Add stock from the brand page.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $brand = Brand::findOrFail($id);
        $stockBatches = InventoryBatch::where('brand_id', $id)->orderBy('created_at')->paginate(15, ['*'], 'stock_page');
        $archivedBatchesCount = InventoryBatch::where('brand_id', $id)->onlyTrashed()->count();
        $sales = $brand->sales()->with('customer')->orderBy('sale_date', 'desc')->orderBy('created_at', 'desc')->paginate(15, ['*'], 'sales_page');
        $availableStock = InventoryService::availableStock($brand);
        return view('admin.brands.show', compact('brand', 'stockBatches', 'archivedBatchesCount', 'sales', 'availableStock'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $brand = Brand::findOrFail($id);
        return view('admin.brands.edit', compact('brand'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $brand = Brand::findOrFail($id);
        $brand->update($request->only(['name', 'description']));

        return redirect()->route('admin.brands.index')
            ->with('success', 'Brand updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $brand = Brand::findOrFail($id);
        $brand->delete();

        return redirect()->route('admin.brands.index')
            ->with('success', 'Brand deleted successfully.');
    }

    /**
     * Show form to add stock (one or more batches) for a brand.
     */
    public function stockCreate(string $id)
    {
        $brand = Brand::findOrFail($id);
        return view('admin.brands.stock-create', compact('brand'));
    }

    /**
     * Store new stock batch(es) for a brand (FIFO).
     */
    public function stockStore(Request $request, string $id)
    {
        $brand = Brand::findOrFail($id);
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'cost_per_unit' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
        ]);

        InventoryBatch::create([
            'brand_id' => $brand->id,
            'quantity' => $request->quantity,
            'quantity_remaining' => $request->quantity,
            'cost_per_unit' => $request->cost_per_unit,
            'sale_price' => $request->filled('sale_price') ? $request->sale_price : null,
            'received_at' => now(),
        ]);

        return redirect()->route('admin.brands.show', $brand->id)
            ->with('success', 'Stock added successfully. System uses FIFO for cost on sales.');
    }

    /**
     * Edit a stock batch.
     */
    public function stockEdit(string $brandId, string $batchId)
    {
        $brand = Brand::findOrFail($brandId);
        $batch = InventoryBatch::where('brand_id', $brandId)->findOrFail($batchId);
        $batch->loadSum('saleBatchAllocations', 'quantity');
        $allocated = (int) ($batch->sale_batch_allocations_sum_quantity ?? 0);
        return view('admin.brands.stock-edit', compact('brand', 'batch', 'allocated'));
    }

    /**
     * Update a stock batch. Quantity cannot be set below already-allocated (sold) amount.
     */
    public function stockUpdate(Request $request, string $brandId, string $batchId)
    {
        $brand = Brand::findOrFail($brandId);
        $batch = InventoryBatch::where('brand_id', $brandId)->findOrFail($batchId);
        $allocated = (int) $batch->saleBatchAllocations()->sum('quantity');

        $request->validate([
            'quantity' => 'required|integer|min:' . max(1, $allocated),
            'cost_per_unit' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
        ]);

        $newQuantity = (int) $request->quantity;
        if ($newQuantity < $allocated) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Quantity cannot be less than the amount already sold from this batch (' . $allocated . '). Set at least ' . $allocated . ' or more.');
        }

        $batch->quantity = $newQuantity;
        $batch->quantity_remaining = $newQuantity - $allocated;
        $batch->cost_per_unit = $request->cost_per_unit;
        $batch->sale_price = $request->filled('sale_price') ? $request->sale_price : null;
        $batch->save();

        return redirect()->route('admin.brands.show', $brand->id)
            ->with('success', 'Stock batch updated successfully.');
    }

    /**
     * Delete a stock batch (soft delete). Allowed even when sales used this batch;
     * existing sales are unaffected (they store their own cost and price).
     */
    public function stockDestroy(string $brandId, string $batchId)
    {
        $brand = Brand::findOrFail($brandId);
        $batch = InventoryBatch::where('brand_id', $brandId)->findOrFail($batchId);

        $batch->delete();

        return redirect()->route('admin.brands.show', $brand->id)
            ->with('success', 'Stock batch deleted. Existing sales are not affected—they keep their recorded cost and amount.');
    }

    /**
     * List archived (soft-deleted) stock batches for a brand.
     */
    public function stockArchived(string $id)
    {
        $brand = Brand::findOrFail($id);
        $batches = InventoryBatch::where('brand_id', $id)->onlyTrashed()->orderBy('deleted_at', 'desc')->get();
        return view('admin.brands.stock-archived', compact('brand', 'batches'));
    }

    /**
     * Restore an archived stock batch.
     */
    public function stockRestore(string $brandId, string $batchId)
    {
        $brand = Brand::findOrFail($brandId);
        $batch = InventoryBatch::where('brand_id', $brandId)->onlyTrashed()->findOrFail($batchId);
        $batch->restore();
        return redirect()->route('admin.brands.stock.archived', $brand->id)
            ->with('success', 'Batch restored. It is active again in Stock Batches.');
    }
}
