@extends('layouts.admin')

@section('title', 'GasGo Admin - Sales Reports')
@section('nav-reports', 'active')
@section('page-title', 'Sales Reports')

@section('admin-styles')
<style>
    .report-card {
        background:#fff; border-radius:16px; padding:24px;
        box-shadow:0 4px 15px rgba(0,0,0,.06);
    }
    .chart-placeholder {
        width:100%; height:250px; border-radius:12px;
        background:linear-gradient(135deg,#f8f9fa,#e9ecef);
        display:flex; align-items:center; justify-content:center; color:#aaa;
    }
    .chart-placeholder i { font-size:2.5rem; margin-bottom:8px; }
    .summary-row { display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #f0f0f0; }
    .summary-row:last-child { border-bottom:none; }
    .summary-row .label { color:#888; font-size:.88rem; }
    .summary-row .value { font-weight:700; font-size:.95rem; }
</style>
@endsection

@section('content')
<!-- Date Range Filter -->
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div class="d-flex gap-2 align-items-center">
        <label class="fw-bold" style="font-size:.88rem;color:#555;">Period:</label>
        <select class="form-select form-select-sm" style="border-radius:10px;width:auto;">
            <option>Today</option>
            <option>This Week</option>
            <option selected>This Month</option>
            <option>Last 3 Months</option>
            <option>This Year</option>
            <option>Custom Range</option>
        </select>
    </div>
    <button class="btn" style="background:var(--gasgo-blue);color:#fff;border-radius:10px;font-weight:600;padding:8px 20px;">
        <i class="fas fa-download me-2"></i>Export Report
    </button>
</div>

<!-- Summary Cards -->
<div class="row g-4 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="stat-card">
            <p>Total Revenue</p>
            <h3 style="color:var(--gasgo-blue);">₱{{ number_format($totalRevenue, 2) }}</h3>
            <small class="text-success"><i class="fas fa-arrow-up me-1"></i>This month</small>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card">
            <p>Total Orders</p>
            <h3 style="color:var(--gasgo-orange);">{{ $totalOrders }}</h3>
            <small class="text-success"><i class="fas fa-arrow-up me-1"></i>{{ $totalOrders }} orders</small>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card">
            <p>Avg. Order Value</p>
            <h3 style="color:#27ae60;">₱{{ number_format($avgOrderValue, 2) }}</h3>
            <small class="text-success"><i class="fas fa-calculator me-1"></i>Per order</small>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card">
            <p>Delivery Completion</p>
            <h3 style="color:#9b59b6;">{{ $deliveryCompletion }}%</h3>
            <small class="text-success"><i class="fas fa-check-circle me-1"></i>Success rate</small>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Revenue Chart -->
    <div class="col-lg-8">
        <div class="report-card">
            <h6 class="fw-bold mb-3" style="color:var(--gasgo-blue);">Revenue Trend</h6>
            <div class="chart-placeholder">
                <div class="text-center">
                    <i class="fas fa-chart-line d-block"></i>
                    <span style="font-size:.88rem;">Integrate with Chart.js for revenue visualization</span>
                </div>
            </div>
        </div>
    </div>
    <!-- Order Channels -->
    <div class="col-lg-4">
        <div class="report-card">
            <h6 class="fw-bold mb-3" style="color:var(--gasgo-blue);">Order Channels</h6>
            <div class="chart-placeholder" style="height:180px;">
                <div class="text-center">
                    <i class="fas fa-chart-pie d-block"></i>
                    <span style="font-size:.88rem;">Pie chart</span>
                </div>
            </div>
            <div class="mt-3">
                <div class="summary-row">
                    <span class="label"><span class="badge bg-primary me-1">&nbsp;</span>Website</span>
                    <span class="value">45%</span>
                </div>
                <div class="summary-row">
                    <span class="label"><span class="badge bg-success me-1">&nbsp;</span>App</span>
                    <span class="value">30%</span>
                </div>
                <div class="summary-row">
                    <span class="label"><span class="badge bg-info me-1">&nbsp;</span>Walk-in</span>
                    <span class="value">15%</span>
                </div>
                <div class="summary-row">
                    <span class="label"><span class="badge bg-secondary me-1">&nbsp;</span>Call/Text</span>
                    <span class="value">10%</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top Products & Payment Methods -->
<div class="row g-4">
    <div class="col-lg-6">
        <div class="report-card">
            <h6 class="fw-bold mb-3" style="color:var(--gasgo-blue);"><i class="fas fa-trophy me-2" style="color:var(--gasgo-orange);"></i>Top Products</h6>
            @forelse($topProducts as $product)
                <div class="summary-row">
                    <div>
                        <span class="fw-bold">{{ $product->name }}</span>
                        <div style="font-size:.78rem;color:#888;">{{ $product->total_quantity }} units sold</div>
                    </div>
                    <span class="value" style="color:var(--gasgo-orange);">₱{{ number_format($product->total_revenue, 2) }}</span>
                </div>
            @empty
                <div class="text-center text-muted py-3">
                    <i class="fas fa-inbox me-2"></i>No products sold yet.
                </div>
            @endforelse
        </div>
    </div>
    <div class="col-lg-6">
        <div class="report-card">
            <h6 class="fw-bold mb-3" style="color:var(--gasgo-blue);"><i class="fas fa-credit-card me-2" style="color:var(--gasgo-orange);"></i>Payment Summary</h6>
            @php
                $totalPaymentRevenue = $paymentMethods->sum('revenue');
            @endphp
            @forelse($paymentMethods as $payment)
                <div class="summary-row">
                    <span class="label">
                        @if($payment->payment_method === 'cash')
                            <i class="fas fa-money-bill-wave me-2 text-success"></i>Cash
                        @elseif($payment->payment_method === 'gcash')
                            <i class="fas fa-mobile-alt me-2 text-primary"></i>GCash
                        @elseif($payment->payment_method === 'card')
                            <i class="fas fa-credit-card me-2 text-info"></i>Card
                        @else
                            {{ ucfirst($payment->payment_method) }}
                        @endif
                    </span>
                    <span class="value">₱{{ number_format($payment->revenue, 2) }} <small class="text-muted">({{ $totalPaymentRevenue > 0 ? round(($payment->revenue / $totalPaymentRevenue) * 100) : 0 }}%)</small></span>
                </div>
            @empty
                <div class="text-center text-muted py-3">
                    <i class="fas fa-inbox me-2"></i>No payment data available.
                </div>
            @endforelse
            <div class="summary-row mt-3" style="border-top:2px solid var(--gasgo-blue);padding-top:14px;">
                <span class="label fw-bold" style="color:var(--gasgo-blue);">Total Revenue</span>
                <span class="value" style="font-size:1.1rem;color:var(--gasgo-blue);">₱{{ number_format($totalRevenue, 2) }}</span>
            </div>

            <h6 class="fw-bold mt-4 mb-3" style="color:var(--gasgo-blue);font-size:.9rem;"><i class="fas fa-motorcycle me-2" style="color:var(--gasgo-orange);"></i>Delivery Stats</h6>
            <div class="summary-row">
                <span class="label">Total Deliveries</span>
                <span class="value">{{ $totalDeliveries }}</span>
            </div>
            <div class="summary-row">
                <span class="label">Avg. Delivery Time</span>
                <span class="value">{{ $avgDeliveryTime ? round($avgDeliveryTime) : 0 }} mins</span>
            </div>
            <div class="summary-row">
                <span class="label">Avg. Customer Rating</span>
                <span class="value"><i class="fas fa-star text-warning me-1"></i>{{ round($avgRating, 1) }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
