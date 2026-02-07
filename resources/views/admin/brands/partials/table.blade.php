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
                            <span class="badge {{ ($brand->quantity ?? 0) < 10 ? 'bg-danger' : 'bg-success' }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Stock: {{ $brand->quantity ?? 0 }} units">
                                {{ $brand->quantity ?? 0 }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.brands.show', $brand->id) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" data-bs-placement="top" title="View Brand Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.brands.edit', $brand->id) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Brand">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete Brand" onclick="confirmDelete({{ $brand->id }}, '{{ $brand->name }}')">
                                <i class="fas fa-trash"></i>
                            </button>
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
    
    <!-- Pagination -->
    <div class="mt-4">
        {{ $brands->links() }}
    </div>
@else
    <p class="text-muted text-center py-4">No brands found. <a href="{{ route('admin.brands.create') }}">Add your first brand</a></p>
@endif
