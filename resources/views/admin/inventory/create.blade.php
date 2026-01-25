@extends('admin.layout')

@section('title', 'Add Inventory')

@section('content')
<div class="card">
    <div class="card-header">
        <i class="fas fa-warehouse me-2"></i>Add Inventory
    </div>
    <div class="card-body">
        <form action="{{ route('admin.inventory.store') }}" method="POST" id="inventoryForm">
            @csrf
            @include('admin.inventory.partials.form', ['brands' => $brands])
        </form>
        
        <script>
            $(document).ready(function() {
                // Brand search (client-side)
                let brandSearchTimeout;
                const $brandSearch = $('#brand_search');
                const $brandId = $('#brand_id');
                const $brandDropdown = $('#brand_dropdown');
                const allBrands = @json($brands);
                
                function showAllBrands() {
                    let html = '';
                    $.each(allBrands, function(index, brand) {
                        html += '<a class="dropdown-item brand-option" href="#" data-id="' + brand.id + '">' + brand.name + '</a>';
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
                                    html += '<a class="dropdown-item brand-option" href="#" data-id="' + brand.id + '">' + brand.name + '</a>';
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
                $('#inventoryForm').on('submit', function() {
                    const $btn = $('#submitBtn');
                    $btn.prop('disabled', true);
                    $btn.find('.spinner-border').removeClass('d-none');
                    $btn.find('.btn-text').text('Saving...');
                });
            });
        </script>
        
        <style>
            #brand_dropdown {
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
            
            .brand-option {
                display: block;
            }
            
            .position-relative {
                z-index: 1;
            }
        </style>
    </div>
</div>
@endsection
