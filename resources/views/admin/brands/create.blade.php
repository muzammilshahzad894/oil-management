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
        
        <script>
            document.getElementById('brandForm').addEventListener('submit', function() {
                const btn = document.getElementById('submitBtn');
                btn.disabled = true;
                btn.querySelector('.spinner-border').classList.remove('d-none');
                btn.querySelector('.btn-text').textContent = 'Saving...';
            });
        </script>
    </div>
</div>
@endsection
