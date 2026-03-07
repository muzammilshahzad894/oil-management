@if($customers->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Total Purchases</th>
                    <th>Extra paid</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($customers as $customer)
                    @php $extraPaid = (float) ($customer->extra_payments_sum_amount ?? 0); @endphp
                    <tr>
                        <td><strong>{{ $customer->name }}</strong></td>
                        <td>{{ $customer->phone ?? 'N/A' }}</td>
                        <td>{{ $customer->email ?? 'N/A' }}</td>
                        <td>
                            <span class="badge bg-primary">{{ $customer->sales_count }} sales</span>
                        </td>
                        <td>{{ $extraPaid != 0 ? format_amount($extraPaid) : '—' }}</td>
                        <td>
                            <a href="{{ route('admin.sales.create', ['customer_id' => $customer->id]) }}" class="btn btn-sm btn-success">New Sale</a>
                            <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn btn-sm btn-info">History</a>
                            <a href="{{ route('admin.customers.edit', $customer->id) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('admin.customers.destroy', $customer->id) }}" method="POST" class="d-inline js-confirm-delete" data-confirm-title="Delete this customer?" data-confirm-text="This cannot be undone.">
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
    <div class="mt-4">
        {{ $customers->links() }}
    </div>
@else
    <p class="text-muted text-center py-4">No customers found. <a href="{{ route('admin.customers.create') }}">Add your first customer</a></p>
@endif
