@extends('admin.layout')

@section('title', 'Add Inventory')

@section('content')
<div class="card">
    <div class="card-header">
        <i class="fas fa-warehouse me-2"></i>Add Inventory
    </div>
    <div class="card-body">
        <form action="{{ route('admin.inventory.store') }}" method="POST">
            @csrf
            @include('admin.inventory.partials.form', ['brands' => $brands])
        </form>
    </div>
</div>
@endsection
