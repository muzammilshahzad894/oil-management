@extends('admin.layout')

@section('title', 'Brand Details')

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-tag me-2"></i>Brand Information</span>
        <div>
            <a href="{{ route('admin.brands.stock.create', $brand->id) }}" class="btn btn-sm btn-success me-2"><i class="fas fa-plus me-1"></i>Add Stock</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Name:</strong> {{ $brand->name }}</p>
                <p><strong>Description:</strong> {{ $brand->description ?? 'N/A' }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Current Stock:</strong>
                    <span class="badge {{ $availableStock < 10 ? 'bg-danger' : 'bg-success' }}">
                        {{ $availableStock }}
                    </span>
                    <small class="text-muted">(FIFO batches)</small>
                </p>
                <p><strong>Total Sales:</strong> <span class="badge bg-primary">{{ $brand->sales->count() }}</span></p>
            </div>
        </div>
    </div>
</div>

@if($brand->inventoryBatches->count() > 0 || $brand->inventoryBatches()->onlyTrashed()->count() > 0)
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-layer-group me-2"></i>Stock List</span>
        <a href="{{ route('admin.brands.stock.archived', $brand->id) }}" class="btn btn-sm btn-archived">
            <i class="fas fa-archive me-1"></i>Archived
        </a>
    </div>
    <div class="card-body">
        @if($brand->inventoryBatches->count() > 0)
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th>Date Added</th>
                        <th>Quantity</th>
                        <th>Remaining</th>
                        @if($showPurchasePrice ?? true)
                        <th>Cost per unit</th>
                        <th>Sale price</th>
                        @endif
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($brand->inventoryBatches->sortBy('created_at') as $batch)
                    @php $allocated = (int) $batch->saleBatchAllocations()->sum('quantity'); @endphp
                    <tr>
                        <td>{{ $batch->created_at->format('M d, Y H:i A') }}</td>
                        <td>{{ $batch->quantity }}</td>
                        <td><span class="badge {{ $batch->quantity_remaining < 10 ? 'bg-warning' : 'bg-secondary' }}">{{ $batch->quantity_remaining }}</span></td>
                        @if($showPurchasePrice ?? true)
                        <td>{{ format_amount($batch->cost_per_unit) }}</td>
                        <td>{{ $batch->sale_price !== null ? format_amount($batch->sale_price) : '—' }}</td>
                        @endif
                        <td>
                            <a href="{{ route('admin.brands.stock.edit', [$brand->id, $batch->id]) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('admin.brands.stock.destroy', [$brand->id, $batch->id]) }}" method="POST" class="d-inline js-confirm-delete" data-confirm-title="Delete this stock?" data-confirm-text="Existing sales are not affected." data-allocated="{{ $allocated }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-muted mb-0">No active batches. <a href="{{ route('admin.brands.stock.create', $brand->id) }}">Add stock</a> or view <a href="{{ route('admin.brands.stock.archived', $brand->id) }}">archived batches</a>.</p>
        @endif
    </div>
</div>
@endif

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
                            @if($showPurchasePrice ?? true)
                            <th>Cost per unit</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sales as $sale)
                            <tr>
                                <td>{{ $sale->sale_date->format('M d, Y') }}</td>
                                <td>{{ $sale->customer->name }}</td>
                                <td><span class="badge bg-primary">{{ $sale->quantity }}</span></td>
                                <td>
                                    {{ $sale->price ? format_amount($sale->price) : '—' }}
                                </td>
                                @if($showPurchasePrice ?? true)
                                <td>
                                    {{ $sale->cost_at_sale ? format_amount($sale->cost_at_sale) : '—' }}
                                </td>
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

@section('styles')
<style>
/* Archive button: visible on card header (gradient background) */
.card-header .btn-archived {
    color: #fff;
    background-color: rgba(255, 255, 255, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.5);
    font-weight: 500;
    padding: 0.35rem 0.75rem;
    border-radius: 0.375rem;
    transition: color .15s ease, background-color .15s ease, border-color .15s ease;
}
.card-header .btn-archived:hover {
    color: #fff;
    background-color: rgba(255, 255, 255, 0.35);
    border-color: rgba(255, 255, 255, 0.8);
}
</style>
@endsection
