@extends('admin.layout')

@section('title', 'Sale Details')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-shopping-cart me-2"></i>Sale Information</span>
        <div>
            <a href="{{ route('admin.sales.receipt', $sale->id) }}" target="_blank" class="btn btn-sm btn-success me-2">
                <i class="fas fa-print me-2"></i>Get Receipt
            </a>
            <a href="{{ route('admin.sales.edit', $sale->id) }}" class="btn btn-sm btn-warning">
                <i class="fas fa-edit me-2"></i>Edit
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Customer:</strong> 
                    {{ $sale->customer->name }}
                    @if($sale->customer->trashed())
                        <span class="badge bg-danger ms-2" title="This customer has been deleted">
                            <i class="fas fa-trash"></i> Deleted
                        </span>
                    @endif
                </p>
                <p><strong>Brand:</strong> 
                    {{ $sale->brand->name }}
                    @if($sale->brand->trashed())
                        <span class="badge bg-danger ms-2" title="This brand has been deleted">
                            <i class="fas fa-trash"></i> Deleted
                        </span>
                    @endif
                </p>
                <p><strong>Quantity:</strong> {{ $sale->quantity }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Price:</strong> {{ $sale->price ? number_format($sale->price, 0) : 'N/A' }}</p>
                <p><strong>Sale Date:</strong> {{ $sale->sale_date->format('M d, Y') }}</p>
                <p><strong>Notes:</strong> {{ $sale->notes ?? 'N/A' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
