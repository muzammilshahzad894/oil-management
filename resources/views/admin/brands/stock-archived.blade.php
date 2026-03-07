@extends('admin.layout')

@section('title', 'Archived Batches - ' . $brand->name)

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-archive me-2"></i>Archived Stock Batches — {{ $brand->name }}</span>
        <a href="{{ route('admin.brands.show', $brand->id) }}" class="btn btn-sm btn-secondary">Back to Brand</a>
    </div>
    <div class="card-body">
        @if($batches->count() > 0)
        <p class="text-muted small mb-3">Deleted batches are listed below. Restore to make them active again in Stock Batches (FIFO).</p>
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th>Date added</th>
                        <th>Deleted at</th>
                        <th>Quantity</th>
                        <th>Remaining</th>
                        <th>Cost per unit</th>
                        <th>Sale price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($batches as $batch)
                    <tr>
                        <td>{{ $batch->created_at->format('M d, Y H:i') }}</td>
                        <td>{{ $batch->deleted_at?->format('M d, Y H:i') ?? '—' }}</td>
                        <td>{{ $batch->quantity }}</td>
                        <td><span class="badge bg-secondary">{{ $batch->quantity_remaining }}</span></td>
                        <td>{{ format_amount($batch->cost_per_unit) }}</td>
                        <td>{{ $batch->sale_price !== null ? format_amount($batch->sale_price) : '—' }}</td>
                        <td>
                            <form action="{{ route('admin.brands.stock.restore', [$brand->id, $batch->id]) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-undo me-1"></i>Restore</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-muted mb-0">No archived batches.</p>
        @endif
    </div>
</div>
@endsection
