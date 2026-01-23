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
        
        <script>
            document.getElementById('brandForm').addEventListener('submit', function() {
                const btn = document.getElementById('submitBtn');
                btn.disabled = true;
                btn.querySelector('.spinner-border').classList.remove('d-none');
                btn.querySelector('.btn-text').textContent = 'Updating...';
            });
        </script>
    </div>
</div>
@endsection
