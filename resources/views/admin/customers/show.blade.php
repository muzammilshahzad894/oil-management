@extends('admin.layout')

@section('title', 'Customer History')

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-user me-2"></i>Customer Information</span>
        <a href="{{ route('admin.customers.edit', $customer->id) }}" class="btn btn-sm btn-warning">
            <i class="fas fa-edit me-2"></i>Edit
        </a>
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
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Brand</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sales as $sale)
                            <tr>
                                <td>{{ $sale->sale_date->format('M d, Y') }}</td>
                                <td>{{ $sale->brand->name }}</td>
                                <td><span class="badge bg-primary">{{ $sale->quantity }}</span></td>
                                <td>{{ $sale->price ?? 'N/A' }}</td>
                                <td>{{ $sale->notes ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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
