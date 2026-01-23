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
            document.getElementById('customerForm').addEventListener('submit', function() {
                const btn = document.getElementById('submitBtn');
                btn.disabled = true;
                btn.querySelector('.spinner-border').classList.remove('d-none');
                btn.querySelector('.btn-text').textContent = 'Saving...';
            });
        </script>
    </div>
</div>
@endsection
