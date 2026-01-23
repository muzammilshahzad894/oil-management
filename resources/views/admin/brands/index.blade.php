@extends('admin.layout')

@section('title', 'Brands')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-tags me-2"></i>Brands</span>
        <a href="{{ route('admin.brands.create') }}" class="btn btn-light btn-sm">
            <i class="fas fa-plus me-2"></i>Add New Brand
        </a>
    </div>
    <div class="card-body">
        @if($brands->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($brands as $brand)
                            <tr>
                                <td><strong>{{ $brand->name }}</strong></td>
                                <td>{{ Str::limit($brand->description, 50) ?? 'N/A' }}</td>
                                <td>
                                    @if($brand->inventory)
                                        <span class="badge {{ $brand->inventory->quantity < 10 ? 'bg-danger' : 'bg-success' }}">
                                            {{ $brand->inventory->quantity }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">No Stock</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.brands.show', $brand->id) }}" class="btn btn-sm btn-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.brands.edit', $brand->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.brands.destroy', $brand->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this brand?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted text-center py-4">No brands found. <a href="{{ route('admin.brands.create') }}">Add your first brand</a></p>
        @endif
    </div>
</div>
@endsection
