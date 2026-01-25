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
                                `<a class="dropdown-item brand-option" href="#" data-id="${brand.id}">
                                    ${brand.name}
                                </a>`
                            ).join('');
                        }
                        brandDropdown.style.display = 'block';
                    }
                }, 300);
            });
            
            function showAllBrands() {
                brandDropdown.innerHTML = allBrands.map(brand => 
                    `<a class="dropdown-item brand-option" href="#" data-id="${brand.id}">
                        ${brand.name}
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
            document.getElementById('inventoryForm').addEventListener('submit', function() {
                const btn = document.getElementById('submitBtn');
                btn.disabled = true;
                btn.querySelector('.spinner-border').classList.remove('d-none');
                btn.querySelector('.btn-text').textContent = 'Saving...';
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
