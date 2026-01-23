<div class="row">
    <div class="col-md-12 mb-3">
        <label for="brand_id" class="form-label">Brand <span class="text-danger">*</span></label>
        <select class="form-select @error('brand_id') is-invalid @enderror" id="brand_id" name="brand_id" required {{ isset($inventory) ? 'disabled' : '' }}>
            <option value="">Select a brand</option>
            @foreach($brands as $brand)
                <option value="{{ $brand->id }}" {{ old('brand_id', $inventory->brand_id ?? '') == $brand->id ? 'selected' : '' }}>
                    {{ $brand->name }}
                </option>
            @endforeach
        </select>
        @if(isset($inventory))
            <input type="hidden" name="brand_id" value="{{ $inventory->brand_id }}">
        @endif
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
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save me-2"></i>{{ isset($inventory) ? 'Update' : 'Save' }} Inventory
    </button>
</div>
