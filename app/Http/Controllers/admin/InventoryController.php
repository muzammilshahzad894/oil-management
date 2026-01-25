<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Brand;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Inventory::with('brand');
        
        if ($request->has('search') && $request->search) {
            $query->whereHas('brand', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }
        
        $inventory = $query->orderBy('id', 'asc')->paginate(15);
        return view('admin.inventory.index', compact('inventory'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $brands = Brand::all();
        return view('admin.inventory.create', compact('brands'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'brand_id' => 'required|exists:brands,id|unique:inventories,brand_id',
            'quantity' => 'required|integer|min:0',
        ]);

        Inventory::create($request->all());

        return redirect()->route('admin.inventory.index')
            ->with('success', 'Inventory added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $inventory = Inventory::with('brand')->findOrFail($id);
        return view('admin.inventory.show', compact('inventory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $inventory = Inventory::with('brand')->findOrFail($id);
        $brands = Brand::all();
        return view('admin.inventory.edit', compact('inventory', 'brands'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        $inventory = Inventory::findOrFail($id);
        $inventory->update($request->only('quantity'));

        return redirect()->route('admin.inventory.index')
            ->with('success', 'Inventory updated successfully.');
    }

    /**
     * Add stock to inventory
     */
    public function addStock(Request $request, string $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $inventory = Inventory::findOrFail($id);
        $inventory->addStock($request->quantity);

        return redirect()->route('admin.inventory.index')
            ->with('success', 'Stock added successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $inventory = Inventory::findOrFail($id);
        $inventory->delete();

        return redirect()->route('admin.inventory.index')
            ->with('success', 'Inventory deleted successfully.');
    }
}
