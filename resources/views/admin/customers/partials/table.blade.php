@if($customers->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Total Purchases</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($customers as $customer)
                    <tr>
                        <td><strong>{{ $customer->name }}</strong></td>
                        <td>{{ $customer->phone ?? 'N/A' }}</td>
                        <td>{{ $customer->email ?? 'N/A' }}</td>
                        <td>
                            <span class="badge bg-primary">{{ $customer->sales_count }} sales</span>
                        </td>
                        <td>
                            <a href="{{ route('admin.sales.create', ['customer_id' => $customer->id]) }}" class="btn btn-sm btn-success">New Sale</a>
                            <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn btn-sm btn-info">History</a>
                            <a href="{{ route('admin.customers.edit', $customer->id) }}" class="btn btn-sm btn-warning">Edit</a>
                            <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete({{ $customer->id }}, '{{ addslashes($customer->name) }}')">Delete</button>
                            <form id="delete-form-{{ $customer->id }}" action="{{ route('admin.customers.destroy', $customer->id) }}" method="POST" style="display: none;">
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
        Showing {{ $customers->firstItem() }} to {{ $customers->lastItem() }} of {{ $customers->total() }} records
    </div>
    <div class="mt-4">
        {{ $customers->links() }}
    </div>
@else
    <p class="text-muted text-center py-4">No customers found. <a href="{{ route('admin.customers.create') }}">Add your first customer</a></p>
@endif
