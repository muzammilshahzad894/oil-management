@extends('admin.layout')

@section('title', 'Customer Report')

@section('content')
<div class="card">
    <div class="card-header">
        <i class="fas fa-chart-line me-2"></i>Customer Report
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.reports.customer') }}" class="row g-3 mb-4 align-items-end">
            <div class="col-md-3">
                <label for="customer_search" class="form-label">Customer</label>
                <div class="position-relative w-100 customer-dropdown-wrapper">
                    <input type="text" 
                           class="form-control" 
                           id="customer_search" 
                           placeholder="Type to search customer..."
                           autocomplete="off"
                           value="{{ request('customer_name', isset($selectedCustomer) ? $selectedCustomer->name : '') }}">
                    <input type="hidden" id="customer_id" name="customer_id" value="{{ request('customer_id', '') }}">
                    <div id="customer_dropdown" class="dropdown-menu w-100" style="display: none;">
                        <div class="text-center p-3">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate ?? now()->subYear()->format('Y-m-d') }}">
            </div>
            <div class="col-md-2">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate ?? now()->format('Y-m-d') }}">
            </div>
            <div class="col-md-2">
                <label for="is_paid" class="form-label">Payment Status</label>
                <select class="form-select" id="is_paid" name="is_paid">
                    <option value="">All</option>
                    <option value="1" {{ request('is_paid') == '1' ? 'selected' : '' }}>Paid</option>
                    <option value="0" {{ request('is_paid') == '0' ? 'selected' : '' }}>Unpaid</option>
                </select>
            </div>
            <div class="col-md-auto d-flex align-items-end">
                <button type="submit" class="btn btn-primary" style="height: 38px;">
                    <i class="fas fa-filter me-2"></i>Generate Report
                </button>
            </div>
            <div class="col-md-auto d-flex align-items-end">
                <a href="{{ route('admin.reports.customer') }}" class="btn btn-secondary" style="height: 38px;">
                    <i class="fas fa-redo me-2"></i>Reset
                </a>
            </div>
            @if(request('customer_id'))
            <div class="col-md-auto d-flex align-items-end">
                <a href="{{ route('admin.reports.customer.export', request()->all()) }}" class="btn btn-success" style="height: 38px;">
                    <i class="fas fa-file-excel me-2"></i>Export Excel
                </a>
            </div>
            @endif
        </form>
        
        @if(!$hasCustomer)
            <div class="bg-info bg-opacity-10 border border-info border-opacity-25 rounded p-3 text-center">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Please select a customer to show report.</strong>
            </div>        
        @elseif($paginatedSales && $paginatedSales->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Brand</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($paginatedSales as $sale)
                            <tr>
                                <td>{{ $sale->sale_date->format('M d, Y') }}</td>
                                <td>
                                    {{ $sale->customer->name }}
                                    @if($sale->customer->trashed())
                                        <span class="badge bg-danger ms-1" title="Deleted customer">
                                            <i class="fas fa-trash"></i>
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    {{ $sale->brand->name }}
                                    @if($sale->brand->trashed())
                                        <span class="badge bg-danger ms-1" title="Deleted brand">
                                            <i class="fas fa-trash"></i>
                                        </span>
                                    @endif
                                </td>
                                <td><span>{{ $sale->quantity }}</span></td>
                                <td>{{ number_format($sale->price, 0) }}</td>
                                <td>
                                    @if($sale->is_paid)
                                        <span class="badge bg-success">Paid</span>
                                    @else
                                        <span class="badge bg-danger">Unpaid</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="3" class="text-end">Total Paid:</th>
                            <th>{{ $allSales->where('is_paid', true)->sum('quantity') }}</th>
                            <th>{{ number_format($allSales->where('is_paid', true)->sum('price'), 0) }}</th>
                            <th>{{ $allSales->where('is_paid', true)->count() }} sales</th>
                        </tr>
                        <tr>
                            <th colspan="3" class="text-end">Total Unpaid:</th>
                            <th>{{ $allSales->where('is_paid', false)->sum('quantity') }}</th>
                            <th>{{ number_format($allSales->where('is_paid', false)->sum('price'), 0) }}</th>
                            <th>{{ $allSales->where('is_paid', false)->count() }} sales</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($paginatedSales && $paginatedSales->hasPages())
                <div class="mt-4">
                    {{ $paginatedSales->links() }}
                </div>
            @endif
        @elseif($hasCustomer)
            <p class="text-muted text-center py-4">No sales found for the selected criteria.</p>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Customer search with AJAX and debouncing
        let customerSearchTimeout;
        const $customerSearch = $('#customer_search');
        const $customerId = $('#customer_id');
        const $customerDropdown = $('#customer_dropdown');
        
        // Set initial customer value if exists
        @if(request('customer_id') && isset($selectedCustomer) && $selectedCustomer)
            $customerSearch.val('{{ $selectedCustomer->name }}');
            $customerId.val('{{ request('customer_id') }}');
        @endif
        
        $customerSearch.on('input', function() {
            clearTimeout(customerSearchTimeout);
            const search = $(this).val().trim();
            
            if (search.length < 2) {
                $customerDropdown.hide();
                if (search.length === 0) {
                    $customerId.val('');
                }
                return;
            }
            
            customerSearchTimeout = setTimeout(function() {
                $customerDropdown.show().html('<div class="text-center p-3"><div class="spinner-border spinner-border-sm text-primary" role="status"></div></div>');
                
                $.ajax({
                    url: '{{ route("admin.sales.search-customers") }}',
                    method: 'GET',
                    data: { search: search },
                    success: function(data) {
                        if (data.length === 0) {
                            $customerDropdown.html('<div class="dropdown-item-text text-muted">No customers found</div>');
                        } else {
                            let html = '';
                            $.each(data, function(index, customer) {
                                html += '<a class="dropdown-item customer-option" href="#" data-id="' + customer.id + '" data-name="' + customer.name + '">' +
                                    '<strong>' + customer.name + '</strong><br>' +
                                    '<small class="text-muted">' + (customer.phone || '') + '</small>' +
                                    '</a>';
                            });
                            $customerDropdown.html(html);
                        }
                    },
                    error: function() {
                        $customerDropdown.html('<div class="dropdown-item-text text-danger">Error loading customers</div>');
                    }
                });
            }, 500);
        });
        
        // Handle customer selection
        $(document).on('click', '.customer-option', function(e) {
            e.preventDefault();
            const $option = $(this);
            $customerId.val($option.data('id'));
            $customerSearch.val($option.data('name'));
            $customerDropdown.hide();
        });
        
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#customer_search, #customer_dropdown').length) {
                $customerDropdown.hide();
            }
        });
    });
</script>
@endsection

@section('styles')
<style>
    #customer_dropdown {
        max-height: 250px !important;
        overflow-y: auto;
        overflow-x: hidden;
        z-index: 9999 !important;
        position: absolute !important;
    }
    
    .customer-dropdown-wrapper {
        position: relative;
    }

    #customer_dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        max-height: 250px;
        overflow-y: auto;
        z-index: 1055; /* Bootstrap modal safe */
    }

    .card,
    .card-body,
    .row {
        overflow: visible !important;
    }

    
    .dropdown-item {
        cursor: pointer;
        padding: 0.75rem 1rem;
    }
    
    .dropdown-item:hover {
        background-color: #f7fafc;
    }
    
    .customer-option {
        display: block;
    }
    
    .position-relative {
        z-index: 1;
    }
</style>
@endsection
