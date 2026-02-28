@extends('admin.layout')

@section('title', 'Edit Brand')

@section('content')
<div class="card">
    <div class="card-header">
        <i class="fas fa-edit me-2"></i>Edit Brand
    </div>
    <div class="card-body">
        <form action="{{ route('admin.brands.update', $brand->id) }}" method="POST" id="brandForm">
            @csrf
            @method('PUT')
            @include('admin.brands.partials.form', ['brand' => $brand])
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        function toggleCostPriceRequired() {
            var q = parseInt($('#quantity').val(), 10) || 0;
            var $cp = $('#cost_price');
            var $star = $('.cost-price-required');
            if (q > 0) { $cp.prop('required', true); $star.show(); } else { $cp.prop('required', false); $star.hide(); }
        }
        $('#quantity').on('input', toggleCostPriceRequired);
        toggleCostPriceRequired();
        $('#brandForm').on('submit', function() {
            var $btn = $('#submitBtn');
            $btn.prop('disabled', true);
            $btn.find('.spinner-border').removeClass('d-none');
            $btn.find('.btn-text').text('Updating...');
        });
    });
</script>
@endsection
