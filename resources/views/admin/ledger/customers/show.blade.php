@extends('admin.layout')

@section('title', 'Ledger - ' . $customer->name)

@section('content')
<div id="ledgerSuccessAlert"></div>
@php
    $totalReceived = $customer->total_received;
    $totalGave = $customer->total_gave;
    $balance = $customer->balance;
    $balanceAbs = abs($balance);
@endphp
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-user me-2"></i>{{ $customer->name }}</span>
        <div>
            <button type="button" class="btn btn-sm btn-success me-2" id="btnYouGot" data-bs-toggle="modal" data-bs-target="#modalYouGot">
                <i class="fas fa-hand-holding-usd me-1"></i>You got
            </button>
            <button type="button" class="btn btn-sm btn-danger me-2" id="btnYouGave" data-bs-toggle="modal" data-bs-target="#modalYouGave">
                <i class="fas fa-hand-holding me-1"></i>You gave
            </button>
            <a href="{{ route('admin.ledger.customers.edit', $customer) }}" class="btn btn-sm btn-warning">Edit</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-0" id="ledgerSummary">
            <div class="col-md-4">
                <div class="p-3 rounded bg-success bg-opacity-10 border border-success">
                    <div class="small text-muted">You got (from this customer)</div>
                    <div class="fs-4 fw-bold text-success" id="summaryReceived">{{ format_amount($totalReceived) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded bg-danger bg-opacity-10 border border-danger">
                    <div class="small text-muted">You gave (to this customer)</div>
                    <div class="fs-4 fw-bold text-danger" id="summaryGave">{{ format_amount($totalGave) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded {{ $balance < 0 ? 'bg-success' : 'bg-danger' }} bg-opacity-10 border {{ $balance < 0 ? 'border-success' : 'border-danger' }}">
                    <div class="small text-muted">Balance</div>
                    <div class="fs-4 fw-bold {{ $balance < 0 ? 'text-success' : 'text-danger' }}" id="summaryBalance">{{ format_amount($balanceAbs) }}</div>
                    <small class="text-muted" id="summaryBalanceLabel">{{ $balance < 0 ? 'They owe you' : 'You owe them' }}</small>
                </div>
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
                    <input type="text" class="form-control" id="modalYouGotDesc" placeholder="Optional">
                </div>
                <div class="mb-3">
                    <label for="modalYouGotDate" class="form-label">Date</label>
                    <input type="date" class="form-control" id="modalYouGotDate" value="{{ date('Y-m-d') }}">
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
                    <input type="text" class="form-control" id="modalYouGaveDesc" placeholder="Optional">
                </div>
                <div class="mb-3">
                    <label for="modalYouGaveDate" class="form-label">Date</label>
                    <input type="date" class="form-control" id="modalYouGaveDate" value="{{ date('Y-m-d') }}">
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
                    <input type="text" class="form-control" id="editTxDesc">
                </div>
                <div class="mb-3">
                    <label for="editTxDate" class="form-label">Date</label>
                    <input type="date" class="form-control" id="editTxDate">
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
    <div class="card-header"><i class="fas fa-history me-2"></i>History</div>
    <div class="card-body">
        <div id="ledgerHistoryContainer">
            @include('admin.ledger.customers.partials.history', ['transactions' => $transactions, 'customer' => $customer])
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(function() {
    const customerId = {{ $customer->id }};
    const showUrl = "{{ route('admin.ledger.customers.show', $customer) }}";
    const storeUrl = "{{ route('admin.ledger.customers.transactions.store', $customer) }}";
    const csrf = "{{ csrf_token() }}";
    const $historyContainer = $('#ledgerHistoryContainer');

    function formatAmt(n) {
        if (n == null || n === '') return '0';
        n = parseFloat(n);
        if (Number.isInteger(n)) return n;
        return n;
    }

    function updateSummary(totals) {
        if (!totals) return;
        $('#summaryReceived').text(formatAmt(totals.total_received));
        $('#summaryGave').text(formatAmt(totals.total_gave));
        const bal = parseFloat(totals.balance) || 0;
        const abs = Math.abs(bal);
        $('#summaryBalance').text(formatAmt(abs));
        $('#summaryBalanceLabel').text(bal < 0 ? 'They owe you' : 'You owe them');
        $('#ledgerSummary .col-md-4:last .p-3').removeClass('bg-success bg-danger bg-opacity-10 border-success border-danger')
            .addClass(bal < 0 ? 'bg-success bg-opacity-10 border border-success' : 'bg-danger bg-opacity-10 border border-danger');
        $('#summaryBalance').removeClass('text-success text-danger').addClass(bal < 0 ? 'text-success' : 'text-danger');
    }

    function loadHistory(page) {
        page = page || 1;
        $historyContainer.html('<div class="text-center p-4"><div class="spinner-border text-primary" role="status"></div></div>');
        $.get(showUrl, { tx_page: page }, function(html) {
            $historyContainer.html(html);
        }).fail(function() {
            $historyContainer.html('<div class="alert alert-danger">Error loading history</div>');
        });
    }

    $historyContainer.on('click', 'a[href*="tx_page"]', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        const match = url && url.match(/tx_page=(\d+)/);
        if (match) loadHistory(parseInt(match[1], 10));
    });

    function showSuccessMessage(msg) {
        const html = '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
            '<i class="fas fa-check-circle me-2"></i>' + msg +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        $('#ledgerSuccessAlert').html(html);
        setTimeout(function() {
            $('#ledgerSuccessAlert .alert').fadeOut(300, function() { $(this).remove(); });
        }, 2000);
    }

    function submitAdd(type) {
        const isGot = (type === 'received');
        const amount = parseFloat(isGot ? $('#modalYouGotAmount').val() : $('#modalYouGaveAmount').val()) || 0;
        const date = (isGot ? $('#modalYouGotDate').val() : $('#modalYouGaveDate').val()) || '';
        const desc = (isGot ? $('#modalYouGotDesc').val() : $('#modalYouGaveDesc').val()) || '';
        if (amount <= 0) { alert('Enter a valid amount.'); return; }
        const $btn = isGot ? $('#submitYouGot') : $('#submitYouGave');
        $btn.prop('disabled', true);
        $.ajax({
            url: storeUrl,
            method: 'POST',
            data: { _token: csrf, type: type, amount: amount, transaction_date: date, description: desc },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    bootstrap.Modal.getInstance(document.getElementById(isGot ? 'modalYouGot' : 'modalYouGave')).hide();
                    if (isGot) { $('#modalYouGotAmount, #modalYouGotDesc').val(''); $('#modalYouGotDate').val('{{ date("Y-m-d") }}'); }
                    else { $('#modalYouGaveAmount, #modalYouGaveDesc').val(''); $('#modalYouGaveDate').val('{{ date("Y-m-d") }}'); }
                    updateSummary(res.totals);
                    loadHistory(1);
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

    $('#modalYouGot').on('show.bs.modal', function() { $('#submitYouGot').prop('disabled', false); });
    $('#modalYouGave').on('show.bs.modal', function() { $('#submitYouGave').prop('disabled', false); });

    $('#submitEditTx').on('click', function() {
        const id = $('#editTxId').val();
        const amount = parseFloat($('#editTxAmount').val()) || 0;
        const date = $('#editTxDate').val();
        const desc = $('#editTxDesc').val();
        if (amount <= 0) { alert('Enter a valid amount.'); return; }
        const $btn = $('#submitEditTx').prop('disabled', true);
        const updateUrl = "{{ url('admin/ledger/customers') }}/" + customerId + "/transactions/" + id;
        $.ajax({
            url: updateUrl,
            method: 'POST',
            data: { _token: csrf, _method: 'PUT', amount: amount, transaction_date: date, description: desc },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modalEditTx')).hide();
                    updateSummary(res.totals);
                    loadHistory(1);
                    showSuccessMessage(res.message);
                }
            },
            error: function(xhr) {
                var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error';
                alert(msg);
            },
            complete: function() { $btn.prop('disabled', false); }
        });
    });

    $(document).on('click', '.btn-delete-tx', function() {
        const id = $(this).data('id');
        if (!confirm('Delete this entry?')) return;
        const deleteUrl = "{{ url('admin/ledger/customers') }}/" + customerId + "/transactions/" + id;
        $.ajax({
            url: deleteUrl,
            method: 'POST',
            data: { _token: csrf, _method: 'DELETE' },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    updateSummary(res.totals);
                    loadHistory(1);
                    showSuccessMessage(res.message);
                }
            },
            error: function() { alert('Error deleting'); }
        });
    });

    $(document).on('click', '.btn-edit-tx', function() {
        const id = $(this).data('id'), amount = $(this).data('amount'), date = $(this).data('date');
        const desc = $(this).attr('data-desc');
        $('#editTxId').val(id);
        $('#editTxAmount').val(amount);
        $('#editTxDate').val(date);
        $('#editTxDesc').val(desc !== undefined ? desc : '');
        var modal = new bootstrap.Modal(document.getElementById('modalEditTx'));
        modal.show();
    });
});
</script>
@endsection
