@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-value">{{ $totalCustomers }}</div>
            <div class="stat-label">Total Customers</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-tags"></i>
            </div>
            <div class="stat-value">{{ $totalBrands }}</div>
            <div class="stat-label">Total Brands</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-value">{{ $totalSales }}</div>
            <div class="stat-label">Total Sales</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-warehouse"></i>
            </div>
            <div class="stat-value">{{ $totalInventory }}</div>
            <div class="stat-label">Total Inventory</div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-clock me-2"></i> Recent Sales
            </div>
            <div class="card-body">
                @if($recentSales->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Brand</th>
                                    <th>Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentSales as $sale)
                                    <tr>
                                        <td>{{ $sale->sale_date->format('M d, Y') }}</td>
                                        <td>
                                            {{ $sale->customer->name }}
                                            @if($sale->customer->trashed())
                                                <span class="deleted-item-badge" data-bs-toggle="tooltip" data-bs-placement="top" title="This customer has been deleted">
                                                    <i class="fas fa-circle-xmark"></i>
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $sale->brand->name }}
                                            @if($sale->brand->trashed())
                                                <span class="deleted-item-badge" data-bs-toggle="tooltip" data-bs-placement="top" title="This brand has been deleted">
                                                    <i class="fas fa-circle-xmark"></i>
                                                </span>
                                            @endif
                                        </td>
                                        <td><span class="badge bg-primary">{{ $sale->quantity }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center py-4">No sales recorded yet.</p>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-exclamation-triangle me-2"></i> Low Stock Alert
            </div>
            <div class="card-body">
                @if($lowStock->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($lowStock as $item)
                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                <div>
                                    <i class="fas fa-exclamation-circle text-danger me-2"></i>
                                    <strong>{{ $item->brand->name }}</strong>
                                </div>
                                <span class="badge bg-danger">{{ $item->quantity }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3 mb-0">All stocks are sufficient.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .deleted-item-badge {
        padding: 0.3rem 0.3rem !important;
        color: red;
    }
</style>
@endsection