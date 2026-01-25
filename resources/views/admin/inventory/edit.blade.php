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
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Set initial brand value for edit
        $('#brand_search').val('{{ $inventory->brand->name }}');
        $('#brand_id').val('{{ $inventory->brand_id }}');
        
        // Form submit loading
        $('#inventoryForm').on('submit', function() {
            const $btn = $('#submitBtn');
            $btn.prop('disabled', true);
            $btn.find('.spinner-border').removeClass('d-none');
            $btn.find('.btn-text').text('Updating...');
        });
    });
</script>
@endsection
