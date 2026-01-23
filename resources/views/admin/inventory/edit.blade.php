@extends('admin.layout')

@section('title', 'Edit Inventory')

@section('content')
<div class="card">
    <div class="card-header">
        <i class="fas fa-edit me-2"></i>Edit Inventory
    </div>
    <div class="card-body">
        <form action="{{ route('admin.inventory.update', $inventory->id) }}" method="POST">
            @csrf
            @method('PUT')
            @include('admin.inventory.partials.form', ['inventory' => $inventory, 'brands' => $brands])
        </form>
    </div>
</div>
@endsection
