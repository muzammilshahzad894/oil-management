@extends('admin.layout')

@section('title', 'Profit & Loss Report')

@section('content')
<div class="card">
    <div class="card-header">
        <i class="fas fa-chart-pie me-2"></i>Profit & Loss Report
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.reports.profit-loss') }}" class="row g-3 mb-4 align-items-end">
            <div class="col-md-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}">
            </div>
            <div class="col-md-3">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}">
            </div>
            <div class="col-md-auto d-flex align-items-end">
                <button type="submit" class="btn btn-primary" style="height: 38px;">
                    <i class="fas fa-filter me-2"></i>Generate
                </button>
            </div>
        </form>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stat-card border-primary">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div class="stat-value">{{ format_amount($totalInvoiced ?? 0) }}</div>
                    <div class="stat-label">Total Invoiced</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card border-info">
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-value">{{ format_amount($totalReceived ?? 0) }}</div>
                    <div class="stat-label">Actual Received</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card {{ ($totalProfit ?? 0) >= 0 ? 'border-success' : 'border-danger' }}">
                    <div class="stat-icon {{ ($totalProfit ?? 0) >= 0 ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger' }}">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-value {{ ($totalProfit ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">{{ format_amount($totalProfit ?? 0) }}</div>
                    <div class="stat-label">Actual Profit / Loss</div>
                </div>
            </div>
        </div>

        <p class="text-muted small">
            <i class="fas fa-info-circle me-1"></i>
            <strong>Actual profit/loss</strong> = money actually received (from payments) minus total cost of goods. 
            Cost comes from the <strong>Purchase price</strong> set on each brand. 
            This reflects real cash flow, not just invoiced amounts.
        </p>
    </div>
</div>
@endsection

@section('styles')
<style>
    .stat-card.border-primary, .stat-card.border-secondary, .stat-card.border-success, .stat-card.border-danger { border-width: 2px !important; }
</style>
@endsection
