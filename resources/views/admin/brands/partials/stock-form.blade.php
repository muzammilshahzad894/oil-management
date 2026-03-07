@php
    $allocated = $allocated ?? 0;
    $isEdit = isset($batch) && $batch;
@endphp

@if($allocated > 0)
<div class="alert alert-info mb-3" role="alert">
    <i class="fas fa-info-circle me-2"></i>
    <strong>{{ $allocated }}</strong> unit(s) from this batch have already been sold. Quantity cannot be set below <strong>{{ $allocated }}</strong>.
</div>
@endif

@if($isEdit && $allocated > 0)
<div id="priceChangeAlert" class="alert alert-warning mb-3" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <strong>Note:</strong> Changing cost or sale price will <strong>not</strong> affect existing sales. Their recorded cost and amount will remain as-is.
</div>
@endif

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
        <input type="number" class="form-control @error('quantity') is-invalid @enderror" id="quantity" name="quantity" value="{{ old('quantity', $isEdit ? $batch->quantity : '') }}" min="{{ max(1, $allocated) }}" required>
        @if($allocated > 0)
        <small class="text-muted">Minimum: {{ $allocated }} (already sold from this batch)</small>
        @endif
        @error('quantity')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="cost_per_unit" class="form-label">Cost per unit <span class="text-danger">*</span></label>
        <input type="number" step="any" min="0" class="form-control @error('cost_per_unit') is-invalid @enderror" id="cost_per_unit" name="cost_per_unit" value="{{ old('cost_per_unit', $isEdit ? $batch->cost_per_unit : '') }}" required>
        @error('cost_per_unit')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="sale_price" class="form-label">Sale per unit</label>
        <input type="number" step="any" min="0" class="form-control @error('sale_price') is-invalid @enderror" id="sale_price" name="sale_price" value="{{ old('sale_price', $isEdit ? $batch->sale_price : '') }}">
        @error('sale_price')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        @if(!$isEdit)
        <small class="text-muted">Used to suggest amount when creating a sale.</small>
        @endif
    </div>
</div>
