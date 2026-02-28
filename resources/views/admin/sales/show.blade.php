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
                @if($sale->cost_at_sale !== null)
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
        <form action="{{ route('admin.sales.payments.store', $sale->id) }}" method="POST" class="row g-3 mb-4 p-3 bg-light rounded">
            @csrf
            <div class="col-md-2">
                <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                <input type="number" step="any" min="0" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" value="{{ old('amount') }}" placeholder="0.00" required>
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
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-plus me-2"></i>Add Payment
                </button>
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
</script>
@endsection
