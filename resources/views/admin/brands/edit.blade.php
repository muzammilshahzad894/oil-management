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
        $('#brandForm').on('submit', function() {
            const $btn = $('#submitBtn');
            $btn.prop('disabled', true);
            $btn.find('.spinner-border').removeClass('d-none');
            $btn.find('.btn-text').text('Updating...');
        });
    });
</script>
@endsection
