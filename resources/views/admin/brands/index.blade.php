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
    let searchTimeout;
    const searchInput = document.getElementById('brandSearch');
    const tableContainer = document.getElementById('tableContainer');
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const search = this.value.trim();
        
        searchTimeout = setTimeout(() => {
            if (search.length === 0) {
                window.location.href = '{{ route("admin.brands.index") }}';
                return;
            }
            
            tableContainer.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Searching...</p></div>';
            
            fetch('{{ route("admin.brands.index") }}?search=' + encodeURIComponent(search))
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newTable = doc.getElementById('tableContainer');
                    if (newTable) {
                        tableContainer.innerHTML = newTable.innerHTML;
                    }
                })
                .catch(error => {
                    tableContainer.innerHTML = '<div class="alert alert-danger">Error loading results</div>';
                });
        }, 500);
    });
    
    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Are you sure?',
            html: `<p>You want to delete brand <strong>${name}</strong>?</p><p class="text-danger"><small>This action cannot be undone.</small></p>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }
</script>
@endsection
