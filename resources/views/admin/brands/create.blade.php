@extends('admin.layout')

@section('title', 'Add Brand')

@section('content')
<div class="card">
    <div class="card-header">
        <i class="fas fa-tag me-2"></i>Add New Brand
    </div>
    <div class="card-body">
        <form action="{{ route('admin.brands.store') }}" method="POST" id="brandForm">
            @csrf
            @include('admin.brands.partials.form')
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
            $btn.find('.btn-text').text('Saving...');
        });
    });
</script>
@endsection
