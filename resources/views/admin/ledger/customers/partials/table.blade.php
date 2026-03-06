@if($customers->count() > 0)
    <span id="ledgerTableTotal" data-total="{{ $customers->total() }}" class="d-none" aria-hidden="true"></span>
    <div class="ledger-customer-list">
        @foreach($customers as $c)
            @php
                $received = (float) ($c->total_received_sum ?? 0);
                $gave = (float) ($c->total_gave_sum ?? 0);
                $balance = $received - $gave;
                $youGive = $balance > 0 ? $balance : 0;
                $youGet = $balance < 0 ? abs($balance) : 0;
                $lastAt = $c->last_transaction_at ? \Carbon\Carbon::parse($c->last_transaction_at) : null;
            @endphp
            <div class="ledger-customer-row" data-ledger-href="{{ route('admin.ledger.customers.show', $c) }}" role="button" tabindex="0">
                <div class="ledger-customer-left">
                    <div class="ledger-customer-avatar">{{ strtoupper(mb_substr($c->name, 0, 1)) }}</div>
                    <div class="ledger-customer-info">
                        <div class="ledger-customer-name">{{ $c->name }}</div>
                        <div class="ledger-customer-meta">
                            @if($lastAt)
                                @if($lastAt->isToday())
                                    Today · {{ $lastAt->format('h:i A') }}
                                @elseif($lastAt->isYesterday())
                                    Yesterday · {{ $lastAt->format('h:i A') }}
                                @else
                                    {{ $lastAt->format('D, d M y · h:i A') }}
                                @endif
                            @else
                                No transaction yet
                            @endif
                        </div>
                    </div>
                </div>
                <div class="ledger-customer-center">
                    @if($youGive > 0 && $youGet > 0)
                        <span class="ledger-center-amount sum-give">Rs {{ number_format($youGive, 0) }}</span>
                        <span class="ledger-center-label sum-give">You'll give</span>
                        <span class="ledger-center-amount sum-get ledger-center-second">Rs {{ number_format($youGet, 0) }}</span>
                        <span class="ledger-center-label sum-get">You'll get</span>
                    @elseif($youGive > 0)
                        <span class="ledger-center-amount sum-give">Rs {{ number_format($youGive, 0) }}</span>
                        <span class="ledger-center-label sum-give">You'll give</span>
                    @elseif($youGet > 0)
                        <span class="ledger-center-amount sum-get">Rs {{ number_format($youGet, 0) }}</span>
                        <span class="ledger-center-label sum-get">You'll get</span>
                    @else
                        <span class="ledger-center-amount sum-zero">Rs 0</span>
                    @endif
                </div>
                <div class="ledger-customer-actions">
                    <a href="{{ route('admin.ledger.customers.show', $c) }}" class="btn btn-sm btn-outline-primary ledger-no-row-nav">Show</a>
                    <a href="{{ route('admin.ledger.customers.edit', $c) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                    <form action="{{ route('admin.ledger.customers.destroy', $c) }}" method="POST" class="d-inline" id="delete-customer-form-{{ $c->id }}">
                        @csrf
                        @method('DELETE')
                    </form>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-customer" data-form-id="delete-customer-form-{{ $c->id }}" data-name="{{ e($c->name) }}">Delete</button>
                </div>
            </div>
        @endforeach
    </div>
    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
        <p class="text-muted small mb-0">Showing {{ $customers->firstItem() }}–{{ $customers->lastItem() }} of {{ $customers->total() }}</p>
        {{ $customers->links() }}
    </div>
@else
    <span id="ledgerTableTotal" data-total="0" class="d-none" aria-hidden="true"></span>
    <p class="text-muted text-center py-4 mb-0">No customers found. <a href="{{ route('admin.ledger.customers.create') }}">Add your first customer</a></p>
@endif
