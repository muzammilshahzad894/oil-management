<div class="row">
    <div class="col-md-12 mb-3">
        <label for="brand_search" class="form-label">Brand <span class="text-danger">*</span></label>
        <div class="position-relative">
            <input type="text" 
                   class="form-control @error('brand_id') is-invalid @enderror" 
                   id="brand_search" 
                   placeholder="Type to search brand..."
                   autocomplete="off"
                   value="{{ old('brand_name', isset($inventory) ? $inventory->brand->name : '') }}"
                   {{ isset($inventory) ? 'disabled' : '' }}>
            <input type="hidden" id="brand_id" name="brand_id" value="{{ old('brand_id', $inventory->brand_id ?? '') }}" required>
                    <div id="brand_dropdown" class="dropdown-menu w-100" style="display: none;">
                @if(!isset($inventory))
                    @foreach($brands as $brand)
                        <a class="dropdown-item brand-option" href="#" data-id="{{ $brand->id }}">
                            {{ $brand->name }}
                        </a>
                    @endforeach
                @endif
            </div>
        </div>
        @error('brand_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="row">
    <div class="col-md-12 mb-3">
        <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
        <input type="number" class="form-control @error('quantity') is-invalid @enderror" id="quantity" name="quantity" value="{{ old('quantity', $inventory->quantity ?? '') }}" min="0" required>
        @error('quantity')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="d-flex justify-content-between">
    <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back
    </a>
    <button type="submit" class="btn btn-primary" id="submitBtn">
        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
        <i class="fas fa-save me-2"></i><span class="btn-text">{{ isset($inventory) ? 'Update' : 'Save' }} Inventory</span>
    </button>
</div>
