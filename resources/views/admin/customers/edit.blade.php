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
        
        <script>
            document.getElementById('customerForm').addEventListener('submit', function() {
                const btn = document.getElementById('submitBtn');
                btn.disabled = true;
                btn.querySelector('.spinner-border').classList.remove('d-none');
                btn.querySelector('.btn-text').textContent = 'Updating...';
            });
        </script>
    </div>
</div>
@endsection
