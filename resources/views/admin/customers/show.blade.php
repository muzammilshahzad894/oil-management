@extends('admin.layout')

@section('title', 'Customer History')

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-user me-2"></i>Customer Information</span>
        <div>
            <a href="{{ route('admin.customers.edit', $customer->id) }}" class="btn btn-sm btn-warning">Edit</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Name:</strong> {{ $customer->name }}</p>
                <p><strong>Phone:</strong> {{ $customer->phone ?? 'N/A' }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Email:</strong> {{ $customer->email ?? 'N/A' }}</p>
                <p><strong>Address:</strong> {{ $customer->address ?? 'N/A' }}</p>
            </div>
        </div>
        <div class="mt-3">
            <h5>Total Quantity Purchased: <span class="badge bg-primary">{{ $totalQuantity }}</span></h5>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <i class="fas fa-history me-2"></i>Purchase History
    </div>
    <div class="card-body">
        @if($sales->count() > 0)
            <div class="text-muted small mb-2">
                Showing {{ $sales->firstItem() }} to {{ $sales->lastItem() }} of {{ $sales->total() }} records
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Brand</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            @if($showPurchasePrice ?? true)
                            <th>Purchase price</th>
                            @endif
                            <th>Status</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sales as $sale)
                            <tr>
                                <td>{{ $sale->sale_date->format('M d, Y') }}</td>
                                <td>{{ $sale->brand->name }}</td>
                                <td><span class="badge bg-primary">{{ $sale->quantity }}</span></td>
                                <td>{{ $sale->price !== null ? format_amount($sale->price) : 'N/A' }}</td>
                                @if($showPurchasePrice ?? true)
                                <td>{{ $sale->cost_at_sale !== null ? format_amount($sale->cost_at_sale) : '—' }}</td>
                                @endif
                                <td>
                                    @if($sale->total_paid >= $sale->price)
                                        <span class="badge bg-success">Paid</span>
                                    @elseif($sale->total_paid > 0)
                                        <span class="badge bg-warning text-dark">Partial</span>
                                    @else
                                        <span class="badge bg-danger">Unpaid</span>
                                    @endif
                                </td>
                                <td>{{ $sale->notes ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('admin.sales.show', $sale->id) }}" class="btn btn-sm btn-info">View</a>
                                    <a href="{{ route('admin.sales.edit', $sale->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <form action="{{ route('admin.sales.destroy', $sale->id) }}" method="POST" class="d-inline js-confirm-delete" data-confirm-title="Delete this sale?" data-confirm-text="Inventory will be restored.">
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
            <div class="text-muted small mt-2">
                Showing {{ $sales->firstItem() }} to {{ $sales->lastItem() }} of {{ $sales->total() }} records
            </div>
            <div class="mt-4">
                {{ $sales->links() }}
            </div>
        @else
            <p class="text-muted text-center py-4">No purchase history found for this customer.</p>
        @endif
    </div>
</div>
@endsection
