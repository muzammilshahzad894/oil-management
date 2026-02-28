@extends('admin.layout')

@section('title', 'Brand Details')

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-tag me-2"></i>Brand Information</span>
        <a href="{{ route('admin.brands.edit', $brand->id) }}" class="btn btn-sm btn-warning">Edit</a>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Name:</strong> {{ $brand->name }}</p>
                <p><strong>Description:</strong> {{ $brand->description ?? 'N/A' }}</p>
            </div>
            <div class="col-md-6">
                @if($showPurchasePrice && $brand->cost_price !== null)
                <p><strong>Purchase price:</strong> {{ format_amount($brand->cost_price) }}</p>
                @endif
                <p><strong>Current Stock:</strong>
                    <span class="badge {{ ($brand->quantity ?? 0) < 10 ? 'bg-danger' : 'bg-success' }}">
                        {{ $brand->quantity ?? 0 }}
                    </span>
                </p>
                <p><strong>Total Sales:</strong> <span class="badge bg-primary">{{ $brand->sales->count() }}</span></p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <i class="fas fa-shopping-cart me-2"></i>Sales History
    </div>
    <div class="card-body">
        @if($sales->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            @if($showPurchasePrice)
                            <th>Purchase price</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sales as $sale)
                            <tr>
                                <td>{{ $sale->sale_date->format('M d, Y') }}</td>
                                <td>{{ $sale->customer->name }}</td>
                                <td><span class="badge bg-primary">{{ $sale->quantity }}</span></td>
                                <td>{{ $sale->price ?? 'N/A' }}</td>
                                @if($showPurchasePrice)
                                <td>{{ $sale->cost_at_sale !== null ? format_amount($sale->cost_at_sale) : '—' }}</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="text-muted small mt-2">
                Showing {{ $sales->firstItem() }} to {{ $sales->lastItem() }} of {{ $sales->total() }} records
            </div>
            <div class="mt-4">
                {{ $sales->links() }}
            </div>
        @else
            <p class="text-muted text-center py-4">No sales recorded for this brand.</p>
        @endif
    </div>
</div>
@endsection
