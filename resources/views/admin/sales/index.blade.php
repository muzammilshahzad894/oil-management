@extends('admin.layout')

@section('title', 'Sales')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-shopping-cart me-2"></i>Sales</span>
        <a href="{{ route('admin.sales.create') }}" class="btn btn-light btn-sm">
            <i class="fas fa-plus me-2"></i>Add New Sale
        </a>
    </div>
    <div class="card-body">
        <!-- Date Filter -->
        <form method="GET" action="{{ route('admin.sales.index') }}" class="row g-3 mb-4 align-items-end">
            <div class="col-md-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}">
            </div>
            <div class="col-md-3">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}">
            </div>
            <div class="col-md-auto d-flex align-items-end">
                <button type="submit" class="btn btn-primary" style="height: 38px;">
                    <i class="fas fa-filter me-2"></i>Filter
                </button>
            </div>
            <div class="col-md-auto d-flex align-items-end">
                <a href="{{ route('admin.sales.index') }}" class="btn btn-secondary" style="height: 38px;">
                    <i class="fas fa-redo me-2"></i>Reset
                </a>
            </div>
        </form>
        
        @if($sales->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Brand</th>
                            <th>Quantity</th>
                            <th>Total Amount</th>
                            <th>Remaining Amount</th>
                            @if($showPurchasePrice)
                            <th>Purchase price</th>
                            @endif
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sales as $sale)
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
                                <td><span>{{ $sale->quantity }}</span></td>
                                <td>{{ format_amount($sale->price) }}</td>
                                <td>{{ format_amount($sale->balance_due) }}</td>
                                @if($showPurchasePrice)
                                <td>{{ $sale->cost_at_sale !== null ? format_amount($sale->cost_at_sale) : '—' }}</td>
                                @endif
                                <td>
                                    @if($sale->total_paid >= (float) $sale->price)
                                        <span class="badge bg-success" data-bs-toggle="tooltip" data-bs-placement="top" title="Payment completed">Paid</span>
                                    @elseif($sale->total_paid > 0)
                                        <span class="badge bg-warning text-dark" data-bs-toggle="tooltip" data-bs-placement="top" title="Partial payment">Partial</span>
                                    @else
                                        <span class="badge bg-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="Payment pending">Unpaid</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.sales.show', $sale->id) }}" class="btn btn-sm btn-info" title="View">View</a>
                                    <a href="{{ route('admin.sales.edit', $sale->id) }}" class="btn btn-sm btn-warning" title="Edit">Edit</a>
                                    <button type="button" class="btn btn-sm btn-danger" title="Delete" onclick="confirmDelete({{ $sale->id }}, '{{ addslashes($sale->customer->name) }}', '{{ addslashes($sale->brand->name) }}')">Delete</button>
                                    <form id="delete-form-{{ $sale->id }}" action="{{ route('admin.sales.destroy', $sale->id) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
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
            <p class="text-muted text-center py-4">No sales recorded yet. <a href="{{ route('admin.sales.create') }}">Add your first sale</a></p>
        @endif
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

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmDelete(id, customerName, brandName) {
        Swal.fire({
            title: 'Are you sure?',
            html: '<p>You want to delete this sale?</p><p><strong>Customer:</strong> ' + customerName + '<br><strong>Brand:</strong> ' + brandName + '</p><p class="text-danger"><small>Inventory will be restored automatically.</small></p>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#delete-form-' + id).submit();
            }
        });
    }
</script>
@endsection
