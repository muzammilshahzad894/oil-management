<div class="row">
    <div class="col-md-6 mb-3">
        <label for="customer_search" class="form-label">Customer <span class="text-danger">*</span></label>
        <div class="position-relative w-100 customer-dropdown-wrapper">

            <input type="text" 
                   class="form-control @error('customer_id') is-invalid @enderror" 
                   id="customer_search" 
                   placeholder="Type to search customer..."
                   autocomplete="off"
                   value="{{ old('customer_name', isset($sale) ? $sale->customer->name : '') }}">
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
                   value="{{ old('brand_name', isset($sale) ? $sale->brand->name : '') }}">
            <input type="hidden" id="brand_id" name="brand_id" value="{{ old('brand_id', $sale->brand_id ?? '') }}" required>
                    <div id="brand_dropdown" class="dropdown-menu w-100" style="display: none;">
                @foreach($brands as $brand)
                    <a class="dropdown-item brand-option" href="#" data-id="{{ $brand->id }}" data-stock="{{ $brand->inventory ? $brand->inventory->quantity : 0 }}">
                        {{ $brand->name }} 
                        @if($brand->inventory)
                            (Stock: {{ $brand->inventory->quantity }})
                        @else
                            (No Stock)
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
        @error('brand_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-muted" id="stock-info"></small>
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
        <label for="price" class="form-label">Price <span class="text-danger">*</span></label>
        <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $sale->price ?? '') }}" min="0" required>
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
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="is_paid" class="form-label">Payment Status</label>
        <select class="form-select @error('is_paid') is-invalid @enderror" id="is_paid" name="is_paid">
            <option value="0" {{ old('is_paid', isset($sale) ? ($sale->is_paid ? '1' : '0') : '0') == '0' ? 'selected' : '' }}>Unpaid</option>
            <option value="1" {{ old('is_paid', isset($sale) ? ($sale->is_paid ? '1' : '0') : '0') == '1' ? 'selected' : '' }}>Paid</option>
        </select>
        @error('is_paid')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6 mb-3">
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
