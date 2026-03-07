@extends('admin.layout')

@section('title', 'Sale Details')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-shopping-cart me-2"></i>Sale #{{ $sale->id }}</span>
        <div>
            <a href="{{ route('admin.sales.receipt', $sale->id) }}" target="_blank" class="btn btn-sm btn-success me-2">Get Receipt</a>
            <a href="{{ route('admin.sales.edit', $sale->id) }}" class="btn btn-sm btn-warning">Edit Sale</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <p><strong>Customer:</strong>
                    {{ $sale->customer->name }}
                    @if($sale->customer->trashed())
                        <span class="badge bg-danger ms-2" title="This customer has been deleted">
                            <i class="fas fa-trash"></i> Deleted
                        </span>
                    @endif
                </p>
                <p><strong>Brand:</strong>
                    {{ $sale->brand->name }}
                    @if($sale->brand->trashed())
                        <span class="badge bg-danger ms-2" title="This brand has been deleted">
                            <i class="fas fa-trash"></i> Deleted
                        </span>
                    @endif
                </p>
                <p><strong>Quantity:</strong> {{ $sale->quantity }}</p>
                <p><strong>Sale Date:</strong> {{ $sale->sale_date->format('M d, Y') }}</p>
            </div>
            <div class="col-md-4">
                <p><strong>Total Amount:</strong> {{ format_amount($sale->price) }}</p>
                <p><strong>Total Paid:</strong> {{ format_amount($sale->total_paid) }}</p>
                <p><strong>Balance Due:</strong> {{ format_amount($sale->balance_due) }}</p>
                <p><strong>Payment Status:</strong>
                    @if($sale->total_paid >= (float) $sale->price)
                        <span class="badge bg-success">Paid</span>
                    @elseif($sale->total_paid > 0)
                        <span class="badge bg-warning text-dark">Partial</span>
                    @else
                        <span class="badge bg-danger">Unpaid</span>
                    @endif
                </p>
            </div>
            <div class="col-md-4">
                @if(($showPurchasePrice ?? true) && $sale->cost_at_sale !== null)
                    <p><strong>Purchase price (per unit):</strong> {{ format_amount($sale->cost_at_sale) }}</p>
                    <p><strong>Profit:</strong> {{ format_amount($sale->profit) }}</p>
                @endif
                <p><strong>Notes:</strong> {{ $sale->notes ?? '—' }}</p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-money-bill-wave me-2"></i>Payments</span>
    </div>
    <div class="card-body">
        @if($sale->balance_due <= 0)
        <p class="text-muted small mb-2"><i class="fas fa-info-circle me-1"></i>This sale is fully paid. Any amount you add below will be added to the customer's wallet.</p>
        @endif
        <form action="{{ route('admin.sales.payments.store', $sale->id) }}" method="POST" class="row g-3 mb-4 p-3 bg-light rounded" id="addPaymentForm" data-balance-due="{{ $sale->balance_due }}">
            @csrf
            <div class="col-md-2">
                <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                <input type="number" step="any" min="0" class="form-control @error('amount') is-invalid @enderror" id="payment_amount" name="amount" value="{{ old('amount') }}" placeholder="0" required>
                @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-2">
                <label for="payment_date" class="form-label">Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control @error('payment_date') is-invalid @enderror" id="payment_date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" required>
                @error('payment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-2">
                <label for="method" class="form-label">Method</label>
                <select class="form-select" id="method" name="method">
                    @foreach(\App\Models\Payment::methods() as $value => $label)
                        <option value="{{ $value }}" {{ old('method', 'cash') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="notes" class="form-label">Notes</label>
                <input type="text" class="form-control" id="notes" name="notes" value="{{ old('notes') }}" placeholder="Optional">
            </div>
            <div class="col-12 col-md-4 d-flex align-items-end gap-2 flex-nowrap">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add Payment
                </button>
                @if(optional($sale->customer)->extra_paid_balance > 0)
                <button type="button" class="btn btn-outline-success" id="btnGetFromExtraPaid" title="Get from extra paid" data-sale-id="{{ $sale->id }}" data-balance-due="{{ $sale->balance_due }}">
                    <i class="fas fa-wallet"></i>
                </button>
                @endif
            </div>
        </form>

        @if($sale->payments->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->payments->sortByDesc('payment_date') as $payment)
                            <tr>
                                <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                                <td>{{ format_amount($payment->amount) }}</td>
                                <td>{{ \App\Models\Payment::methods()[$payment->method] ?? $payment->method }}</td>
                                <td>{{ $payment->notes ?? '—' }}</td>
                                <td>
                                    <form id="delete-payment-form-{{ $payment->id }}" action="{{ route('admin.sales.payments.destroy', [$sale->id, $payment->id]) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeletePayment({{ $payment->id }}, '{{ addslashes(format_amount($payment->amount)) }}', '{{ $payment->payment_date->format('M d, Y') }}', '{{ addslashes(\App\Models\Payment::methods()[$payment->method] ?? $payment->method) }}')">Remove</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted mb-0">No payments yet. Use the form above to add a payment.</p>
        @endif
    </div>
</div>

<div class="mb-3">
    <a href="{{ route('admin.sales.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Sales
    </a>
</div>

{{-- Modal: Get from Extra Paid (for this sale) --}}
<div class="modal fade" id="getFromExtraPaidModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-wallet me-2"></i>Get from extra paid</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Balance due on this sale: <strong id="getExtraBalanceDue">—</strong></p>
                <p class="mb-3">Customer extra paid balance: <strong id="getExtraAvailable">—</strong></p>
                <form action="{{ route('admin.sales.payments.from-extra-paid', $sale->id) }}" method="POST" id="getFromExtraPaidForm">
                    @csrf
                    <div class="mb-3">
                        <label for="getExtraAmount" class="form-label">Amount to use <span class="text-danger">*</span></label>
                        <input type="number" step="any" min="0.01" class="form-control" id="getExtraAmount" name="amount" required>
                        <div class="form-text">Max: balance due or extra paid balance, whichever is lower.</div>
                    </div>
                    <div class="mb-3">
                        <label for="getExtraPaymentDate" class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="getExtraPaymentDate" name="payment_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="getExtraNotes" class="form-label">Notes (optional)</label>
                        <input type="text" class="form-control" id="getExtraNotes" name="notes" placeholder="From extra paid">
                    </div>
                    <button type="submit" class="btn btn-success" id="getFromExtraSubmitBtn">
                        <i class="fas fa-check me-2"></i>Apply as payment
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmDeletePayment(paymentId, amount, date, method) {
        Swal.fire({
            title: 'Remove payment?',
            html: '<p>You want to remove this payment?</p><p><strong>Amount:</strong> ' + amount + '<br><strong>Date:</strong> ' + date + '<br><strong>Method:</strong> ' + method + '</p><p class="text-danger"><small>This action cannot be undone.</small></p>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, remove it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-payment-form-' + paymentId).submit();
            }
        });
    }

    $(document).ready(function() {
        const balanceUrl = '{{ url("admin/customers") }}';
        const storeExtraUrl = '{{ url("admin/customers") }}';
        var _allowOverpaymentSubmit = false;

        $('#addPaymentForm').on('submit', function(e) {
            if (_allowOverpaymentSubmit) {
                _allowOverpaymentSubmit = false;
                return;
            }
            const balanceDue = parseFloat($('#addPaymentForm').data('balance-due')) || 0;
            const amount = parseFloat($('#payment_amount').val()) || 0;
            function reenableAddPaymentButton() {
                $('#addPaymentForm button[type="submit"]').prop('disabled', false);
            }
            if (balanceDue <= 0 && amount > 0) {
                e.preventDefault();
                var msg = 'This sale is already fully paid. The amount of ' + amount + ' will be added to the customer\'s wallet.';
                Swal.fire({
                    title: 'Add to customer wallet?',
                    html: '<p>' + msg + '</p>',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'OK',
                    cancelButtonText: 'Cancel'
                }).then(function(r) {
                    if (r.isConfirmed) {
                        _allowOverpaymentSubmit = true;
                        $('#addPaymentForm').submit();
                    } else {
                        reenableAddPaymentButton();
                    }
                });
                return false;
            }
            if (balanceDue > 0 && amount > balanceDue) {
                e.preventDefault();
                const excess = amount - balanceDue;
                var msg = 'You are adding ' + amount + '. Balance due is ' + balanceDue + '. Extra amount of ' + excess + ' will be added to the customer\'s wallet.';
                Swal.fire({
                    title: 'Add extra to wallet?',
                    html: '<p>' + msg + '</p>',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'OK',
                    cancelButtonText: 'Cancel'
                }).then(function(r) {
                    if (r.isConfirmed) {
                        _allowOverpaymentSubmit = true;
                        $('#addPaymentForm').submit();
                    } else {
                        reenableAddPaymentButton();
                    }
                });
                return false;
            }
        });

        $('#btnExtraPaid').on('click', function() {
            const customerId = $(this).data('customer-id');
            const customerName = $(this).data('customer-name');
            $('#extraPaidCustomerId').val(customerId);
            $('#extraPaidCustomerName').text(customerName);
            $('#extraPaidAmount').val('');
            $('#extraPaidNote').val('');
            $('#extraPaidBalance').text('…');
            var modal = new bootstrap.Modal(document.getElementById('extraPaidModal'));
            modal.show();
            $.get(balanceUrl + '/' + customerId + '/extra-paid/balance', function(data) {
                $('#extraPaidBalance').text(data.formatted);
            }).fail(function() {
                $('#extraPaidBalance').text('0');
            });
        });

        $('#extraPaidForm').on('submit', function(e) {
            e.preventDefault();
            const customerId = $('#extraPaidCustomerId').val();
            const $btn = $('#extraPaidSubmitBtn').prop('disabled', true);
            $.ajax({
                url: storeExtraUrl + '/' + customerId + '/extra-paid',
                method: 'POST',
                data: {
                    _token: $('#extraPaidForm input[name="_token"]').val(),
                    amount: $('#extraPaidAmount').val(),
                    note: $('#extraPaidNote').val()
                },
                success: function(data) {
                    $('#extraPaidBalance').text(data.formatted);
                    $('#extraPaidAmount').val('');
                    $('#extraPaidNote').val('');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'success', title: 'Saved', text: data.message }).then(function() {
                            window.location.reload();
                        });
                    } else {
                        alert(data.message);
                        window.location.reload();
                    }
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error saving.';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Error', text: msg });
                    } else {
                        alert(msg);
                    }
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        });

        $('#btnGetFromExtraPaid').on('click', function() {
            const saleId = $(this).data('sale-id');
            const balanceDue = parseFloat($(this).data('balance-due')) || 0;
            const customerId = {{ $sale->customer_id }};
            $('#getExtraBalanceDue').text(balanceDue.toFixed(2));
            $('#getExtraAvailable').text('…');
            $('#getExtraAmount').attr('max', balanceDue);
            var modal = new bootstrap.Modal(document.getElementById('getFromExtraPaidModal'));
            modal.show();
            $.get(balanceUrl + '/' + customerId + '/extra-paid/balance', function(data) {
                const avail = parseFloat(data.balance) || 0;
                $('#getExtraAvailable').text(data.formatted);
                $('#getExtraAmount').attr('max', Math.min(balanceDue, avail));
            }).fail(function() {
                $('#getExtraAvailable').text('0');
            });
        });
    });
</script>
@endsection
