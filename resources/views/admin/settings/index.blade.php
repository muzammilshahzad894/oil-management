@extends('admin.layout')

@section('title', 'Settings')

@section('content')
<div class="card">
    <div class="card-header">
        <i class="fas fa-cog me-2"></i>Settings
    </div>
    <div class="card-body">
        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="show_purchase_price" name="show_purchase_price" value="1" {{ $showPurchasePrice ? 'checked' : '' }}>
                    <label class="form-check-label" for="show_purchase_price">Show purchase price column</label>
                </div>
                <small class="text-muted d-block mt-1">When enabled, the purchase price column is shown on Sales, Customer History, and Brand details. When disabled, it is hidden (e.g. when showing screen to customer).</small>
            </div>
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>
    </div>
</div>
@endsection
