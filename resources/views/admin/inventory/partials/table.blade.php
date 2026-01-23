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
                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addStockModal{{ $item->id }}" title="Add Stock">
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
                            
                            <!-- Add Stock Modal -->
                            <div class="modal fade" id="addStockModal{{ $item->id }}" tabindex="-1" data-bs-backdrop="static" style="z-index: 1060;">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content" style="z-index: 1061;">
                                        <form action="{{ route('admin.inventory.add-stock', $item->id) }}" method="POST" id="addStockForm{{ $item->id }}">
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
                                                <button type="submit" class="btn btn-primary" id="submitBtn{{ $item->id }}">
                                                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                                    <span class="btn-text">Add Stock</span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <script>
                                document.getElementById('addStockForm{{ $item->id }}').addEventListener('submit', function() {
                                    const btn = document.getElementById('submitBtn{{ $item->id }}');
                                    btn.disabled = true;
                                    btn.querySelector('.spinner-border').classList.remove('d-none');
                                    btn.querySelector('.btn-text').textContent = 'Adding...';
                                });
                            </script>
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
