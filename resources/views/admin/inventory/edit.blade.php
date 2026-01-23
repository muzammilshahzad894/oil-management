@extends('admin.layout')

@section('title', 'Edit Inventory')

@section('content')
<div class="card">
    <div class="card-header">
        <i class="fas fa-edit me-2"></i>Edit Inventory
    </div>
    <div class="card-body">
        <form action="{{ route('admin.inventory.update', $inventory->id) }}" method="POST" id="inventoryForm">
            @csrf
            @method('PUT')
            @include('admin.inventory.partials.form', ['inventory' => $inventory, 'brands' => $brands])
        </form>
        
        <script>
            // Brand search (client-side) - for edit page, brand is disabled but we still need the script
            const brandSearch = document.getElementById('brand_search');
            const brandId = document.getElementById('brand_id');
            
            // Set initial brand value for edit
            brandSearch.value = '{{ $inventory->brand->name }}';
            brandId.value = '{{ $inventory->brand_id }}';
            
            // Form submit loading
            document.getElementById('inventoryForm').addEventListener('submit', function() {
                const btn = document.getElementById('submitBtn');
                btn.disabled = true;
                btn.querySelector('.spinner-border').classList.remove('d-none');
                btn.querySelector('.btn-text').textContent = 'Updating...';
            });
        </script>
    </div>
</div>
@endsection
