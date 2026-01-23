@extends('admin.layout')

@section('title', 'Edit Brand')

@section('content')
<div class="card">
    <div class="card-header">
        <i class="fas fa-edit me-2"></i>Edit Brand
    </div>
    <div class="card-body">
        <form action="{{ route('admin.brands.update', $brand->id) }}" method="POST">
            @csrf
            @method('PUT')
            @include('admin.brands.partials.form', ['brand' => $brand])
        </form>
    </div>
</div>
@endsection
