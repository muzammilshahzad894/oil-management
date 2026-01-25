@extends('admin.layout')

@section('title', 'Add Customer')

@section('content')
<div class="card">
    <div class="card-header">
        <i class="fas fa-user-plus me-2"></i>Add New Customer
    </div>
    <div class="card-body">
        <form action="{{ route('admin.customers.store') }}" method="POST" id="customerForm">
            @csrf
            @include('admin.customers.partials.form')
        </form>
        
        <script>
            $(document).ready(function() {
                $('#customerForm').on('submit', function() {
                    const $btn = $('#submitBtn');
                    $btn.prop('disabled', true);
                    $btn.find('.spinner-border').removeClass('d-none');
                    $btn.find('.btn-text').text('Saving...');
                });
            });
        </script>
    </div>
</div>
@endsection
