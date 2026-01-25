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
<script>
    // Customer search with AJAX and debouncing
    let customerSearchTimeout;
    const customerSearch = document.getElementById('customer_search');
    const customerId = document.getElementById('customer_id');
    const customerDropdown = document.getElementById('customer_dropdown');
    
    customerSearch.addEventListener('input', function() {
        clearTimeout(customerSearchTimeout);
        const search = this.value.trim();
        
        if (search.length < 2) {
            customerDropdown.style.display = 'none';
            customerId.value = '';
            return;
        }
        
        customerSearchTimeout = setTimeout(() => {
            customerDropdown.style.display = 'block';
            customerDropdown.innerHTML = '<div class="text-center p-3"><div class="spinner-border spinner-border-sm text-primary" role="status"></div></div>';
            
            fetch('{{ route("admin.sales.search-customers") }}?search=' + encodeURIComponent(search))
                .then(response => response.json())
                .then(data => {
                    if (data.length === 0) {
                        customerDropdown.innerHTML = '<div class="dropdown-item-text text-muted">No customers found</div>';
                    } else {
                        customerDropdown.innerHTML = data.map(customer => 
                            `<a class="dropdown-item customer-option" href="#" data-id="${customer.id}" data-name="${customer.name}">
                                <strong>${customer.name}</strong><br>
                                <small class="text-muted">${customer.phone || ''} ${customer.email || ''}</small>
                            </a>`
                        ).join('');
                    }
                })
                .catch(error => {
                    customerDropdown.innerHTML = '<div class="dropdown-item-text text-danger">Error loading customers</div>';
                });
        }, 500);
    });
    
    // Set initial customer value if passed via URL
    @if(isset($selectedCustomerId) && $selectedCustomerId)
        @php
            $selectedCustomer = \App\Models\Customer::find($selectedCustomerId);
        @endphp
        @if($selectedCustomer)
            customerSearch.value = '{{ $selectedCustomer->name }}';
            customerId.value = '{{ $selectedCustomerId }}';
        @endif
    @endif
    
    // Handle customer selection
    document.addEventListener('click', function(e) {
        if (e.target.closest('.customer-option')) {
            e.preventDefault();
            const option = e.target.closest('.customer-option');
            customerId.value = option.dataset.id;
            customerSearch.value = option.dataset.name;
            customerDropdown.style.display = 'none';
        } else if (!e.target.closest('#customer_search') && !e.target.closest('#customer_dropdown')) {
            customerDropdown.style.display = 'none';
        }
    });
    
    // Brand search (client-side)
    let brandSearchTimeout;
    const brandSearch = document.getElementById('brand_search');
    const brandId = document.getElementById('brand_id');
    const brandDropdown = document.getElementById('brand_dropdown');
    const allBrands = @json($brands);
    
    brandSearch.addEventListener('input', function() {
        clearTimeout(brandSearchTimeout);
        const search = this.value.toLowerCase().trim();
        
        brandSearchTimeout = setTimeout(() => {
            if (search.length === 0) {
                brandDropdown.style.display = 'block';
                showAllBrands();
            } else {
                const filtered = allBrands.filter(brand => 
                    brand.name.toLowerCase().includes(search)
                );
                
                if (filtered.length === 0) {
                    brandDropdown.innerHTML = '<div class="dropdown-item-text text-muted">No brands found</div>';
                } else {
                    brandDropdown.innerHTML = filtered.map(brand => 
                        `<a class="dropdown-item brand-option" href="#" data-id="${brand.id}" data-stock="${brand.inventory ? brand.inventory.quantity : 0}">
                            ${brand.name} ${brand.inventory ? '(Stock: ' + brand.inventory.quantity + ')' : '(No Stock)'}
                        </a>`
                    ).join('');
                }
                brandDropdown.style.display = 'block';
            }
        }, 300);
    });
    
    function showAllBrands() {
        brandDropdown.innerHTML = allBrands.map(brand => 
            `<a class="dropdown-item brand-option" href="#" data-id="${brand.id}" data-stock="${brand.inventory ? brand.inventory.quantity : 0}">
                ${brand.name} ${brand.inventory ? '(Stock: ' + brand.inventory.quantity + ')' : '(No Stock)'}
            </a>`
        ).join('');
    }
    
    // Handle brand selection
    document.addEventListener('click', function(e) {
        if (e.target.closest('.brand-option')) {
            e.preventDefault();
            const option = e.target.closest('.brand-option');
            brandId.value = option.dataset.id;
            brandSearch.value = allBrands.find(b => b.id == option.dataset.id).name;
            brandDropdown.style.display = 'none';
            
            // Update stock info
            const stock = option.dataset.stock;
            const stockInfo = document.getElementById('stock-info');
            if (stock !== null && stock !== '') {
                stockInfo.textContent = 'Available stock: ' + stock;
                stockInfo.className = parseInt(stock) < 10 ? 'text-danger' : 'text-success';
            }
        } else if (!e.target.closest('#brand_search') && !e.target.closest('#brand_dropdown')) {
            brandDropdown.style.display = 'none';
        }
    });
    
    // Show brand dropdown on focus
    brandSearch.addEventListener('focus', function() {
        if (this.value.trim() === '') {
            showAllBrands();
            brandDropdown.style.display = 'block';
        }
    });
    
    // Form submit loading
    document.getElementById('saleForm').addEventListener('submit', function(e) {
        const clickedButton = e.submitter || document.activeElement;
        const isPrintButton = clickedButton && clickedButton.name === 'action' && clickedButton.value === 'save_and_print';
        
        const btn = isPrintButton ? document.getElementById('submitPrintBtn') : document.getElementById('submitBtn');
        const otherBtn = isPrintButton ? document.getElementById('submitBtn') : document.getElementById('submitPrintBtn');
        
        if (btn) {
            btn.disabled = true;
            const spinner = btn.querySelector('.spinner-border');
            const text = btn.querySelector('.btn-text');
            if (spinner) spinner.classList.remove('d-none');
            if (text) text.textContent = isPrintButton ? 'Saving & Printing...' : 'Saving...';
        }
        
        if (otherBtn) {
            otherBtn.disabled = true;
        }
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
    
    .dropdown-menu {
        position: absolute;
        top: 100%;
        left: 0;
        z-index: 9999 !important;
        margin-top: 0.25rem;
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
</style>
@endsection
