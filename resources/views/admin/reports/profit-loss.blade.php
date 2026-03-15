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

        <div class="row mb-4 g-3">
            <div class="col-md-3">
                <div class="stat-card border-secondary h-100">
                    <div class="stat-card-top d-flex align-items-center gap-3 mb-2">
                        <div class="stat-icon stat-icon-secondary rounded-3 d-flex align-items-center justify-content-center">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <span class="stat-label mb-0">Purchase Price</span>
                    </div>
                    <div class="stat-value">{{ format_amount($totalCost ?? 0) }}</div>
                    <div class="stat-readable text-muted small">{{ format_amount_readable($totalCost ?? 0) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card border-primary h-100">
                    <div class="stat-card-top d-flex align-items-center gap-3 mb-2">
                        <div class="stat-icon stat-icon-primary rounded-3 d-flex align-items-center justify-content-center">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <span class="stat-label mb-0">Selling Price</span>
                    </div>
                    <div class="stat-value">{{ format_amount($totalInvoiced ?? 0) }}</div>
                    <div class="stat-readable text-muted small">{{ format_amount_readable($totalInvoiced ?? 0) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card {{ ($profitOnSales ?? 0) >= 0 ? 'border-success' : 'border-danger' }} h-100">
                    <div class="stat-card-top d-flex align-items-center gap-3 mb-2">
                        <div class="stat-icon {{ ($profitOnSales ?? 0) >= 0 ? 'stat-icon-success' : 'stat-icon-danger' }} rounded-3 d-flex align-items-center justify-content-center">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <span class="stat-label mb-0">Profit</span>
                    </div>
                    <div class="stat-value {{ ($profitOnSales ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">{{ format_amount($profitOnSales ?? 0) }}</div>
                    <div class="stat-readable small {{ ($profitOnSales ?? 0) >= 0 ? 'text-success' : 'text-danger' }}" style="opacity: 0.9;">{{ format_amount_readable($profitOnSales ?? 0) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card border-warning h-100">
                    <div class="stat-card-top d-flex align-items-center gap-3 mb-2">
                        <div class="stat-icon stat-icon-warning rounded-3 d-flex align-items-center justify-content-center">
                            <i class="fas fa-clock"></i>
                        </div>
                        <span class="stat-label mb-0">Pending Amount</span>
                    </div>
                    <div class="stat-value">{{ format_amount($pendingAmount ?? 0) }}</div>
                    <div class="stat-readable text-muted small">{{ format_amount_readable($pendingAmount ?? 0) }}</div>
                </div>
            </div>
        </div>

        <p class="text-muted small">
            <i class="fas fa-info-circle me-1"></i>
            <strong>Profit</strong> = Selling price − Purchase price (based on invoiced amounts, not actual cash received).
            <strong>Pending amount</strong> = amount still to be received from customers (Selling price − Actual received).
        </p>
    </div>
</div>
@endsection

@section('styles')
<style>
    .stat-card.border-primary, .stat-card.border-secondary, .stat-card.border-success, .stat-card.border-danger, .stat-card.border-warning { border-width: 2px !important; }
    .stat-card-top { min-height: 2.5rem; }
    .stat-card .stat-label { font-size: 0.9rem; font-weight: 600; color: #4a5568; }
    .stat-value { margin-top: 0.25rem; }
    .stat-readable { margin-top: 0.2rem; font-weight: 500; }

    /* Stat card icons: larger, rounded, with soft background and shadow */
    .stat-icon {
        width: 2.75rem;
        height: 2.75rem;
        font-size: 1.15rem;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }
    .stat-icon-secondary {
        background: linear-gradient(135deg, rgba(108, 117, 125, 0.15) 0%, rgba(108, 117, 125, 0.08) 100%);
        color: #6c757d;
    }
    .stat-icon-primary {
        background: linear-gradient(135deg, rgba(13, 110, 253, 0.18) 0%, rgba(13, 110, 253, 0.08) 100%);
        color: #0d6efd;
    }
    .stat-icon-success {
        background: linear-gradient(135deg, rgba(25, 135, 84, 0.18) 0%, rgba(25, 135, 84, 0.08) 100%);
        color: #198754;
    }
    .stat-icon-danger {
        background: linear-gradient(135deg, rgba(220, 53, 69, 0.18) 0%, rgba(220, 53, 69, 0.08) 100%);
        color: #dc3545;
    }
    .stat-icon-warning {
        background: linear-gradient(135deg, rgba(255, 193, 7, 0.25) 0%, rgba(255, 193, 7, 0.12) 100%);
        color: #b8860b;
    }
</style>
@endsection
