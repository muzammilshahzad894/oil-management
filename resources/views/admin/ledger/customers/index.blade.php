@extends('admin.layout')

@section('title', 'Ledger - Customers')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="fas fa-book me-2"></i>Ledger Customers</span>
        <a href="{{ route('admin.ledger.customers.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-2"></i>Add Customer
        </a>
    </div>
    <div class="card-body">
        <div class="mb-4">
            <input type="text" id="ledgerCustomerSearch" class="form-control" placeholder="Search by name, phone or address..." value="{{ request('search') }}" autocomplete="off">
        </div>
        <div id="ledgerTableContainer">
            @include('admin.ledger.customers.partials.table', ['customers' => $customers])
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(function() {
    let searchTimeout;
    const $searchInput = $('#ledgerCustomerSearch');
    const $container = $('#ledgerTableContainer');
    const indexUrl = "{{ route('admin.ledger.customers.index') }}";

    $searchInput.on('input', function() {
        clearTimeout(searchTimeout);
        const search = $(this).val().trim();

        searchTimeout = setTimeout(function() {
            if (search.length === 0) {
                window.location.href = indexUrl;
                return;
            }
            $container.html('<div class="text-center p-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Searching...</p></div>');
            $.ajax({
                url: indexUrl,
                method: 'GET',
                data: { search: search },
                success: function(html) {
                    const $html = $(html);
                    const $newContent = $html.find('#ledgerTableContainer');
                    if ($newContent.length) {
                        $container.html($newContent.html());
                    }
                },
                error: function() {
                    $container.html('<div class="alert alert-danger">Error loading results</div>');
                }
            });
        }, 400);
    });
});
</script>
@endsection
