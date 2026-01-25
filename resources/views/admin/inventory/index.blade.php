@extends('admin.layout')

@section('title', 'Inventory')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-warehouse me-2"></i>Inventory</span>
        <a href="{{ route('admin.inventory.create') }}" class="btn btn-light btn-sm">
            <i class="fas fa-plus me-2"></i>Add Stock
        </a>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <input type="text" 
                   id="inventorySearch" 
                   class="form-control" 
                   placeholder="Search by brand name..." 
                   value="{{ request('search') }}"
                   autocomplete="off">
        </div>
        
        <div id="tableContainer">
            @include('admin.inventory.partials.table', ['inventory' => $inventory])
        </div>
    </div>
</div>

<!-- Add Stock Modal (Single Modal for All Items) -->
<div class="modal fade" id="addStockModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="addStockForm">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStockModalTitle">Add Stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="addStockQuantity" class="form-label">Quantity to Add</label>
                        <input type="number" class="form-control" id="addStockQuantity" name="quantity" min="1" required>
                        <small class="text-muted" id="currentStockInfo">Current stock: 0</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="addStockSubmitBtn">
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        <span class="btn-text">Add Stock</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        let searchTimeout;
        const $searchInput = $('#inventorySearch');
        const $tableContainer = $('#tableContainer');
        
        $searchInput.on('input', function() {
            clearTimeout(searchTimeout);
            const search = $(this).val().trim();
            
            searchTimeout = setTimeout(function() {
                if (search.length === 0) {
                    window.location.href = '{{ route("admin.inventory.index") }}';
                    return;
                }
                
                $tableContainer.html('<div class="text-center p-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Searching...</p></div>');
                
                $.ajax({
                    url: '{{ route("admin.inventory.index") }}',
                    method: 'GET',
                    data: { search: search },
                    success: function(html) {
                        const $html = $(html);
                        const $newTable = $html.find('#tableContainer');
                        if ($newTable.length) {
                            $tableContainer.html($newTable.html());
                        }
                    },
                    error: function() {
                        $tableContainer.html('<div class="alert alert-danger">Error loading results</div>');
                    }
                });
            }, 500);
        });
        
        // Handle form submission - use event delegation to ensure it works after AJAX updates
        $(document).on('submit', '#addStockForm', function(e) {
            const $form = $(this);
            const $submitBtn = $('#addStockSubmitBtn');
            const itemId = $form.data('item-id');
            const actionUrl = $form.attr('action');
            
            // Verify the form action is set correctly
            if (!actionUrl || actionUrl === '' || !itemId) {
                e.preventDefault();
                alert('Error: Form action not set correctly. Please try again.');
                return false;
            }
            
            $submitBtn.prop('disabled', true);
            $submitBtn.find('.spinner-border').removeClass('d-none');
            $submitBtn.find('.btn-text').text('Adding...');
        });
    });
    
    // Function to show add stock modal with item data
    function showAddStockModal(itemId, brandName, currentStock) {
        // Update modal title
        $('#addStockModalTitle').text('Add Stock - ' + brandName);
        
        // Update current stock info
        $('#currentStockInfo').text('Current stock: ' + currentStock);
        
        // Update form action URL - IMPORTANT: Use full URL to ensure correct routing
        const $form = $('#addStockForm');
        const baseUrl = '{{ url("/admin/inventory") }}';
        const actionUrl = baseUrl + '/' + itemId + '/add-stock';
        
        // Store the item ID for verification
        $form.data('item-id', itemId);
        
        // Update the form action
        $form.attr('action', actionUrl);
        
        // Clear form
        $form[0].reset();
        $('#addStockQuantity').val('').focus();
        
        // Reset submit button
        const $submitBtn = $('#addStockSubmitBtn');
        $submitBtn.prop('disabled', false);
        $submitBtn.find('.spinner-border').addClass('d-none');
        $submitBtn.find('.btn-text').text('Add Stock');
        
        // Show modal using jQuery/Bootstrap
        const $modal = $('#addStockModal');
        const modalElement = $modal[0];
        const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
        modal.show();
        
        // Ensure form is properly bound after modal shows
        $modal.off('shown.bs.modal').on('shown.bs.modal', function() {
            $('#addStockQuantity').focus();
        });
    }
    
    function confirmDelete(id, brandName) {
        Swal.fire({
            title: 'Are you sure?',
            html: '<p>You want to delete inventory for <strong>' + brandName + '</strong>?</p><p class="text-danger"><small>This action cannot be undone.</small></p>',
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
