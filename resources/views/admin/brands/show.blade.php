@extends('admin.layout')

@section('title', 'Brand Details')

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-tag me-2"></i>Brand Information</span>
        <a href="{{ route('admin.brands.edit', $brand->id) }}" class="btn btn-sm btn-warning">
            <i class="fas fa-edit me-2"></i>Edit
        </a>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Name:</strong> {{ $brand->name }}</p>
                <p><strong>Description:</strong> {{ $brand->description ?? 'N/A' }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Current Stock:</strong> 
                    @if($brand->inventory)
                        <span class="badge {{ $brand->inventory->quantity < 10 ? 'bg-danger' : 'bg-success' }}">
                            {{ $brand->inventory->quantity }}
                        </span>
                    @else
                        <span class="badge bg-secondary">No Stock</span>
                    @endif
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
        @if($brand->sales->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Quantity</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($brand->sales as $sale)
                            <tr>
                                <td>{{ $sale->sale_date->format('M d, Y') }}</td>
                                <td>{{ $sale->customer->name }}</td>
                                <td><span class="badge bg-primary">{{ $sale->quantity }}</span></td>
                                <td>{{ $sale->price ? '$' . number_format($sale->price, 2) : 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted text-center py-4">No sales recorded for this brand.</p>
        @endif
    </div>
</div>
@endsection
