@extends('admin.layout')

@section('title', 'Add New Sale')

@section('content')
<div class="card">
    <div class="card-header">
        <i class="fas fa-shopping-cart me-2"></i>Add New Sale
    </div>
    <div class="card-body">
        <form action="{{ route('admin.sales.store') }}" method="POST" id="saleForm">
            @csrf
            @include('admin.sales.partials.form', ['brands' => $brands])
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Customer search with AJAX and debouncing
        let customerSearchTimeout;
        const $customerSearch = $('#customer_search');
        const $customerId = $('#customer_id');
        const $customerDropdown = $('#customer_dropdown');
        
        $customerSearch.on('input', function() {
            clearTimeout(customerSearchTimeout);
            const search = $(this).val().trim();
            
            if (search.length < 2) {
                $customerDropdown.hide();
                $customerId.val('');
                fetchExtraPaidAndShowOffer();
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
        
        // Set initial customer value if passed via URL
        @if(isset($selectedCustomerId) && $selectedCustomerId)
            @php
                $selectedCustomer = \App\Models\Customer::find($selectedCustomerId);
            @endphp
            @if($selectedCustomer)
                $customerSearch.val('{{ $selectedCustomer->name }}');
                $customerId.val('{{ $selectedCustomerId }}');
            @endif
        @endif
        
        // Handle customer selection
        $(document).on('click', '.customer-option', function(e) {
            e.preventDefault();
            const $option = $(this);
            $customerId.val($option.data('id'));
            $customerSearch.val($option.data('name'));
            $customerDropdown.hide();
            fetchExtraPaidAndShowOffer();
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
        
        function updateAmountFromBrandAndQuantity() {
            const brandId = $brandId.val();
            if (!brandId) return;
            const brand = allBrands.find(function(b) { return b.id == brandId; });
            if (!brand) return;
            const qty = parseInt($('#quantity').val(), 10) || 0;
            const salePrice = parseFloat(brand.sale_price) || 0;
            const calculated = qty * salePrice;
            $('#price').val(calculated > 0 ? calculated : '');
        }

        function showAllBrands() {
            let html = '';
            $.each(allBrands, function(index, brand) {
                const stock = brand.quantity ?? 0;
                const salePrice = brand.sale_price != null ? brand.sale_price : '';
                html += '<a class="dropdown-item brand-option" href="#" data-id="' + brand.id + '" data-stock="' + stock + '" data-sale-price="' + salePrice + '">' +
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
                            const salePrice = brand.sale_price != null ? brand.sale_price : '';
                            html += '<a class="dropdown-item brand-option" href="#" data-id="' + brand.id + '" data-stock="' + stock + '" data-sale-price="' + salePrice + '">' +
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
            // Calculate amount from sale price × quantity (admin can still edit amount)
            updateAmountFromBrandAndQuantity();
        });

        // When quantity changes, recalculate amount if a brand is selected
        $('#quantity').on('input', function() {
            updateAmountFromBrandAndQuantity();
            if ($('#extraPaidOfferCard').is(':visible')) updateExtraPaidBreakdown();
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

        // Restore brand and stock when form is re-displayed after validation error
        (function restoreBrandAndStock() {
            const brandId = $('#brand_id').val();
            if (!brandId) return;
            const brand = allBrands.find(function(b) { return String(b.id) === String(brandId); });
            if (brand) {
                const stock = brand.quantity ?? 0;
                $('#stock-info').html('<i class="fas fa-box me-2"></i>Available stock: <span class="stock-number">' + stock + '</span>').show()
                    .removeClass('stock-info-low stock-info-ok').addClass(parseInt(stock) < 10 ? 'stock-info-low' : 'stock-info-ok');
            }
        })();

        // Extra paid: show offer when customer has balance; checkbox to use in this sale
        const balanceUrlCreate = '{{ url("admin/customers") }}';
        let extraPaidBalance = 0;

        function updateExtraPaidBreakdown() {
            if (!$('#use_extra_paid_checkbox').is(':checked')) {
                $('#initial_extra_paid_amount').val('0');
                $('#extraPaidBreakdown').hide();
                return;
            }
            const price = parseFloat($('#price').val()) || 0;
            const useAmount = Math.min(extraPaidBalance, price);
            $('#initial_extra_paid_amount').val(useAmount > 0 ? useAmount : '0');
            if (useAmount > 0) {
                const remaining = price - useAmount;
                $('#extraPaidBreakdown').html('Total amount: ' + price + ' — Using ' + useAmount + ' from extra paid → <strong>' + remaining + ' remaining</strong> to collect.').show();
            } else {
                $('#extraPaidBreakdown').hide();
            }
        }

        function fetchExtraPaidAndShowOffer() {
            const customerId = $('#customer_id').val();
            $('#extraPaidOfferCard').hide();
            $('#use_extra_paid_checkbox').prop('checked', false);
            $('#initial_extra_paid_amount').val('0');
            extraPaidBalance = 0;
            if (!customerId) return;
            $.get(balanceUrlCreate + '/' + customerId + '/extra-paid/balance', function(data) {
                extraPaidBalance = parseFloat(data.balance) || 0;
                if (extraPaidBalance > 0) {
                    $('#extraPaidOfferText').text('This customer has an amount of ' + data.formatted + ' extra paid. Do you want to use that extra paid amount in this sale?');
                    $('#extraPaidOfferCard').show();
                    var oldExtra = parseFloat($('#initial_extra_paid_amount').val()) || 0;
                    if (oldExtra > 0) $('#use_extra_paid_checkbox').prop('checked', true);
                    updateExtraPaidBreakdown();
                }
            });
        }

        $('#use_extra_paid_checkbox').on('change', updateExtraPaidBreakdown);
        $('#price').on('input', function() {
            if ($('#extraPaidOfferCard').is(':visible')) updateExtraPaidBreakdown();
        });

        if ($('#customer_id').val()) fetchExtraPaidAndShowOffer();

        // Form submit: confirm when paying more than sale total (excess goes to wallet)
        var _allowOverpaymentSubmit = false;
        $('#saleForm').on('submit', function(e) {
            if (_allowOverpaymentSubmit) {
                _allowOverpaymentSubmit = false;
                return;
            }
            const price = parseFloat($('#price').val()) || 0;
            const initialPayment = parseFloat($('#initial_payment').val()) || 0;
            const fromExtra = parseFloat($('#initial_extra_paid_amount').val()) || 0;
            const totalPaying = initialPayment + fromExtra;
            if (price > 0 && totalPaying > price) {
                e.preventDefault();
                const excess = totalPaying - price;
                var msg = 'Total sale cost is ' + price + ' and you are paying ' + totalPaying + '. Remaining ' + excess + ' will be added to the customer\'s wallet.';
                function reenableSubmitButtons() {
                    $('#submitBtn, #submitPrintBtn').prop('disabled', false);
                    $('#submitBtn .spinner-border, #submitPrintBtn .spinner-border').addClass('d-none');
                    $('#submitBtn .btn-text').text('Save');
                    $('#submitPrintBtn .btn-text').text('Save and Print');
                }
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Add extra to wallet?',
                        html: '<p>' + msg + '</p>',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'OK',
                        cancelButtonText: 'Cancel'
                    }).then(function(r) {
                        if (r.isConfirmed) {
                            _allowOverpaymentSubmit = true;
                            $('#saleForm').submit();
                        } else {
                            reenableSubmitButtons();
                        }
                    });
                } else {
                    if (confirm(msg + '\n\nClick OK to continue or Cancel to go back.')) {
                        _allowOverpaymentSubmit = true;
                        $('#saleForm').submit();
                    } else {
                        reenableSubmitButtons();
                    }
                }
                return;
            }
            const $clickedButton = $(document.activeElement);
            const isPrintButton = $clickedButton.attr('name') === 'action' && $clickedButton.val() === 'save_and_print';
            const $btn = isPrintButton ? $('#submitPrintBtn') : $('#submitBtn');
            const $otherBtn = isPrintButton ? $('#submitBtn') : $('#submitPrintBtn');
            if ($btn.length) {
                $btn.prop('disabled', true);
                $btn.find('.spinner-border').removeClass('d-none');
                $btn.find('.btn-text').text(isPrintButton ? 'Saving & Printing...' : 'Saving...');
            }
            if ($otherBtn.length) $otherBtn.prop('disabled', true);
        });
    });
</script>
@endsection

@section('styles')
<style>
    #customer_dropdown,
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
    
    .customer-option, .brand-option {
        display: block;
    }
    
    .position-relative {
        z-index: 1;
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
