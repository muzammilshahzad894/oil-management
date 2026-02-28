<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
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
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'quantity' => 'nullable|integer|min:0',
            'cost_price' => 'nullable|numeric|min:0',
        ];
        if ((int) ($request->input('quantity') ?? 0) > 0) {
            $rules['cost_price'] = 'required|numeric|min:0';
        }
        $request->validate($rules);

        Brand::create($request->only(['name', 'description', 'quantity', 'cost_price']));

        return redirect()->route('admin.brands.index')
            ->with('success', 'Brand created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $brand = Brand::findOrFail($id);
        $sales = $brand->sales()->with('customer')->orderBy('sale_date', 'desc')->orderBy('created_at', 'desc')->paginate(15);
        return view('admin.brands.show', compact('brand', 'sales'));
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
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'quantity' => 'nullable|integer|min:0',
            'cost_price' => 'nullable|numeric|min:0',
        ];
        if ((int) ($request->input('quantity') ?? 0) > 0) {
            $rules['cost_price'] = 'required|numeric|min:0';
        }
        $request->validate($rules);

        $brand = Brand::findOrFail($id);
        $brand->update($request->only(['name', 'description', 'quantity', 'cost_price']));

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
}
