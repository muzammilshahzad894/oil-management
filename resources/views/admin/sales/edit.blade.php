@extends('admin.layout')

@section('title', 'Edit Sale')

@section('content')
<div class="card">
    <div class="card-header">
        <i class="fas fa-edit me-2"></i>Edit Sale
    </div>
    <div class="card-body">
        <form action="{{ route('admin.sales.update', $sale->id) }}" method="POST" id="saleForm">
            @csrf
            @method('PUT')
            @include('admin.sales.partials.form', ['sale' => $sale, 'brands' => $brands])
        </form>
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
        
        // Set initial customer value
        $customerSearch.val('{{ $sale->customer->name }}');
        $customerId.val('{{ $sale->customer_id }}');
        
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
                                    '<small class="text-muted">' + (customer.phone || '') + ' ' + (customer.email || '') + '</small>' +
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
        
        // Brand search (client-side)
        let brandSearchTimeout;
        const $brandSearch = $('#brand_search');
        const $brandId = $('#brand_id');
        const $brandDropdown = $('#brand_dropdown');
        const allBrands = @json($brands);
        
        // Set initial brand value
        $brandSearch.val('{{ $sale->brand->name }}');
        $brandId.val('{{ $sale->brand_id }}');
        
        // Show current brand's stock on load
        (function() {
            const brandId = {{ $sale->brand_id }};
            const brand = allBrands.find(function(b) { return b.id == brandId; });
            if (brand) {
                const stock = brand.quantity ?? 0;
                const $stockInfo = $('#stock-info');
                $stockInfo.html('<i class="fas fa-box me-2"></i>Available stock: <span class="stock-number">' + stock + '</span>').show();
                $stockInfo.removeClass('stock-info-low stock-info-ok').addClass(parseInt(stock) < 10 ? 'stock-info-low' : 'stock-info-ok');
            }
        })();
        
        function showAllBrands() {
            let html = '';
            $.each(allBrands, function(index, brand) {
                const stock = brand.quantity ?? 0;
                html += '<a class="dropdown-item brand-option" href="#" data-id="' + brand.id + '" data-stock="' + stock + '">' +
                    brand.name + ' (Stock: ' + stock + ')' +
                    '</a>';
            });
            $brandDropdown.html(html);
        }
        
        $brandSearch.on('input', function() {
            clearTimeout(brandSearchTimeout);
            const search = $(this).val().toLowerCase().trim();
            
            brandSearchTimeout = setTimeout(function() {
                if (search.length === 0) {
                    showAllBrands();
                    $brandDropdown.show();
                } else {
                    const filtered = allBrands.filter(function(brand) {
                        return brand.name.toLowerCase().includes(search);
                    });
                    
                    if (filtered.length === 0) {
                        $brandDropdown.html('<div class="dropdown-item-text text-muted">No brands found</div>');
                    } else {
                        let html = '';
                        $.each(filtered, function(index, brand) {
                            const stock = brand.quantity ?? 0;
                            html += '<a class="dropdown-item brand-option" href="#" data-id="' + brand.id + '" data-stock="' + stock + '">' +
                                brand.name + ' (Stock: ' + stock + ')' +
                                '</a>';
                        });
                        $brandDropdown.html(html);
                    }
                    $brandDropdown.show();
                }
            }, 300);
        });
        
        // Handle brand selection
        $(document).on('click', '.brand-option', function(e) {
            e.preventDefault();
            const $option = $(this);
            const brandId = $option.data('id');
            const brand = allBrands.find(function(b) { return b.id == brandId; });
            
            $brandId.val(brandId);
            $brandSearch.val(brand.name);
            $brandDropdown.hide();
            
            // Update stock info - show in visible style
            const stock = $option.data('stock');
            const $stockInfo = $('#stock-info');
            if (stock !== null && stock !== '') {
                $stockInfo.html('<i class="fas fa-box me-2"></i>Available stock: <span class="stock-number">' + stock + '</span>').show();
                $stockInfo.removeClass('stock-info-low stock-info-ok').addClass(parseInt(stock) < 10 ? 'stock-info-low' : 'stock-info-ok');
            }
        });
        
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#brand_search, #brand_dropdown').length) {
                $brandDropdown.hide();
            }
        });
        
        // Show brand dropdown on focus
        $brandSearch.on('focus', function() {
            if ($(this).val().trim() === '') {
                showAllBrands();
                $brandDropdown.show();
            }
        });
        
        // Form submit loading
        $('#saleForm').on('submit', function() {
            const $btn = $('#submitBtn');
            $btn.prop('disabled', true);
            $btn.find('.spinner-border').removeClass('d-none');
            $btn.find('.btn-text').text('Updating...');
        });
    });
</script>
@endsection

@section('styles')
<style>
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
    
    .customer-option, .brand-option {
        display: block;
    }

    /* Available stock - visible and clear */
    .stock-info-display {
        font-size: 1rem;
        min-height: 2.5rem;
    }
    .stock-info-display .stock-number {
        font-size: 1.15rem;
    }
    .stock-info-ok {
        background-color: #d1e7dd;
        color: #0f5132;
        border: 1px solid #badbcc;
    }
    .stock-info-low {
        background-color: #f8d7da;
        color: #842029;
        border: 1px solid #f5c2c7;
    }
</style>
@endsection
