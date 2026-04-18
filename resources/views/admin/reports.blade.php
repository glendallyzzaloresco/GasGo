@extends('layouts.admin')

@section('title', 'GasGo Admin - Sales Reports')
@section('nav-reports', 'active')
@section('page-title', 'Sales Reports')

@section('admin-styles')
<style>
    .report-card {
        background:#fff; border-radius:16px; padding:18px;
        box-shadow:0 2px 8px rgba(0,0,0,.08);
        border:1px solid #f0f0f0;
        transition: all 0.3s ease;
    }
    .report-card:hover {
        box-shadow:0 4px 16px rgba(0,0,0,.12);
    }
    .chart-placeholder {
        width:100%; height:250px; border-radius:12px;
        background:linear-gradient(135deg,#f8f9fa,#e9ecef);
        display:flex; align-items:center; justify-content:center; color:#aaa;
    }
    .chart-placeholder i { font-size:2.5rem; margin-bottom:8px; }
    .chart-shell {
        height: 280px;
        position: relative;
    }
    .chart-shell.small {
        height: 220px;
    }
    .summary-row { 
        display:flex; 
        justify-content:space-between;
        align-items:center;
        padding:8px 0; 
        border-bottom:1px solid #f5f5f5;
    }
    .summary-row:last-child { border-bottom:none; }
    .summary-row .label { color:#666; font-size:.88rem; font-weight:500; }
    .summary-row .value { font-weight:700; font-size:.95rem; }
    .analytics-grid {
        display:grid;
        grid-template-columns:repeat(4, minmax(0, 1fr));
        gap:12px;
    }
    .analytics-card {
        background:linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        border:1px solid #e8f0fa;
        border-radius:12px;
        padding:14px;
        box-shadow:0 2px 6px rgba(15,23,42,.04);
        transition: all 0.3s ease;
    }
    .analytics-card:hover {
        transform: translateY(-2px);
        box-shadow:0 4px 12px rgba(15,23,42,.08);
    }
    .analytics-card .label {
        color:#64748b;
        font-size:.82rem;
        font-weight:600;
        text-transform:uppercase;
        letter-spacing:.04em;
        margin-bottom:8px;
    }
    .analytics-card .value {
        font-size:1.8rem;
        font-weight:800;
        color:var(--gasgo-blue);
        margin:8px 0 8px;
    }
    .analytics-card .meta {
        color:#757575;
        font-size:.82rem;
        line-height:1.4;
    }
    .growth-chart {
        display:flex;
        align-items:flex-end;
        gap:14px;
        min-height:200px;
        padding-top:12px;
    }
    .growth-bar-wrap {
        flex:1;
        text-align:center;
    }
    .growth-bar {
        width:100%;
        max-width:48px;
        margin:0 auto 10px;
        border-radius:14px 14px 8px 8px;
        background:linear-gradient(180deg, var(--gasgo-orange) 0%, #ffb357 100%);
        min-height:12px;
        box-shadow:0 10px 20px rgba(247,148,29,.18);
    }
    .growth-label {
        font-size:.82rem;
        font-weight:600;
        color:#64748b;
    }
    .growth-value {
        font-size:.85rem;
        color:#94a3b8;
        margin-bottom:6px;
        font-weight:600;
    }
    .insight-pill {
        display:inline-flex;
        align-items:center;
        gap:8px;
        padding:8px 12px;
        border-radius:999px;
        background:var(--gasgo-orange-light);
        color:var(--gasgo-orange-dark);
        font-size:.82rem;
        font-weight:600;
        margin:4px 8px 0 0;
    }
    .report-loading-target {
        position: relative;
        overflow: hidden;
        transition: opacity .25s ease, transform .25s ease;
    }
    .report-loading-target.loading {
        opacity: .68;
        transform: translateY(1px);
        pointer-events: none;
    }
    .report-loading-target.loading::after {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: inherit;
        background: linear-gradient(100deg, rgba(255,255,255,0) 0%, rgba(255,255,255,.7) 48%, rgba(255,255,255,0) 100%);
        transform: translateX(-120%);
        animation: reportShimmer 1.1s linear infinite;
    }
    @keyframes reportShimmer {
        100% { transform: translateX(120%); }
    }
    @media (max-width: 1199px) {
        .analytics-grid { grid-template-columns:repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 767px) {
        .analytics-grid { grid-template-columns:1fr; }
    }
</style>
@endsection

@section('content')
<!-- Date Range Filter -->
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
    <form method="GET" action="{{ route('admin.reports') }}" class="d-flex gap-2 align-items-center" id="periodFilterForm">
        <label class="fw-bold" style="font-size:.88rem;color:#555;">Period:</label>
        <select name="period" id="periodFilterSelect" class="form-select form-select-sm" style="border-radius:10px;width:auto;">
            <option value="today" {{ $selectedPeriod === 'today' ? 'selected' : '' }}>Today</option>
            <option value="this_week" {{ $selectedPeriod === 'this_week' ? 'selected' : '' }}>This Week</option>
            <option value="this_month" {{ $selectedPeriod === 'this_month' ? 'selected' : '' }}>This Month</option>
            <option value="last_3_months" {{ $selectedPeriod === 'last_3_months' ? 'selected' : '' }}>Last 3 Months</option>
            <option value="this_year" {{ $selectedPeriod === 'this_year' ? 'selected' : '' }}>This Year</option>
        </select>
    </form>
    <button class="btn" style="background:var(--gasgo-blue);color:#fff;border-radius:10px;font-weight:600;padding:8px 20px;">
        <i class="fas fa-download me-2"></i>Export Report
    </button>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-3">
    <div class="col-lg-3 col-md-6">
        <div class="stat-card report-loading-target" data-report-loading>
            <p>Total Revenue</p>
            <h3 id="metricTotalRevenue" style="color:var(--gasgo-blue);">₱{{ number_format($totalRevenue, 2) }}</h3>
            <small class="text-success"><i class="fas fa-arrow-up me-1"></i><span id="metricPeriodLabel">{{ $periodLabel }}</span></small>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card report-loading-target" data-report-loading>
            <p>Total Orders</p>
            <h3 id="metricTotalOrders" style="color:var(--gasgo-orange);">{{ $totalOrders }}</h3>
            <small class="text-success"><i class="fas fa-arrow-up me-1"></i><span id="metricTotalOrdersNote">{{ $totalOrders }} orders</span></small>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card report-loading-target" data-report-loading>
            <p>Avg. Order Value</p>
            <h3 id="metricAvgOrderValue" style="color:#27ae60;">₱{{ number_format($avgOrderValue, 2) }}</h3>
            <small class="text-success"><i class="fas fa-calculator me-1"></i>Per order</small>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card report-loading-target" data-report-loading>
            <p>Delivery Completion</p>
            <h3 id="metricDeliveryCompletion" style="color:#9b59b6;">{{ $deliveryCompletion }}%</h3>
            <small class="text-success"><i class="fas fa-check-circle me-1"></i>Success rate</small>
        </div>
    </div>
</div>

<div class="report-card mb-3 report-loading-target" data-report-loading>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h6 class="fw-bold mb-1" style="color:var(--gasgo-blue);"><i class="fas fa-bullhorn me-2" style="color:var(--gasgo-orange);"></i>Marketing Analytics</h6>
            <p class="mb-0 text-muted" style="font-size:.88rem;">Customer acquisition, retention, and loyalty performance based on current platform data.</p>
        </div>
        <div>
            <span class="insight-pill" id="pillNewCustomers"><i class="fas fa-user-plus"></i>{{ $newCustomersInPeriod }} new in {{ strtolower($periodLabel) }}</span>
            <span class="insight-pill" id="pillRepeatRate"><i class="fas fa-rotate"></i>{{ $repeatCustomerRate }}% repeat rate</span>
        </div>
    </div>

    <div class="analytics-grid">
        <div class="analytics-card">
            <div class="label">Total Customers</div>
            <div class="value" id="metricTotalCustomers">{{ $totalCustomers }}</div>
            <div class="meta" id="metaPayingCustomers">{{ $payingCustomers }} customers have placed at least one non-cancelled order.</div>
        </div>
        <div class="analytics-card">
            <div class="label">Repeat Customers</div>
            <div class="value" id="metricRepeatCustomers">{{ $repeatCustomers }}</div>
            <div class="meta" id="metaRepeatRate">{{ $repeatCustomerRate }}% of paying customers ordered two times or more.</div>
        </div>
        <div class="analytics-card">
            <div class="label">Loyalty Adoption</div>
            <div class="value" id="metricLoyaltyAdoption">{{ $loyaltyAdoptionRate }}%</div>
            <div class="meta" id="metaLoyaltyMembers">{{ $loyaltyMembers }} customers have engaged with the rewards system.</div>
        </div>
        <div class="analytics-card">
            <div class="label">Reward Redemption</div>
            <div class="value" id="metricRewardRedemption">{{ $rewardRedemptionRate }}%</div>
            <div class="meta" id="metaRedeemingCustomers">{{ $redeemingCustomers }} loyalty members have redeemed at least once.</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <!-- Revenue Chart -->
    <div class="col-lg-8">
        <div class="report-card report-loading-target" data-report-loading>
            <h6 class="fw-bold mb-2" style="color:var(--gasgo-blue);">Revenue Trend</h6>
            <div class="chart-shell">
                <canvas id="revenueTrendChart"></canvas>
            </div>
        </div>
    </div>
    <!-- Top Products -->
    <div class="col-lg-4">
        <div class="report-card report-loading-target" data-report-loading>
            <h6 class="fw-bold mb-2" style="color:var(--gasgo-blue);"><i class="fas fa-trophy me-2" style="color:var(--gasgo-orange);"></i>Top Products</h6>
            <div id="topProductsList" style="max-height: 320px; overflow-y: auto;">
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
    </div>
</div>

<!-- Customer Growth -->
<div class="row g-3 mb-3">
    <div class="col-lg-4">
        <div class="report-card report-loading-target" data-report-loading>
            <h6 class="fw-bold mb-2" style="color:var(--gasgo-blue);">Customer Growth</h6>
            <div class="chart-shell small mb-2">
                <canvas id="customerGrowthChart"></canvas>
            </div>
            <div class="growth-chart" id="growthBarsContainer">
                @foreach($customerGrowth as $month)
                    @php
                        $barHeight = $maxCustomerGrowth > 0 ? max(12, round(($month['count'] / $maxCustomerGrowth) * 160)) : 12;
                    @endphp
                    <div class="growth-bar-wrap">
                        <div class="growth-value">{{ $month['count'] }}</div>
                        <div class="growth-bar" data-height="{{ $barHeight }}"></div>
                        <div class="growth-label">{{ $month['label'] }}</div>
                    </div>
                @endforeach
            </div>
            <div class="mt-2">
                <div class="summary-row">
                    <span class="label" id="labelNewCustomersPeriod">New Customers ({{ $periodLabel }})</span>
                    <span class="value" id="metricNewCustomersInPeriod">{{ $newCustomersInPeriod }}</span>
                </div>
                <div class="summary-row">
                    <span class="label">Average Revenue Per Paying Customer</span>
                    <span class="value" id="metricAvgRevenuePerCustomer">₱{{ number_format($avgRevenuePerCustomer, 2) }}</span>
                </div>
                <div class="summary-row">
                    <span class="label">Loyalty Members</span>
                    <span class="value" id="metricLoyaltyMembers">{{ $loyaltyMembers }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<div id="reportConfig" data-endpoint="{{ route('admin.reports') }}" style="display:none;"></div>
<div id="reportChartData"
    data-revenue-labels='@json($revenueTrend->pluck("label"))'
    data-revenue-data='@json($revenueTrend->pluck("value"))'
    data-customer-growth-labels='@json($customerGrowth->pluck("label"))'
    data-customer-growth-data='@json($customerGrowth->pluck("count"))'
    data-payment-labels='@json($paymentBreakdown->pluck("label"))'
    data-payment-data='@json($paymentBreakdown->pluck("value"))'
    style="display:none;">
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const reportEndpoint = document.getElementById('reportConfig').dataset.endpoint;
    const periodSelect = document.getElementById('periodFilterSelect');
    const chartDataEl = document.getElementById('reportChartData');
    const loadingTargets = document.querySelectorAll('[data-report-loading]');
    const revenueLabels = JSON.parse(chartDataEl.dataset.revenueLabels || '[]');
    const revenueData = JSON.parse(chartDataEl.dataset.revenueData || '[]');
    const customerGrowthLabels = JSON.parse(chartDataEl.dataset.customerGrowthLabels || '[]');
    const customerGrowthData = JSON.parse(chartDataEl.dataset.customerGrowthData || '[]');
    const paymentLabels = JSON.parse(chartDataEl.dataset.paymentLabels || '[]');
    const paymentData = JSON.parse(chartDataEl.dataset.paymentData || '[]');

    const moneyTick = (value) => 'P' + Number(value).toLocaleString();
    const peso = (value) => '₱' + Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const numberFmt = (value) => Number(value || 0).toLocaleString();

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function renderGrowthBars(labels, data) {
        const container = document.getElementById('growthBarsContainer');
        const maxValue = Math.max(1, ...(data || [0]));
        container.innerHTML = labels.map((label, idx) => {
            const val = Number(data[idx] || 0);
            const barHeight = Math.max(12, Math.round((val / maxValue) * 160));
            return `<div class="growth-bar-wrap">
                        <div class="growth-value">${numberFmt(val)}</div>
                        <div class="growth-bar" style="height:${barHeight}px"></div>
                        <div class="growth-label">${escapeHtml(label)}</div>
                    </div>`;
        }).join('');
    }

    function paymentIcon(method) {
        const key = String(method || '').toLowerCase();
        if (key === 'cash') return '<i class="fas fa-money-bill-wave me-2 text-success"></i>Cash';
        if (key === 'gcash') return '<i class="fas fa-mobile-alt me-2 text-primary"></i>GCash';
        if (key === 'card') return '<i class="fas fa-credit-card me-2 text-info"></i>Card';
        return escapeHtml(method);
    }

    function renderTopProducts(products) {
        const el = document.getElementById('topProductsList');
        if (!products || !products.length) {
            el.innerHTML = '<div class="text-center text-muted py-3"><i class="fas fa-inbox me-2"></i>No products sold yet.</div>';
            return;
        }
        el.innerHTML = products.map((p) => `
            <div class="summary-row">
                <div>
                    <span class="fw-bold">${escapeHtml(p.name)}</span>
                    <div style="font-size:.78rem;color:#888;">${numberFmt(p.total_quantity)} units sold</div>
                </div>
                <span class="value" style="color:var(--gasgo-orange);">${peso(p.total_revenue)}</span>
            </div>
        `).join('');
    }

    function toggleLoadingState(isLoading) {
        loadingTargets.forEach((el) => el.classList.toggle('loading', isLoading));
        periodSelect.classList.toggle('opacity-75', isLoading);
    }

    const revenueCtx = document.getElementById('revenueTrendChart');
    let revenueChart = null;
    if (revenueCtx && revenueData.length) {
        revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: revenueLabels,
                datasets: [{
                    label: 'Revenue',
                    data: revenueData,
                    borderColor: '#1a6db0',
                    backgroundColor: 'rgba(26,109,176,0.12)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 4,
                    pointHoverRadius: 5,
                    pointBackgroundColor: '#f7941d',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => 'Revenue: ' + moneyTick(ctx.parsed.y)
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: (value) => moneyTick(value) }
                    }
                }
            }
        });
    }

    const growthCtx = document.getElementById('customerGrowthChart');
    let growthChart = null;
    if (growthCtx && customerGrowthData.length) {
        growthChart = new Chart(growthCtx, {
            type: 'bar',
            data: {
                labels: customerGrowthLabels,
                datasets: [{
                    label: 'New Customers',
                    data: customerGrowthData,
                    borderRadius: 8,
                    backgroundColor: 'rgba(247,148,29,0.72)',
                    borderColor: '#f7941d',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, precision: 0 }
                }
            }
        });
    }

    renderGrowthBars(customerGrowthLabels, customerGrowthData);

    async function updateReports(period) {
        periodSelect.disabled = true;
        toggleLoadingState(true);
        const previousText = periodSelect.options[periodSelect.selectedIndex].text;
        try {
            const response = await fetch(`${reportEndpoint}?period=${encodeURIComponent(period)}&ajax=1`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!response.ok) {
                throw new Error('Failed to load reports data.');
            }

            const payload = await response.json();
            const metrics = payload.metrics || {};
            const charts = payload.charts || {};
            const lists = payload.lists || {};
            const periodLabel = payload.periodLabel || previousText;

            document.getElementById('metricPeriodLabel').textContent = periodLabel;
            document.getElementById('metricTotalRevenue').textContent = peso(metrics.totalRevenue);
            document.getElementById('metricTotalRevenueBottom').textContent = peso(metrics.totalRevenue);
            document.getElementById('metricTotalOrders').textContent = numberFmt(metrics.totalOrders);
            document.getElementById('metricTotalOrdersNote').textContent = `${numberFmt(metrics.totalOrders)} orders`;
            document.getElementById('metricAvgOrderValue').textContent = peso(metrics.avgOrderValue);
            document.getElementById('metricDeliveryCompletion').textContent = `${numberFmt(metrics.deliveryCompletion)}%`;

            document.getElementById('pillNewCustomers').innerHTML = `<i class="fas fa-user-plus"></i>${numberFmt(metrics.newCustomersInPeriod)} new in ${String(periodLabel).toLowerCase()}`;
            document.getElementById('pillRepeatRate').innerHTML = `<i class="fas fa-rotate"></i>${numberFmt(metrics.repeatCustomerRate)}% repeat rate`;

            document.getElementById('metricTotalCustomers').textContent = numberFmt(metrics.totalCustomers);
            document.getElementById('metaPayingCustomers').textContent = `${numberFmt(metrics.payingCustomers)} customers have placed at least one non-cancelled order.`;
            document.getElementById('metricRepeatCustomers').textContent = numberFmt(metrics.repeatCustomers);
            document.getElementById('metaRepeatRate').textContent = `${numberFmt(metrics.repeatCustomerRate)}% of paying customers ordered two times or more.`;
            document.getElementById('metricLoyaltyAdoption').textContent = `${numberFmt(metrics.loyaltyAdoptionRate)}%`;
            document.getElementById('metaLoyaltyMembers').textContent = `${numberFmt(metrics.loyaltyMembers)} customers have engaged with the rewards system.`;
            document.getElementById('metricRewardRedemption').textContent = `${numberFmt(metrics.rewardRedemptionRate)}%`;
            document.getElementById('metaRedeemingCustomers').textContent = `${numberFmt(metrics.redeemingCustomers)} loyalty members have redeemed at least once.`;

            document.getElementById('labelNewCustomersPeriod').textContent = `New Customers (${periodLabel})`;
            document.getElementById('metricNewCustomersInPeriod').textContent = numberFmt(metrics.newCustomersInPeriod);
            document.getElementById('metricAvgRevenuePerCustomer').textContent = peso(metrics.avgRevenuePerCustomer);
            document.getElementById('metricLoyaltyMembers').textContent = numberFmt(metrics.loyaltyMembers);
            document.getElementById('metricAvgRating').innerHTML = `<i class="fas fa-star text-warning me-1"></i>${Number(metrics.avgRating || 0).toFixed(1)}`;

            const revenueTrend = charts.revenueTrend || { labels: [], data: [] };
            if (revenueChart) {
                revenueChart.data.labels = revenueTrend.labels || [];
                revenueChart.data.datasets[0].data = revenueTrend.data || [];
                revenueChart.update();
            }

            const growthTrend = charts.customerGrowth || { labels: [], data: [] };
            if (growthChart) {
                growthChart.data.labels = growthTrend.labels || [];
                growthChart.data.datasets[0].data = growthTrend.data || [];
                growthChart.update();
            }
            renderGrowthBars(growthTrend.labels || [], growthTrend.data || []);

            const paymentTrend = charts.paymentBreakdown || { labels: [], data: [] };
            renderTopProducts(lists.topProducts || []);

            const newUrl = `${reportEndpoint}?period=${encodeURIComponent(period)}`;
            window.history.replaceState({}, '', newUrl);
        } catch (error) {
            alert('Unable to update reports right now. Please try again.');
        } finally {
            periodSelect.disabled = false;
            toggleLoadingState(false);
        }
    }

    periodSelect.addEventListener('change', function() {
        updateReports(this.value);
    });
});
</script>
@endsection
