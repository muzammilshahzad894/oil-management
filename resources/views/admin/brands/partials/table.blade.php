@if($brands->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Stock</th>
                    <th>Sold</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($brands as $brand)
                    @php $stock = (int) ($brand->inventory_batches_sum_quantity_remaining ?? 0); @endphp
                    <tr>
                        <td><strong>{{ $brand->name }}</strong></td>
                        <td>{{ Str::limit($brand->description, 50) ?? 'N/A' }}</td>
                        <td>
                            <span class="badge {{ $stock < 10 ? 'bg-danger' : 'bg-success' }}">
                                {{ $stock }}
                            </span>
                        </td>
                        <td>{{ $brand->sales_sum_quantity ?? 0 }}</td>
                        <td>
                            <a href="{{ route('admin.brands.show', $brand->id) }}" class="btn btn-sm btn-info">View</a>
                            <a href="{{ route('admin.brands.edit', $brand->id) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('admin.brands.destroy', $brand->id) }}" method="POST" class="d-inline js-confirm-delete" data-confirm-title="Delete this brand?" data-confirm-text="This cannot be undone.">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="text-muted small mt-2">
        Showing {{ $brands->firstItem() }} to {{ $brands->lastItem() }} of {{ $brands->total() }} records
    </div>
    <div class="mt-4">
        {{ $brands->links() }}
    </div>
@else
    <p class="text-muted text-center py-4">No brands found. <a href="{{ route('admin.brands.create') }}">Add your first brand</a></p>
@endif
