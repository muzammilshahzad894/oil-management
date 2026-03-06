@extends('admin.layout')

@section('title', 'Ledger - ' . $customer->name)

@section('content')
<div id="ledgerSuccessAlert"></div>
@php
    $balance = $customer->balance;
    $balanceAbs = abs($balance);
@endphp
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center ledger-detail-header">
        <span class="ledger-detail-name"><i class="fas fa-user me-2"></i>{{ $customer->name }}</span>
        <div class="ledger-detail-actions">
            <button type="button" class="btn btn-sm btn-success" id="btnYouGot" data-bs-toggle="modal" data-bs-target="#modalYouGot">
                <i class="fas fa-hand-holding-usd me-1"></i>You got
            </button>
            <button type="button" class="btn btn-sm btn-danger" id="btnYouGave" data-bs-toggle="modal" data-bs-target="#modalYouGave">
                <i class="fas fa-hand-holding me-1"></i>You gave
            </button>
            <a href="{{ route('admin.ledger.customers.edit', $customer) }}" class="btn btn-sm btn-warning"><i class="fas fa-user-edit me-1"></i>Edit name</a>
        </div>
    </div>
    <div class="card-body">
        <div id="ledgerBalanceCard" class="ledger-detail-balance-card {{ $balance > 0 ? 'ledger-balance-give' : ($balance < 0 ? 'ledger-balance-get' : 'ledger-balance-zero') }}">
            <div class="ledger-detail-balance-inner">
                <span id="ledgerMainAmount" class="ledger-detail-balance-figure">Rs {{ number_format($balanceAbs, 0) }}</span>
                @if($balance != 0)
                <span id="ledgerMainLabel" class="ledger-detail-balance-label">
                    @if($balance > 0)
                        You will give
                    @else
                        You will get
                    @endif
                </span>
                @else
                <span id="ledgerMainLabel" class="ledger-detail-balance-label d-none"></span>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Modal: You got --}}
<div class="modal fade" id="modalYouGot" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">You got (customer gave you)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modalYouGotType" value="received">
                <div class="mb-3">
                    <label for="modalYouGotAmount" class="form-label">Amount <span class="text-danger">*</span></label>
                    <input type="number" step="any" min="0.01" class="form-control" id="modalYouGotAmount" required>
                </div>
                <div class="mb-3">
                    <label for="modalYouGotDesc" class="form-label">Description</label>
                    <input type="text" class="form-control" id="modalYouGotDesc" placeholder="">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label for="modalYouGotDateOnly" class="form-label">Date</label>
                        <input type="date" class="form-control ledger-date-input" id="modalYouGotDateOnly">
                    </div>
                    <div class="col-6">
                        <label for="modalYouGotTimeOnly" class="form-label">Time</label>
                        <input type="time" class="form-control ledger-time-input" id="modalYouGotTimeOnly" step="60">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="submitYouGot">Save</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal: You gave --}}
<div class="modal fade" id="modalYouGave" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">You gave (you gave to customer)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modalYouGaveType" value="gave">
                <div class="mb-3">
                    <label for="modalYouGaveAmount" class="form-label">Amount <span class="text-danger">*</span></label>
                    <input type="number" step="any" min="0.01" class="form-control" id="modalYouGaveAmount" required>
                </div>
                <div class="mb-3">
                    <label for="modalYouGaveDesc" class="form-label">Description</label>
                    <input type="text" class="form-control" id="modalYouGaveDesc" placeholder="">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label for="modalYouGaveDateOnly" class="form-label">Date</label>
                        <input type="date" class="form-control ledger-date-input" id="modalYouGaveDateOnly">
                    </div>
                    <div class="col-6">
                        <label for="modalYouGaveTimeOnly" class="form-label">Time</label>
                        <input type="time" class="form-control ledger-time-input" id="modalYouGaveTimeOnly" step="60">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="submitYouGave">Save</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Edit transaction --}}
<div class="modal fade" id="modalEditTx" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editTxId">
                <div class="mb-3">
                    <label for="editTxAmount" class="form-label">Amount <span class="text-danger">*</span></label>
                    <input type="number" step="any" min="0.01" class="form-control" id="editTxAmount" required>
                </div>
                <div class="mb-3">
                    <label for="editTxDesc" class="form-label">Description</label>
                    <input type="text" class="form-control" id="editTxDesc" placeholder="">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label for="editTxDateOnly" class="form-label">Date</label>
                        <input type="date" class="form-control ledger-date-input" id="editTxDateOnly">
                    </div>
                    <div class="col-6">
                        <label for="editTxTimeOnly" class="form-label">Time</label>
                        <input type="time" class="form-control ledger-time-input" id="editTxTimeOnly" step="60">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitEditTx">Update</button>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center gap-2 ledger-history-header">
        <span class="ledger-history-title"><i class="fas fa-history me-2"></i>History</span>
        <div class="ms-auto ledger-history-search-wrap">
            <i class="fas fa-search ledger-history-search-icon"></i>
            <input type="text" class="form-control form-control-sm ledger-history-search-input" id="ledgerHistorySearch" placeholder="Search by amount or description..." value="{{ request('history_search') }}" autocomplete="off">
        </div>
    </div>
    <div class="card-body ledger-history-scroll-wrap">
        <div id="ledgerHistoryContainer">
            @include('admin.ledger.customers.partials.history', ['transactions' => $transactions, 'customer' => $customer])
        </div>
    </div>
