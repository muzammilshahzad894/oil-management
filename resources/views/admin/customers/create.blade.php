@extends('admin.layout')

@section('title', 'Add Customer')

@section('content')
<div class="card">
    <div class="card-header">
        <i class="fas fa-user-plus me-2"></i>Add New Customer
    </div>
    <div class="card-body">
        <form action="{{ route('admin.customers.store') }}" method="POST">
            @csrf
            @include('admin.customers.partials.form')
        </form>
    </div>
</div>
@endsection
