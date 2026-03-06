@if($transactions->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover table-align-middle mb-0 ledger-history-table">
            <thead class="table-light">
                <tr>
                    <th class="ledger-th-entry">Entry</th>
                    <th class="ledger-th-desc">Description</th>
                    <th class="ledger-th-gave text-end">You gave</th>
                    <th class="ledger-th-get text-end">You get</th>
                    <th class="ledger-th-action text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $item)
                    @php
                        $tx = $item->tx ?? $item;
                        $balanceAfter = isset($item->balance_after) ? $item->balance_after : null;
                        $isReceived = $tx->type === 'received';
                        $dateObj = $tx->transaction_date instanceof \Carbon\Carbon ? $tx->transaction_date : \Carbon\Carbon::parse($tx->transaction_date);
                    @endphp
                    <tr>
                        <td class="ledger-td-entry">
                            <div class="ledger-entry-date">{{ $dateObj->format('D, d M y · h:i A') }}</div>
                            @if($balanceAfter !== null)
                                <div class="ledger-entry-bal {{ $isReceived ? 'text-success' : 'text-danger' }}">Bal. Rs {{ number_format(abs($balanceAfter), 0) }}</div>
                            @endif
                        </td>
                        <td class="ledger-td-desc">
                            @if($tx->description)
                                <span class="ledger-desc-text" title="{{ e($tx->description) }}">{{ Str::limit($tx->description, 50) }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="ledger-td-gave text-end">
                            @if($isReceived)

                            @else
                                <span class="text-danger fw-semibold ledger-amount-cell">{{ number_format($tx->amount, 0) }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($isReceived)
                                <span class="text-success fw-semibold ledger-amount-cell">{{ number_format($tx->amount, 0) }}</span>
                            @else

                            @endif
                        </td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-primary btn-edit-tx" data-id="{{ $tx->id }}" data-amount="{{ $tx->amount }}" data-date="{{ $tx->transaction_date->format('Y-m-d H:i') }}" data-desc="{{ e($tx->description ?? '') }}">Edit</button>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-tx" data-id="{{ $tx->id }}">Delete</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2">
        <p class="text-muted small mb-0">Showing {{ $transactions->firstItem() }} to {{ $transactions->lastItem() }} of {{ $transactions->total() }} entries</p>
        {{ $transactions->withQueryString()->links() }}
    </div>
@endif
<p id="historyEmpty" class="text-muted text-center py-4 mb-0" style="display: {{ $transactions->count() ? 'none' : 'block' }};">No entries yet. Use &quot;You got&quot; or &quot;You gave&quot; to add.</p>

<style>
.table-align-middle td, .table-align-middle th { vertical-align: middle !important; }
.ledger-th-entry, .ledger-td-entry { min-width: 150px; }
.ledger-th-desc, .ledger-td-desc { max-width: 200px; min-width: 120px; }
.ledger-desc-text {
    display: block;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 0.9rem;
}
.ledger-th-gave, .ledger-td-gave { width: 100px; background: rgba(254, 226, 226, 0.4); }
.ledger-history-table .ledger-td-gave { background: rgba(254, 226, 226, 0.35); }
.ledger-th-get { width: 100px; }
.ledger-th-action { width: 150px; }
.ledger-entry-date { font-weight: 600; font-size: 0.9rem; }
.ledger-entry-bal { font-size: 0.85rem; font-weight: 600; }
.ledger-amount-cell { white-space: nowrap; }
</style>
