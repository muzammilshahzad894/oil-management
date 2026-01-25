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
                            <span>
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
                            <button type="button" class="btn btn-sm btn-success" onclick="showAddStockModal({{ $item->id }}, '{{ $item->brand->name }}', {{ $item->quantity }})" title="Add Stock">
                                <i class="fas fa-plus"></i>
                            </button>
                            <a href="{{ route('admin.inventory.edit', $item->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-danger" title="Delete" onclick="confirmDelete({{ $item->id }}, '{{ $item->brand->name }}')">
                                <i class="fas fa-trash"></i>
                            </button>
                            <form id="delete-form-{{ $item->id }}" action="{{ route('admin.inventory.destroy', $item->id) }}" method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="mt-4">
        {{ $inventory->links() }}
    </div>
@else
    <p class="text-muted text-center py-4">No inventory found. <a href="{{ route('admin.inventory.create') }}">Add your first inventory</a></p>
@endif
