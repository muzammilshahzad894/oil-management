@extends('admin.layout')

@section('title', 'Inventory')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-warehouse me-2"></i>Inventory</span>
        <a href="{{ route('admin.inventory.create') }}" class="btn btn-light btn-sm">
            <i class="fas fa-plus me-2"></i>Add Stock
        </a>
    </div>
    <div class="card-body">
        @if($inventory->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Brand</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($inventory as $item)
                            <tr>
                                <td><strong>{{ $item->brand->name }}</strong></td>
                                <td>
                                    <span class="badge {{ $item->quantity < 10 ? 'bg-danger' : ($item->quantity < 50 ? 'bg-warning' : 'bg-success') }}">
                                        {{ $item->quantity }}
                                    </span>
                                </td>
                                <td>
                                    @if($item->quantity < 10)
                                        <span class="badge bg-danger">Low Stock</span>
                                    @elseif($item->quantity < 50)
                                        <span class="badge bg-warning">Medium Stock</span>
                                    @else
                                        <span class="badge bg-success">In Stock</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addStockModal{{ $item->id }}" title="Add Stock">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <a href="{{ route('admin.inventory.edit', $item->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.inventory.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this inventory?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    
                                    <!-- Add Stock Modal -->
                                    <div class="modal fade" id="addStockModal{{ $item->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('admin.inventory.add-stock', $item->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Add Stock - {{ $item->brand->name }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label for="quantity{{ $item->id }}" class="form-label">Quantity to Add</label>
                                                            <input type="number" class="form-control" id="quantity{{ $item->id }}" name="quantity" min="1" required>
                                                            <small class="text-muted">Current stock: {{ $item->quantity }}</small>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary">Add Stock</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted text-center py-4">No inventory found. <a href="{{ route('admin.inventory.create') }}">Add your first inventory</a></p>
        @endif
    </div>
</div>
@endsection
