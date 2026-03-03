@if($brands->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Stock</th>
                    <th>Sold</th>
                    @if($showPurchasePrice ?? true)
                    <th>Purchase price</th>
                    @endif
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($brands as $brand)
                    <tr>
                        <td><strong>{{ $brand->name }}</strong></td>
                        <td>{{ Str::limit($brand->description, 50) ?? 'N/A' }}</td>
                        <td>
                            <span class="badge {{ ($brand->quantity ?? 0) < 10 ? 'bg-danger' : 'bg-success' }}">
                                {{ $brand->quantity ?? 0 }}
                            </span>
                        </td>
                        <td>{{ $brand->sales_sum_quantity ?? 0 }}</td>
                        @if($showPurchasePrice ?? true)
                        <td>{{ $brand->cost_price !== null ? format_amount($brand->cost_price) : '—' }}</td>
                        @endif
                        <td>
                            <a href="{{ route('admin.brands.show', $brand->id) }}" class="btn btn-sm btn-info">View</a>
                            <a href="{{ route('admin.brands.edit', $brand->id) }}" class="btn btn-sm btn-warning">Edit</a>
                            <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete({{ $brand->id }}, '{{ addslashes($brand->name) }}')">Delete</button>
                            <form id="delete-form-{{ $brand->id }}" action="{{ route('admin.brands.destroy', $brand->id) }}" method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
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
