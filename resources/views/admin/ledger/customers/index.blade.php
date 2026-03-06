@extends('admin.layout')

@section('title', 'Ledger - Customers')

@section('content')
@php
    $totalC = (int) ($totalCustomers ?? $customers->total());
    $placeholder = $totalC === 0 ? 'Search customers...' : ($totalC === 1 ? 'Search 1 customer...' : 'Search ' . $totalC . ' customers...');
@endphp
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="ledger-summary-card ledger-summary-give">
            <div class="ledger-summary-icon"><i class="fas fa-arrow-up"></i></div>
            <div class="ledger-summary-content">
                <span class="ledger-summary-title">You will give</span>
                <span class="ledger-summary-desc">Total amount you owe to customers</span>
                <span class="ledger-summary-value">Rs {{ number_format($overallYouWillGive ?? 0, 0) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="ledger-summary-card ledger-summary-get">
            <div class="ledger-summary-icon"><i class="fas fa-arrow-down"></i></div>
            <div class="ledger-summary-content">
                <span class="ledger-summary-title">You will get</span>
                <span class="ledger-summary-desc">Total amount customers owe you</span>
                <span class="ledger-summary-value">Rs {{ number_format($overallYouWillGet ?? 0, 0) }}</span>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="fas fa-book me-2"></i>Ledger Customers</span>
        <a href="{{ route('admin.ledger.customers.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-2"></i>Add Customer
        </a>
    </div>
    <div class="card-body">
        <div class="mb-4">
            <div class="ledger-search-wrap">
                <i class="fas fa-search ledger-search-icon"></i>
                <input type="text" id="ledgerCustomerSearch" class="form-control ledger-search-input" placeholder="{{ $placeholder }}" value="{{ request('search') }}" autocomplete="off">
            </div>
        </div>
        <div class="ledger-list-scroll-wrap">
            <div id="ledgerTableContainer">
                @include('admin.ledger.customers.partials.table', ['customers' => $customers, 'totalCustomers' => $totalC])
            </div>
        </div>
    </div>
</div>

{{-- Delete customer confirmation modal --}}
<div class="modal fade" id="modalDeleteCustomer" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">Remove customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-0">
                <p class="mb-0">Remove <strong id="modalDeleteCustomerName"></strong> from the ledger? This cannot be undone.</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="modalDeleteCustomerConfirm"><i class="fas fa-trash me-1"></i>Remove</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.ledger-summary-card {
    border-radius: 14px;
    padding: 1.25rem 1.5rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    transition: box-shadow 0.2s;
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    border: 1px solid rgba(0,0,0,0.06);
}
.ledger-summary-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.1); }
.ledger-summary-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}
.ledger-summary-give .ledger-summary-icon { background: rgba(34, 197, 94, 0.15); color: #16a34a; }
.ledger-summary-get .ledger-summary-icon { background: rgba(239, 68, 68, 0.15); color: #dc2626; }
.ledger-summary-content { display: flex; flex-direction: column; gap: 0.25rem; min-width: 0; }
.ledger-summary-title { font-weight: 700; font-size: 1rem; color: #1f2937; }
.ledger-summary-desc { font-size: 0.8rem; color: #6b7280; }
.ledger-summary-give .ledger-summary-value { color: #16a34a; font-size: 1.5rem; font-weight: 800; letter-spacing: -0.02em; }
.ledger-summary-get .ledger-summary-value { color: #dc2626; font-size: 1.5rem; font-weight: 800; letter-spacing: -0.02em; }

.ledger-search-wrap {
    position: relative;
    max-width: 400px;
}
.ledger-search-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 0.95rem;
    pointer-events: none;
    z-index: 1;
}
.ledger-search-input {
    padding-left: 42px !important;
    padding-right: 1rem;
    height: 44px;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
}
.ledger-search-input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15);
}

.ledger-customer-row {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    padding: 0.9rem 1.25rem;
    border-radius: 10px;
    border: 1px solid #f3f4f6;
    transition: all 0.2s;
    margin-bottom: 0.5rem;
    background: #fff;
}
.ledger-customer-row:hover { border-color: #e5e7eb; background: #fafafa; }
.ledger-customer-left { display: flex; align-items: center; gap: 1rem; min-width: 0; flex: 1; }
.ledger-customer-avatar {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-weight: 700;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.ledger-customer-info { min-width: 0; }
.ledger-customer-name { font-weight: 600; font-size: 0.95rem; margin-bottom: 0.15rem; }
.ledger-customer-meta { font-size: 0.8rem; color: #6b7280; }
.ledger-customer-center {
    flex-shrink: 0;
    font-weight: 700;
    font-size: 1rem;
    text-align: center;
    min-width: 140px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0;
}
.ledger-center-amount { display: block; font-size: 1rem; }
.ledger-center-label { display: block; font-size: 0.85rem; font-weight: 600; }
.ledger-center-second { margin-top: 0.25rem; }
.ledger-customer-center .sum-give { color: #16a34a; }
.ledger-customer-center .sum-get { color: #dc2626; }
.ledger-customer-center .sum-zero { color: #6b7280; }
.ledger-customer-row { cursor: pointer; }
.ledger-customer-actions { display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0; }
.ledger-customer-actions .btn { white-space: nowrap; }

.ledger-list-scroll-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; margin: 0 -0.25rem; }
.ledger-customer-list { min-width: min(100%, 640px); }
.ledger-customer-row { min-width: 600px; }

@media (max-width: 768px) {
    .ledger-summary-card { padding: 1rem 1.25rem; }
    .ledger-summary-value { font-size: 1.25rem !important; }
    .ledger-search-wrap { max-width: 100%; }
}
</style>
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
            $container.html('<div class="text-center p-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted small">Searching...</p></div>');
            $.ajax({
                url: indexUrl,
                method: 'GET',
                data: { search: search },
                success: function(html) {
                    const $html = $(html);
                    const $newContent = $html.find('#ledgerTableContainer');
                    if ($newContent.length) {
                        $container.html($newContent.html());
                        const total = $container.find('#ledgerTableTotal').data('total');
                        if (typeof total !== 'undefined') {
                            const ph = total === 0 ? 'Search customers...' : (total === 1 ? 'Search 1 customer...' : 'Search ' + total + ' customers...');
                            $searchInput.attr('placeholder', ph);
                        }
                    }
                },
                error: function() {
                    $container.html('<div class="alert alert-danger">Error loading results</div>');
                }
            });
        }, 400);
    });

    $(document).on('click', '.ledger-customer-actions a, .ledger-customer-actions button, .ledger-customer-actions input', function(e) {
        e.stopPropagation();
    });
    $(document).on('click', '.ledger-customer-row', function(e) {
        if ($(e.target).closest('.ledger-customer-actions').length) return;
        const href = $(this).data('ledger-href');
        if (href) window.location.href = href;
    });
    $(document).on('keydown', '.ledger-customer-row', function(e) {
        if (e.which === 13 || e.which === 32) {
            e.preventDefault();
            if ($(e.target).closest('.ledger-customer-actions').length) return;
            const href = $(this).data('ledger-href');
            if (href) window.location.href = href;
        }
    });

    var deleteCustomerFormId = null;
    $(document).on('click', '.btn-delete-customer', function(e) {
        e.stopPropagation();
        deleteCustomerFormId = $(this).data('form-id');
        $('#modalDeleteCustomerName').text($(this).data('name'));
        var modal = new bootstrap.Modal(document.getElementById('modalDeleteCustomer'));
        modal.show();
    });
    $('#modalDeleteCustomerConfirm').on('click', function() {
        if (deleteCustomerFormId && $('#' + deleteCustomerFormId).length) {
            $('#' + deleteCustomerFormId).submit();
        }
        bootstrap.Modal.getInstance(document.getElementById('modalDeleteCustomer')).hide();
    });
});
</script>
@endsection
