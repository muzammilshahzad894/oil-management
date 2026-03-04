<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="historyBody">
            @foreach($transactions as $tx)
                <tr data-tx-id="{{ $tx->id }}">
                    <td class="tx-date">{{ $tx->transaction_date->format('M d, Y') }}</td>
                    <td>
                        @if($tx->type === 'received')
                            <span class="badge bg-success">You got</span>
                        @else
                            <span class="badge bg-danger">You gave</span>
                        @endif
                    </td>
                    <td class="tx-amount">{{ format_amount($tx->amount) }}</td>
                    <td class="tx-desc text-truncate" style="max-width: 180px;" title="{{ e($tx->description ?? '') }}">{{ Str::limit($tx->description ?? '—', 35) }}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-primary btn-edit-tx" data-id="{{ $tx->id }}" data-amount="{{ $tx->amount }}" data-date="{{ $tx->transaction_date->format('Y-m-d') }}" data-desc="{{ e($tx->description ?? '') }}">Edit</button>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete-tx" data-id="{{ $tx->id }}">Delete</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@if($transactions->count() > 0)
    <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2">
        <p class="text-muted small mb-0">Showing {{ $transactions->firstItem() }} to {{ $transactions->lastItem() }} of {{ $transactions->total() }} entries</p>
        {{ $transactions->withQueryString()->links() }}
    </div>
@endif
<p id="historyEmpty" class="text-muted text-center py-4 mb-0" style="display: {{ $transactions->count() ? 'none' : 'block' }};">No entries yet. Use &quot;You got&quot; or &quot;You gave&quot; to add.</p>
