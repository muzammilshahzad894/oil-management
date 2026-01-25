@extends('admin.layout')

@section('title', 'Brands')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-tags me-2"></i>Brands</span>
        <a href="{{ route('admin.brands.create') }}" class="btn btn-light btn-sm">
            <i class="fas fa-plus me-2"></i>Add New Brand
        </a>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <input type="text" 
                   id="brandSearch" 
                   class="form-control" 
                   placeholder="Search brands..." 
                   value="{{ request('search') }}"
                   autocomplete="off">
        </div>
        
        <div id="tableContainer">
            @include('admin.brands.partials.table', ['brands' => $brands])
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        let searchTimeout;
        const $searchInput = $('#brandSearch');
        const $tableContainer = $('#tableContainer');
        
        $searchInput.on('input', function() {
            clearTimeout(searchTimeout);
            const search = $(this).val().trim();
            
            searchTimeout = setTimeout(function() {
                if (search.length === 0) {
                    window.location.href = '{{ route("admin.brands.index") }}';
                    return;
                }
                
                $tableContainer.html('<div class="text-center p-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Searching...</p></div>');
                
                $.ajax({
                    url: '{{ route("admin.brands.index") }}',
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
    });
    
    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Are you sure?',
            html: '<p>You want to delete brand <strong>' + name + '</strong>?</p><p class="text-danger"><small>This action cannot be undone.</small></p>',
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
