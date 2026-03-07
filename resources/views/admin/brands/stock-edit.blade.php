@extends('admin.layout')

@section('title', 'Edit Stock - ' . $brand->name)

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-edit me-2"></i>Edit Stock Batch — {{ $brand->name }}</span>
        <a href="{{ route('admin.brands.show', $brand->id) }}" class="btn btn-sm btn-secondary">Back to Brand</a>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.brands.stock.update', [$brand->id, $batch->id]) }}" method="POST" id="stockEditForm">
            @csrf
            @method('PUT')
            @include('admin.brands.partials.stock-form', ['batch' => $batch, 'allocated' => $allocated])
            <div class="d-flex justify-content-between mt-3">
                <a href="{{ route('admin.brands.show', $brand->id) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary" id="submitBtn"><i class="fas fa-save me-2"></i>Update</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function() {
    const allocated = {{ $allocated }};
    const $form = document.getElementById('stockEditForm');
    if (!$form) return;
    $form.addEventListener('submit', function(e) {
        const qty = parseInt(document.getElementById('quantity').value, 10);
        if (qty < allocated) {
            e.preventDefault();
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Invalid quantity', text: 'Quantity cannot be less than ' + allocated + '.' });
            } else {
                alert('Quantity cannot be less than ' + allocated + '.');
            }
            return false;
        }
    });
})();
</script>
@endsection
