<div class="row">
    <div class="col-md-12 mb-3">
        <label for="name" class="form-label">Brand Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $brand->name ?? '') }}" required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="row">
    <div class="col-md-12 mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description', $brand->description ?? '') }}</textarea>
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="quantity" class="form-label">Stock Quantity</label>
        <input type="number" class="form-control @error('quantity') is-invalid @enderror" id="quantity" name="quantity" value="{{ old('quantity', $brand->quantity ?? 0) }}" min="0">
        @error('quantity')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="cost_price" class="form-label">Purchase price (per unit) <span class="cost-price-required text-danger" style="display: none;">*</span></label>
        @php
            $qtyVal = old('quantity', isset($brand) ? $brand->quantity : 0);
            $costPlaceholder = ($qtyVal > 0) ? 'Required when stock present' : '';
        @endphp
        <input type="number" step="0.01" min="0" class="form-control @error('cost_price') is-invalid @enderror" id="cost_price" name="cost_price" value="{{ old('cost_price', isset($brand) ? $brand->cost_price : '') }}" placeholder="{{ $costPlaceholder }}">
        <small class="text-muted">Required when stock quantity is set, so profit/loss can be calculated on sales.</small>
        @error('cost_price')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="d-flex justify-content-between">
    <a href="{{ route('admin.brands.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back
    </a>
    <button type="submit" class="btn btn-primary" id="submitBtn">
        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
        <i class="fas fa-save me-2"></i><span class="btn-text">{{ isset($brand) ? 'Update' : 'Save' }} Brand</span>
    </button>
</div>
