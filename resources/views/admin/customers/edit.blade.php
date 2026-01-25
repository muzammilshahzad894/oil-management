@extends('admin.layout')

@section('title', 'Edit Customer')

@section('content')
<div class="card">
    <div class="card-header">
        <i class="fas fa-edit me-2"></i>Edit Customer
    </div>
    <div class="card-body">
        <form action="{{ route('admin.customers.update', $customer->id) }}" method="POST" id="customerForm">
            @csrf
            @method('PUT')
            @include('admin.customers.partials.form', ['customer' => $customer])
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#customerForm').on('submit', function() {
            const $btn = $('#submitBtn');
            $btn.prop('disabled', true);
            $btn.find('.spinner-border').removeClass('d-none');
            $btn.find('.btn-text').text('Updating...');
        });
    });
</script>
@endsection
