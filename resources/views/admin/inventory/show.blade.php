@extends('admin.layout')

@section('title', 'Inventory Details')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-warehouse me-2"></i>Inventory Information</span>
        <a href="{{ route('admin.inventory.edit', $inventory->id) }}" class="btn btn-sm btn-warning">
            <i class="fas fa-edit me-2"></i>Edit
        </a>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Brand:</strong> {{ $inventory->brand->name }}</p>
                <p><strong>Quantity:</strong> 
                    <span class="badge {{ $inventory->quantity < 10 ? 'bg-danger' : ($inventory->quantity < 50 ? 'bg-warning' : 'bg-success') }}">
                        {{ $inventory->quantity }}
                    </span>
                </p>
            </div>
            <div class="col-md-6">
                <p><strong>Status:</strong> 
                    @if($inventory->quantity < 10)
                        <span class="badge bg-danger">Low Stock</span>
                    @elseif($inventory->quantity < 50)
                        <span class="badge bg-warning">Medium Stock</span>
                    @else
                        <span class="badge bg-success">In Stock</span>
                    @endif
                </p>
                <p><strong>Last Updated:</strong> {{ $inventory->updated_at->format('M d, Y h:i A') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