</div>

{{-- Delete transaction confirmation modal --}}
<div class="modal fade" id="modalDeleteTx" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">Delete entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-0">
                <p class="mb-0">Delete this history entry? This cannot be undone.</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="modalDeleteTxConfirm"><i class="fas fa-trash me-1"></i>Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.ledger-detail-balance-card {
    border-radius: 12px;
    padding: 1.5rem 1.75rem;
    border: 1px solid transparent;
}
.ledger-balance-give { background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border-color: #a7f3d0; }
.ledger-balance-get { background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); border-color: #fecaca; }
.ledger-balance-zero { background: #f9fafb; border-color: #e5e7eb; }
.ledger-detail-balance-figure { display: block; font-size: 1.75rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.25rem; }
.ledger-detail-balance-label { display: block; font-size: 1rem; font-weight: 600; }
.ledger-balance-give .ledger-detail-balance-figure { color: #047857; }
.ledger-balance-give .ledger-detail-balance-label { color: #047857; }
.ledger-balance-get .ledger-detail-balance-figure { color: #b91c1c; }
.ledger-balance-get .ledger-detail-balance-label { color: #b91c1c; }
.ledger-balance-zero .ledger-detail-balance-figure, .ledger-balance-zero .ledger-detail-balance-label { color: #6b7280; }
.ledger-date-input, .ledger-time-input { cursor: pointer; min-height: 42px; padding: 0.5rem 0.75rem; }
.ledger-time-input { min-width: 120px; position: relative; overflow: visible; }
/* Whole time field opens picker: stretch invisible indicator over input */
.ledger-time-input::-webkit-calendar-picker-indicator {
    position: absolute;
    left: 0;
    top: 0;
    right: 0;
    bottom: 0;
    width: 100%;
    height: 100%;
    margin: 0;
    padding: 0;
    opacity: 0;
    cursor: pointer;
}

.ledger-history-search-wrap {
    position: relative;
    max-width: 280px;
}
.ledger-history-search-icon {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 0.9rem;
    pointer-events: none;
}
.ledger-history-search-input {
    padding-left: 32px !important;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}
.ledger-history-scroll-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
.ledger-history-table { min-width: 640px; }

/* Detail page header: stack name + buttons on mobile with better tap targets */
.ledger-detail-header { flex-wrap: wrap; gap: 0.75rem; }
.ledger-detail-header .ledger-detail-name { font-weight: 600; }
.ledger-detail-header .btn { white-space: nowrap; }

/* History card: on mobile put search below "History" and full width */
.ledger-history-header { flex-wrap: wrap; }
.ledger-history-header .ledger-history-title { flex: 0 0 auto; }
.ledger-history-header .ledger-history-search-wrap { flex: 1 1 auto; min-width: 0; }

@media (max-width: 768px) {
    .ledger-detail-header { flex-direction: column; align-items: stretch; text-align: center; }
    .ledger-detail-header .ledger-detail-name { order: 1; padding-bottom: 0.25rem; }
    .ledger-detail-header .ledger-detail-actions { order: 2; display: flex; flex-wrap: wrap; justify-content: center; gap: 0.5rem; }
    .ledger-detail-header .ledger-detail-actions .btn { flex: 1 1 auto; min-width: 100px; }

    .ledger-history-header { flex-direction: column; align-items: stretch; }
    .ledger-history-title { width: 100%; margin-bottom: 0.5rem; }
    .ledger-history-search-wrap { width: 100%; max-width: 100%; }
    .ledger-history-search-input { width: 100% !important; }
}
</style>
@endsection

@section('scripts')
<script>
$(function() {
    const customerId = {{ $customer->id }};
    const showUrl = "{{ route('admin.ledger.customers.show', $customer) }}";
    const storeUrl = "{{ route('admin.ledger.customers.transactions.store', $customer) }}";
    const csrf = "{{ csrf_token() }}";
    function getLocalDateStr() {
        const d = new Date();
        return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    }
    function getLocalTimeStr() {
        const d = new Date();
        return String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
    }
    const $historyContainer = $('#ledgerHistoryContainer');

    function formatAmt(n) {
        if (n == null || n === '') return '0';
        n = parseFloat(n);
        return Number.isInteger(n) ? n : n;
    }

    function getGotDatetime() {
        const d = $('#modalYouGotDateOnly').val() || getLocalDateStr();
        const t = $('#modalYouGotTimeOnly').val() || getLocalTimeStr();
        return d + ' ' + t + ':00';
    }
    function getGaveDatetime() {
        const d = $('#modalYouGaveDateOnly').val() || getLocalDateStr();
        const t = $('#modalYouGaveTimeOnly').val() || getLocalTimeStr();
        return d + ' ' + t + ':00';
    }
    function setGotDatetime(val) {
        $('#modalYouGotDateOnly').val(getLocalDateStr());
        $('#modalYouGotTimeOnly').val(getLocalTimeStr());
        if (val) {
            const m = val.match(/^(\d{4}-\d{2}-\d{2})[\sT](\d{1,2}):(\d{2})/);
            if (m) {
                $('#modalYouGotDateOnly').val(m[1]);
                $('#modalYouGotTimeOnly').val(m[2].padStart(2,'0') + ':' + m[3]);
            }
        }
    }
    function setGaveDatetime(val) {
        $('#modalYouGaveDateOnly').val(getLocalDateStr());
        $('#modalYouGaveTimeOnly').val(getLocalTimeStr());
        if (val) {
            const m = val.match(/^(\d{4}-\d{2}-\d{2})[\sT](\d{1,2}):(\d{2})/);
            if (m) {
                $('#modalYouGaveDateOnly').val(m[1]);
                $('#modalYouGaveTimeOnly').val(m[2].padStart(2,'0') + ':' + m[3]);
            }
        }
    }

    function updateBalanceCard(totals) {
        if (!totals) return;
        const bal = parseFloat(totals.balance) || 0;
        const abs = Math.abs(bal);
        const $card = $('#ledgerBalanceCard');
        const $figure = $('#ledgerMainAmount');
        const $label = $('#ledgerMainLabel');
        $card.removeClass('ledger-balance-give ledger-balance-get ledger-balance-zero');
        if (bal > 0) {
            $card.addClass('ledger-balance-give');
            $figure.text('Rs ' + formatAmt(abs));
            $label.removeClass('d-none').text('You will give');
        } else if (bal < 0) {
            $card.addClass('ledger-balance-get');
            $figure.text('Rs ' + formatAmt(abs));
            $label.removeClass('d-none').text('You will get');
        } else {
            $card.addClass('ledger-balance-zero');
            $figure.text('Rs 0');
            $label.addClass('d-none').text('');
        }
    }

    function loadHistory(page, search) {
        page = page || 1;
        const params = { tx_page: page };
        if (search) params.history_search = search;
        $historyContainer.html('<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary" role="status"></div></div>');
        $.get(showUrl, params, function(html) {
            $historyContainer.html(html);
        }).fail(function() {
            $historyContainer.html('<div class="alert alert-danger">Error loading history</div>');
        });
    }

    var historySearchDebounce;
    $('#ledgerHistorySearch').on('input', function() {
        clearTimeout(historySearchDebounce);
        var q = $(this).val().trim();
        historySearchDebounce = setTimeout(function() {
            loadHistory(1, q || undefined);
        }, 400);
    });

    $historyContainer.on('click', 'a[href*="tx_page"]', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        const match = url && url.match(/tx_page=(\d+)/);
        const search = $('#ledgerHistorySearch').val().trim();
        if (match) loadHistory(parseInt(match[1], 10), search || undefined);
    });

    function showSuccessMessage(msg) {
        $('#ledgerSuccessAlert').html('<div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i>' + msg + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
        setTimeout(function() { $('#ledgerSuccessAlert .alert').fadeOut(300, function() { $(this).remove(); }); }, 2500);
    }

    function submitAdd(type) {
        const isGot = (type === 'received');
        const amount = parseFloat(isGot ? $('#modalYouGotAmount').val() : $('#modalYouGaveAmount').val()) || 0;
        const dateVal = isGot ? getGotDatetime() : getGaveDatetime();
        const desc = (isGot ? $('#modalYouGotDesc').val() : $('#modalYouGaveDesc').val()) || '';
        if (amount <= 0) { alert('Enter a valid amount.'); return; }
        const $btn = isGot ? $('#submitYouGot') : $('#submitYouGave');
        $btn.prop('disabled', true);
        $.ajax({
            url: storeUrl,
            method: 'POST',
            data: { _token: csrf, type: type, amount: amount, transaction_date: dateVal, description: desc },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    bootstrap.Modal.getInstance(document.getElementById(isGot ? 'modalYouGot' : 'modalYouGave')).hide();
                    if (isGot) { $('#modalYouGotAmount, #modalYouGotDesc').val(''); setGotDatetime(null); }
                    else { $('#modalYouGaveAmount, #modalYouGaveDesc').val(''); setGaveDatetime(null); }
                    updateBalanceCard(res.totals);
                    loadHistory(1, $('#ledgerHistorySearch').val().trim() || undefined);
                    showSuccessMessage(res.message);
                }
            },
            error: function(xhr) {
                var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : (xhr.responseJSON && xhr.responseJSON.errors ? JSON.stringify(xhr.responseJSON.errors) : 'Error');
                alert(msg);
            },
            complete: function() { $btn.prop('disabled', false); }
        });
    }

    $('#submitYouGot').on('click', function() { submitAdd('received'); });
    $('#submitYouGave').on('click', function() { submitAdd('gave'); });

    $('#modalYouGot').on('show.bs.modal', function() {
        $('#submitYouGot').prop('disabled', false);
        setGotDatetime(null);
        $('#modalYouGotDateOnly').val(getLocalDateStr());
        $('#modalYouGotTimeOnly').val(getLocalTimeStr());
    });
    $('#modalYouGave').on('show.bs.modal', function() {
        $('#submitYouGave').prop('disabled', false);
        setGaveDatetime(null);
        $('#modalYouGaveDateOnly').val(getLocalDateStr());
        $('#modalYouGaveTimeOnly').val(getLocalTimeStr());
    });

    $('#submitEditTx').on('click', function() {
        const id = $('#editTxId').val();
        const amount = parseFloat($('#editTxAmount').val()) || 0;
        const dateVal = $('#editTxDateOnly').val() + ' ' + ($('#editTxTimeOnly').val() || '00:00') + ':00';
        const desc = $('#editTxDesc').val();
        if (amount <= 0) { alert('Enter a valid amount.'); return; }
        $('#submitEditTx').prop('disabled', true);
        const updateUrl = "{{ url('admin/ledger/customers') }}/" + customerId + "/transactions/" + id;
        $.ajax({
            url: updateUrl,
            method: 'POST',
            data: { _token: csrf, _method: 'PUT', amount: amount, transaction_date: dateVal, description: desc },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modalEditTx')).hide();
                    updateBalanceCard(res.totals);
                    loadHistory(1, $('#ledgerHistorySearch').val().trim() || undefined);
                    showSuccessMessage(res.message);
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error');
            },
            complete: function() { $('#submitEditTx').prop('disabled', false); }
        });
    });

    var deleteTxId = null;
    $(document).on('click', '.btn-delete-tx', function(e) {
        e.preventDefault();
        deleteTxId = $(this).data('id');
        (new bootstrap.Modal(document.getElementById('modalDeleteTx'))).show();
    });
    $('#modalDeleteTxConfirm').on('click', function() {
        if (!deleteTxId) return;
        var deleteUrl = "{{ url('admin/ledger/customers') }}/" + customerId + "/transactions/" + deleteTxId;
        var $btn = $(this);
        $btn.prop('disabled', true);
        $.ajax({
            url: deleteUrl,
            method: 'POST',
            data: { _token: csrf, _method: 'DELETE' },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modalDeleteTx')).hide();
                    updateBalanceCard(res.totals);
                    loadHistory(1, $('#ledgerHistorySearch').val().trim() || undefined);
                    showSuccessMessage(res.message);
                }
            },
            error: function() { alert('Error deleting'); },
            complete: function() { $btn.prop('disabled', false); deleteTxId = null; }
        });
    });

    $(document).on('click', '.btn-edit-tx', function() {
        const id = $(this).data('id'), amount = $(this).data('amount');
        const dateVal = $(this).data('date');
        const desc = $(this).attr('data-desc');
        $('#editTxId').val(id);
        $('#editTxAmount').val(amount);
        $('#editTxDesc').val(desc !== undefined ? desc : '');
        if (dateVal) {
            const m = dateVal.match(/^(\d{4}-\d{2}-\d{2})[\sT](\d{1,2}):(\d{2})/);
            if (m) {
                $('#editTxDateOnly').val(m[1]);
                $('#editTxTimeOnly').val(m[2].padStart(2,'0') + ':' + m[3]);
            }
        } else {
            $('#editTxDateOnly').val(getLocalDateStr());
            $('#editTxTimeOnly').val(getLocalTimeStr());
        }
        (new bootstrap.Modal(document.getElementById('modalEditTx'))).show();
    });
});
</script>
@endsection
