@if($customers->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>You got</th>
                    <th>You gave</th>
                    <th>Balance</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($customers as $c)
                    @php
                        $received = (float) ($c->total_received_sum ?? 0);
                        $gave = (float) ($c->total_gave_sum ?? 0);
                        $balance = $received - $gave;
                    @endphp
                    <tr>
                        <td><strong>{{ $c->name }}</strong></td>
                        <td>{{ $c->phone ?? '—' }}</td>
                        <td>{{ Str::limit($c->address ?? '—', 30) }}</td>
                        <td>{{ format_amount($received) }}</td>
                        <td>{{ format_amount($gave) }}</td>
                        <td class="{{ $balance >= 0 ? 'text-success' : 'text-danger' }}">{{ format_amount($balance) }}</td>
                        <td>
                            <a href="{{ route('admin.ledger.customers.show', $c) }}" class="btn btn-sm btn-info">View</a>
                            <a href="{{ route('admin.ledger.customers.edit', $c) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('admin.ledger.customers.destroy', $c) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this customer from ledger?');">
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
    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
        <p class="text-muted small mb-0">Showing {{ $customers->firstItem() }} to {{ $customers->lastItem() }} of {{ $customers->total() }} customers</p>
        {{ $customers->links() }}
    </div>
@else
    <p class="text-muted text-center py-4 mb-0">No ledger customers found. <a href="{{ route('admin.ledger.customers.create') }}">Add your first customer</a></p>
@endif
