<div class="row">
    <div class="col-md-6 mb-3">
        <label for="customer_search" class="form-label">Customer <span class="text-danger">*</span></label>
        <div class="position-relative w-100 customer-dropdown-wrapper">

            <input type="text" 
                   class="form-control @error('customer_id') is-invalid @enderror" 
                   id="customer_search" 
                   placeholder="Type to search customer..."
                   autocomplete="off"
                   value="{{ session('old_customer_name', old('customer_name', isset($sale) ? $sale->customer->name : '')) }}">
            <input type="hidden" id="customer_id" name="customer_id" value="{{ old('customer_id', $sale->customer_id ?? '') }}" required>
                    <div id="customer_dropdown" class="dropdown-menu w-100" style="display: none;">
                <div class="text-center p-3">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
        @error('customer_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="brand_search" class="form-label">Brand <span class="text-danger">*</span></label>
        <div class="position-relative">
            <input type="text" 
                   class="form-control @error('brand_id') is-invalid @enderror" 
                   id="brand_search" 
                   placeholder="Type to search brand..."
                   autocomplete="off"
                   value="{{ session('old_brand_name', old('brand_name', isset($sale) ? $sale->brand->name : '')) }}">
            <input type="hidden" id="brand_id" name="brand_id" value="{{ old('brand_id', $sale->brand_id ?? '') }}" required>
                    <div id="brand_dropdown" class="dropdown-menu w-100" style="display: none;">
                @foreach($brands as $brand)
                    @php $stock = (int) ($brand->inventory_batches_sum_quantity_remaining ?? 0); @endphp
                    <a class="dropdown-item brand-option" href="#" data-id="{{ $brand->id }}" data-stock="{{ $stock }}">
                        {{ $brand->name }} (Stock: {{ $stock }})
                    </a>
                @endforeach
            </div>
        </div>
        @error('brand_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div id="stock-info" class="stock-info-display mt-2 py-2 px-3 rounded fw-bold" style="display: none;"></div>
    </div>
</div>
<div class="row">
    <div class="col-md-4 mb-3">
        <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
        <input type="number" class="form-control @error('quantity') is-invalid @enderror" id="quantity" name="quantity" value="{{ old('quantity', $sale->quantity ?? '') }}" min="1" required>
        @error('quantity')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="price" class="form-label">Amount <span class="text-danger">*</span></label>
        <input type="number" step="any" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $sale->price ?? '') }}" min="0" required>
        @error('price')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="sale_date" class="form-label">Sale Date <span class="text-danger">*</span></label>
        <input type="date" class="form-control @error('sale_date') is-invalid @enderror" id="sale_date" name="sale_date" value="{{ old('sale_date', isset($sale) ? $sale->sale_date->format('Y-m-d') : date('Y-m-d')) }}" required>
        @error('sale_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
@if(!isset($sale))
<div class="card bg-light border-0 mb-3" id="extraPaidOfferCard" style="display: none;">
    <div class="card-body py-3">
        <p class="mb-2 small" id="extraPaidOfferText"></p>
        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" id="use_extra_paid_checkbox" name="use_extra_paid" value="1" {{ old('use_extra_paid') ? 'checked' : '' }}>
            <label class="form-check-label" for="use_extra_paid_checkbox">Use this extra paid amount in this sale</label>
        </div>
        <div id="extraPaidBreakdown" class="small text-muted mb-0" style="display: none;"></div>
        <input type="hidden" name="initial_extra_paid_amount" id="initial_extra_paid_amount" value="{{ old('initial_extra_paid_amount', '0') }}">
    </div>
</div>
<div class="card bg-light border-0 mb-3">
    <div class="card-body py-3">
        <h6 class="text-muted mb-2"><i class="fas fa-money-bill-wave me-1"></i> Payment received now (optional)</h6>
        <p class="small text-muted mb-3">Enter amount received. You can enter more than the sale total; the excess will be added to the customer's extra paid balance for future use.</p>
        <div class="row align-items-end">
            <div class="col-md-4 mb-2 mb-md-0">
                <label for="initial_payment" class="form-label">Amount received now</label>
                <input type="number" step="any" min="0" class="form-control @error('initial_payment') is-invalid @enderror" id="initial_payment" name="initial_payment" value="{{ old('initial_payment', '') }}" placeholder="0">
                @error('initial_payment')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-3 mb-2 mb-md-0">
                <label for="initial_payment_date" class="form-label">Date</label>
                <input type="date" class="form-control" id="initial_payment_date" name="initial_payment_date" value="{{ old('initial_payment_date', date('Y-m-d')) }}">
            </div>
            <div class="col-md-3 mb-2 mb-md-0">
                <label for="initial_payment_method" class="form-label">Method</label>
                <select class="form-select" id="initial_payment_method" name="initial_payment_method">
                    @foreach(\App\Models\Payment::methods() as $value => $label)
                        <option value="{{ $value }}" {{ old('initial_payment_method', 'cash') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>
@endif
<div class="row">
    <div class="col-md-12 mb-3">
        <label for="notes" class="form-label">Notes</label>
        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes', $sale->notes ?? '') }}</textarea>
        @error('notes')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="d-flex justify-content-between">
    <a href="{{ route('admin.sales.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back
    </a>
    <div>
        @if(!isset($sale))
        <button type="submit" name="action" value="save_and_print" class="btn btn-success me-2" id="submitPrintBtn">
            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
            <i class="fas fa-print me-2"></i><span class="btn-text">Save and Print</span>
        </button>
        @endif
        <button type="submit" name="action" value="save" class="btn btn-primary" id="submitBtn">
            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
            <i class="fas fa-save me-2"></i><span class="btn-text">{{ isset($sale) ? 'Update' : 'Save' }}</span>
        </button>
    </div>
</div>
