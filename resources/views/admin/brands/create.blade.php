@extends('admin.layout')

@section('title', 'Add Brand')

@section('content')
<div class="card">
    <div class="card-header">
        <i class="fas fa-tag me-2"></i>Add New Brand
    </div>
    <div class="card-body">
        <form action="{{ route('admin.brands.store') }}" method="POST">
            @csrf
            @include('admin.brands.partials.form')
        </form>
    </div>
</div>
@endsection
