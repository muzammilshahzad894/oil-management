@extends('admin.layout')

@section('title', 'Add Stock - ' . $brand->name)

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-layer-group me-2"></i>Add Stock — {{ $brand->name }}</span>
        <a href="{{ route('admin.brands.show', $brand->id) }}" class="btn btn-sm btn-secondary">Back to Brand</a>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">Add a new batch of stock. The system uses FIFO when selling.</p>
        <form action="{{ route('admin.brands.stock.store', $brand->id) }}" method="POST">
            @csrf
            @include('admin.brands.partials.stock-form', ['allocated' => 0])
            <div class="d-flex justify-content-between mt-3">
                <a href="{{ route('admin.brands.show', $brand->id) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Add Stock</button>
            </div>
        </form>
    </div>
</div>
@endsection
