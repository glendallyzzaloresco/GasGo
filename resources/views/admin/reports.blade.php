@extends('layouts.admin')

@section('title', 'Sales Reports & Forecast Analytics')
@section('nav-reports', 'active')
@section('page-title', 'Sales Reports & Forecast Analytics')

@section('admin-styles')
    <style>
        .report-card {
            background: #fff;
            border-radius: 16px;
            padding: 18px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
            border: 1px solid #f0f0f0;
            transition: all .25s ease;
        }

        .report-card:hover {
            box-shadow: 0 6px 16px rgba(0, 0, 0, .10);
        }

        .chart-shell {
            height: 300px;
            position: relative;
        }

        .chart-shell.short {
            height: 240px;
        }

        .section-heading {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }

        .section-note {
            color: #64748b;
            font-size: .87rem;
            margin: 0;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .summary-row:last-child {
            border-bottom: none;
        }

        .insight-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: .78rem;
            font-weight: 700;
            background: var(--gasgo-orange-light);
            color: var(--gasgo-orange-dark);
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: .78rem;
            font-weight: 700;
        }

        .status-pill.good {
            background: rgba(39, 174, 96, .14);
            color: #1e8449;
        }

        .status-pill.warn {
            background: rgba(231, 76, 60, .14);
            color: #c0392b;
        }

        .status-pill.info {
            background: rgba(26, 109, 176, .12);
            color: #1a6db0;
        }

        .process-flow {
            border-left: 2px dashed #d4e2f3;
            margin-left: 10px;
            padding-left: 18px;
        }

        .process-item {
            position: relative;
            margin-bottom: 14px;
        }

        .process-item:last-child {
            margin-bottom: 0;
        }

        .process-item::before {
            content: '';
            position: absolute;
            left: -24px;
            top: 5px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--gasgo-blue);
        }

        .process-title {
            font-weight: 700;
            color: var(--gasgo-blue);
            font-size: .9rem;
        }

        .process-text {
            color: #64748b;
            font-size: .82rem;
            margin-top: 2px;
        }

        .table thead th {
            background: #f8fbff;
            color: var(--gasgo-blue);
            border-bottom: 1px solid #e8eef5;
            font-size: .82rem;
            font-weight: 700;
        }

        @media (max-width: 768px) {
            .report-card {
                padding: 14px 12px;
                border-radius: 14px;
            }
            .chart-shell {
                height: 220px;
            }
            .chart-shell.short {
                height: 180px;
            }
        }

        .table tbody td {
            font-size: .86rem;
            color: #334155;
        }

        .section-divider {
            margin: 20px 0 16px;
            border-top: 1px solid #edf2f7;
        }

        @media (max-width: 991px) {
            .chart-shell {
                height: 260px;
            }
        }
    </style>
@endsection

@section('content')
    @php
        $forecastDates = collect($forecastModel['forecastDates'] ?? []);
        $forecastValues = collect($forecastModel['forecastValues'] ?? []);
    @endphp

    <div class="report-card mb-3">
        <div class="section-heading mb-0">
            <div>
                <h5 class="fw-bold mb-1" style="color:var(--gasgo-blue);">Sales Reports & Forecast Analytics</h5>
                <p class="section-note">Delivered orders are used as sales history only through completed Inventory OUT
                    transactions. Inventory stock is only used after forecasting to check restocking needs.</p>
            </div>

        </div>
    </div>

    <form method="GET" action="{{ route('admin.reports') }}" class="report-card mb-3">
        <div class="row g-3 align-items-end">
            <div class="col-lg-2 col-md-4">
                <label class="form-label fw-semibold" style="font-size:.82rem;">Period</label>
                <select class="form-select form-select-sm" name="period" id="periodSelect">
                    <option value="today" {{ $selectedPeriod === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="this_week" {{ $selectedPeriod === 'this_week' ? 'selected' : '' }}>This Week</option>
                    <option value="this_month" {{ $selectedPeriod === 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="custom" {{ $selectedPeriod === 'custom' ? 'selected' : '' }}>Custom Date Range</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-4">
                <label class="form-label fw-semibold" style="font-size:.82rem;">Date From</label>
                <input type="date" class="form-control form-control-sm" name="date_from" value="{{ $dateFrom }}">
            </div>
            <div class="col-lg-2 col-md-4">
                <label class="form-label fw-semibold" style="font-size:.82rem;">Date To</label>
                <input type="date" class="form-control form-control-sm" name="date_to" value="{{ $dateTo }}">
            </div>

            <input type="hidden" name="forecast_product_id" value="{{ $forecastProductId }}">
            <input type="hidden" name="forecast_period" value="{{ $forecastPeriod }}">

            <div class="col-lg-3 col-md-6">
                <button class="btn btn-sm w-100" style="background:var(--gasgo-blue);color:#fff;">Apply</button>
            </div>
        </div>
    </form>

    <!-- SECTION 1: Historical Sales -->
    <div class="report-card mb-3">
        <div class="section-heading">
            <div>
                <h6 class="fw-bold mb-1" style="color:var(--gasgo-blue);">Section 1: Sales Reports (Historical Data)</h6>
                <p class="section-note">Historical sales computed from Inventory OUT transactions for {{ $periodLabel }}.
                </p>
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-outline-success fw-semibold" onclick="exportSalesForecastCsv()"
                    style="border-radius:8px;">
                    <i class="fas fa-file-csv me-1"></i>Export Sales Data (CSV)
                </button>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                <div class="stat-card" style="border-left: 4px solid #64748b;">
                    <p><i class="fas fa-coins text-secondary me-1"></i>Capital (COGS)</p>
                    <h3 class="text-secondary">₱{{ number_format($salesSummary['totalCapital'] ?? 0, 2) }}</h3>
                    <small class="text-muted" style="font-size:.75rem;">Total product cost invested</small>
                </div>
            </div>
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                <div class="stat-card" style="cursor: pointer; border-left: 4px solid var(--gasgo-blue);" onclick="openSalesDetails()">
                    <p><i class="fas fa-chart-line text-primary me-1"></i>Revenue (Total Sales)</p>
                    <h3 style="color:var(--gasgo-blue);">₱{{ number_format($salesSummary['totalSales'] ?? 0, 2) }}</h3>
                    <small class="text-muted" style="font-size:.75rem;">Total sales generated</small>
                </div>
            </div>
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                <div class="stat-card" style="border-left: 4px solid #28a745;">
                    <p><i class="fas fa-hand-holding-dollar text-success me-1"></i>Gross Profit</p>
                    <h3 class="text-success">₱{{ number_format($salesSummary['totalProfit'] ?? 0, 2) }}</h3>
                    <small class="text-muted" style="font-size:.75rem;">Net profit: <strong>{{ number_format($salesSummary['profitMargin'] ?? 0, 1) }}%</strong> margin</small>
                </div>
            </div>
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                <div class="stat-card" style="border-left: 4px solid var(--gasgo-orange);">
                    <p><i class="fas fa-boxes-stacked text-warning me-1"></i>Volume & Orders</p>
                    <h3 style="color:var(--gasgo-orange);">{{ number_format($salesSummary['totalItemsSold'] ?? 0) }} <small style="font-size:.85rem;color:#64748b;">units</small></h3>
                    <small class="text-muted" style="font-size:.75rem;">Across {{ number_format($salesSummary['totalOrders'] ?? 0) }} orders</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-7">
            <div class="report-card h-100">
                <div class="section-heading">
                    <div>
                        <h6 class="fw-bold mb-1" style="color:var(--gasgo-blue);"><i class="fas fa-list-check me-2"></i>Product Sales & Profit Breakdown</h6>
                        <p class="section-note">Capital cost, revenue, profit, and margin per product for {{ $periodLabel }}.</p>
                    </div>
                </div>
                <div class="table-responsive" style="max-height:340px;">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr class="table-light">
                                <th>Product</th>
                                <th class="text-end">Cost</th>
                                <th class="text-end">Sold</th>
                                <th class="text-end">Capital</th>
                                <th class="text-end">Revenue</th>
                                <th class="text-end">Profit</th>
                                <th class="text-end">Margin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($salesByProduct as $row)
                                <tr>
                                    <td class="fw-semibold">{{ $row['product'] }}</td>
                                    <td class="text-end text-muted small">₱{{ number_format($row['unit_cost'] ?? 0, 2) }}</td>
                                    <td class="text-end fw-bold">{{ number_format($row['units_sold']) }}</td>
                                    <td class="text-end text-secondary">₱{{ number_format($row['capital'] ?? 0, 2) }}</td>
                                    <td class="text-end fw-semibold">₱{{ number_format($row['sales_amount'], 2) }}</td>
                                    <td class="text-end fw-bold {{ ($row['profit'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ ($row['profit'] ?? 0) >= 0 ? '+' : '' }}₱{{ number_format($row['profit'] ?? 0, 2) }}
                                    </td>
                                    <td class="text-end">
                                        <span class="badge {{ ($row['margin_pct'] ?? 0) >= 0 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' }}" style="font-size:.72rem;">
                                            {{ number_format($row['margin_pct'] ?? 0, 1) }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-3">No inventory OUT transactions found for selected date range.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="report-card h-100">
                <div class="section-heading">
                    <div>
                        <h6 class="fw-bold mb-1" style="color:var(--gasgo-blue);"><i class="fas fa-pie-chart me-2"></i>Sales by Category</h6>
                        <p class="section-note">LPG Tanks, Appliances, Accessories, and Others.</p>
                    </div>
                </div>
                <div class="chart-shell short">
                    <canvas id="salesCategoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-6">
            <div class="report-card h-100">
                <div class="section-heading">
                    <div>
                        <h6 class="fw-bold mb-1" style="color:var(--gasgo-blue);">Sales by Customer</h6>
                        <p class="section-note">Customers ranked by inventory-backed sales.</p>
                    </div>
                </div>
                <div class="table-responsive" style="max-height:280px;">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th class="text-end">Orders</th>
                                <th class="text-end">Sales Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($salesByCustomer as $row)
                                <tr>
                                    <td>{{ $row['customer'] }}</td>
                                    <td class="text-end">{{ number_format($row['orders']) }}</td>
                                    <td class="text-end">₱{{ number_format($row['sales_amount'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">No customer sales data available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="report-card h-100">
                <div class="section-heading">
                    <div>
                        <h6 class="fw-bold mb-1" style="color:var(--gasgo-blue);">Sales by Date</h6>
                        <p class="section-note">Transactions and sales amount by date.</p>
                    </div>
                </div>
                <div class="table-responsive" style="max-height:280px;">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th class="text-end">Transactions</th>
                                <th class="text-end">Sales Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($salesByDate as $row)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($row['date'])->format('M d, Y') }}</td>
                                    <td class="text-end">{{ number_format($row['transactions']) }}</td>
                                    <td class="text-end">₱{{ number_format($row['sales_amount'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">No daily sales data available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="report-card mb-3">
        <div class="section-heading">
            <div>
                <h6 class="fw-bold mb-1" style="color:var(--gasgo-blue);">Revenue Trend (Historical)</h6>
                <p class="section-note">Historical revenue only from Inventory OUT transactions. No forecast overlay in this
                    chart.</p>
            </div>
        </div>
        <div class="chart-shell">
            <canvas id="revenueTrendChart"></canvas>
        </div>
    </div>

    <!-- SECTION 2: Forecast Analytics -->
    <div class="report-card mb-3" id="forecastSection">
        <div class="section-heading">
            <div style="flex:1">
                <h6 class="fw-bold mb-1" style="color:var(--gasgo-blue);">Section 2: Forecast Analytics (Triple Exponential
                    Smoothing)</h6>
                <p class="section-note">Forecast generated from Inventory OUT transactions only. Pending, cancelled, and
                    incomplete deliveries are excluded.</p>

                <form id="forecastForm" method="GET" action="{{ route('admin.reports') }}"
                    class="row g-3 align-items-end mt-3">
                    <input type="hidden" name="period" value="{{ $selectedPeriod }}">
                    <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                    <input type="hidden" name="date_to" value="{{ $dateTo }}">
                    <input type="hidden" name="ajax" value="1">

                    <div class="col-lg-4 col-md-6">
                        <label class="form-label fw-semibold" style="font-size:.82rem;">Forecast Product</label>
                        <select class="form-select form-select-sm" name="forecast_product_id" id="forecastProductSelect">
                            @foreach($productsForForecast as $product)
                                <option value="{{ $product->id }}" {{ $forecastProductId == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <label class="form-label fw-semibold" style="font-size:.82rem;">Forecast Period</label>
                        <select class="form-select form-select-sm" name="forecast_period" id="forecastPeriodSelect">
                            <option value="next_7_days" {{ $forecastPeriod == 'next_7_days' ? 'selected' : '' }}>Next 7 Days
                            </option>
                            <option value="next_30_days" {{ $forecastPeriod == 'next_30_days' ? 'selected' : '' }}>Next 30
                                Days</option>
                            <option value="next_month" {{ $forecastPeriod == 'next_month' ? 'selected' : '' }}>Next Month
                            </option>
                        </select>
                    </div>

                    <!-- <div class="col-lg-2 col-md-4">
                            <button type="submit" class="btn btn-sm btn-primary w-100">Update</button>
                        </div> -->
                </form>
            </div>
            <span class="insight-chip" id="forecastInsightChip"><i class="fas fa-filter"></i>{{ $forecastPeriodLabel }} for
                {{ optional($productsForForecast->firstWhere('id', $forecastProductId))->name }}</span>
        </div>

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="chart-shell">
                    <canvas id="forecastChart"></canvas>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="summary-row"><span>Forecast Demand</span><strong
                        id="forecastDemandValue">{{ number_format($forecastSummary['forecast_demand'], 2) }} Units</strong>
                </div>
                <div class="summary-row"><span>Forecast Period</span><strong
                        id="forecastPeriodValue">{{ $forecastSummary['forecast_period'] }}</strong></div>
                <div class="summary-row"><span>Expected Growth</span><strong
                        id="expectedGrowthValue">{{ $forecastSummary['expected_growth'] >= 0 ? '+' : '' }}{{ number_format($forecastSummary['expected_growth'], 1) }}%</strong>
                </div>
                <div class="summary-row"><span>Forecast Accuracy</span><strong
                        id="forecastAccuracyValue">{{ number_format($forecastSummary['forecast_accuracy'], 1) }}% (MAPE
                        {{ number_format($forecastSummary['mape'], 2) }})</strong></div>
                <div class="section-divider"></div>
                <div class="summary-row"><span>Level</span><strong
                        id="forecastLevelValue">{{ number_format($forecastModel['level'] ?? 0, 2) }}</strong></div>
                <div class="summary-row"><span>Trend</span><strong
                        id="forecastTrendValue">{{ number_format($forecastModel['trend'] ?? 0, 2) }}</strong></div>
                <div class="summary-row"><span>Seasonality Avg</span><strong
                        id="forecastSeasonalityValue">{{ number_format($forecastModel['seasonalityAverage'] ?? 0, 2) }}</strong>
                </div>
            </div>
        </div>
    </div>

    </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-6">
            <div class="report-card h-100">
                <div class="section-heading">
                    <div>
                        <h6 class="fw-bold mb-1" style="color:var(--gasgo-blue);">Top Forecast Products</h6>
                        <p class="section-note">Products ranked by projected demand.</p>
                    </div>
                </div>
                <div class="table-responsive" style="max-height:280px;">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Product</th>
                                <th class="text-end">Forecast Units</th>

                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topForecastProducts as $row)
                                <tr>
                                    <td>{{ $row['rank'] }}</td>
                                    <td>{{ $row['product'] }}</td>
                                    <td class="text-end">{{ number_format($row['forecast_units'], 2) }}</td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">No forecast product ranking available.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="report-card h-100">
                <div class="section-heading">
                    <div>
                        <h6 class="fw-bold mb-1" style="color:var(--gasgo-blue);">Predicted Seasonal Products</h6>
                        <p class="section-note">Products with detectable recurring seasonal demand peaks.</p>
                    </div>
                </div>
                <div class="table-responsive" style="max-height:280px;">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Peak Season</th>
                                <th class="text-end">Expected Increase</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($seasonalProducts as $row)
                                <tr>
                                    <td>{{ $row['product'] }}</td>
                                    <td>{{ $row['peak_season'] }}</td>
                                    <td class="text-end">{{ number_format($row['expected_increase'], 1) }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">No seasonal pattern detected yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-5">
            <div class="report-card h-100">
                <div class="section-heading">
                    <div>
                        <h6 class="fw-bold mb-1" style="color:var(--gasgo-blue);">Forecast Insights</h6>
                        <p class="section-note">Decision-ready highlights from forecast and inventory comparison.</p>
                    </div>
                </div>
                <div class="summary-row"><span>Trending
                        Product</span><strong>{{ $forecastInsights['trending_product'] }}</strong></div>
                <div class="summary-row"><span>Highest Forecast
                        Product</span><strong>{{ $forecastInsights['highest_forecast_product'] }}</strong></div>
                <div class="summary-row"><span>Seasonal
                        Product</span><strong>{{ $forecastInsights['seasonal_product'] }}</strong></div>
                <div class="summary-row"><span>Peak Season</span><strong>{{ $forecastInsights['peak_season'] }}</strong>
                </div>

                <div class="summary-row">
                    <span>Inventory Recommendation</span>
                    <span
                        class="status-pill {{ str_contains(strtolower($forecastInsights['inventory_recommendation']), 'sufficient') ? 'good' : 'warn' }}">{{ $forecastInsights['inventory_recommendation'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="report-card h-100">
                <div class="section-heading">
                    <div>
                        <h6 class="fw-bold mb-1" style="color:var(--gasgo-blue);">Forecast Process Diagram</h6>
                        <p class="section-note">How Holt-Winters turns historical inventory movements into restocking
                            guidance.</p>
                    </div>
                </div>
                <div class="process-flow">
                    <div class="process-item">
                        <div class="process-title">Historical Sales (Inventory OUT)</div>
                        <div class="process-text">Completed Inventory OUT transactions linked to completed/delivered orders.
                        </div>
                    </div>
                    <div class="process-item">
                        <div class="process-title">Triple Exponential Smoothing</div>
                        <div class="process-text">Level: current average demand. Trend: upward/downward direction.
                            Seasonality: recurring demand cycles.</div>
                    </div>
                    <div class="process-item">
                        <div class="process-title">Demand Forecast</div>
                        <div class="process-text">Product-level projected demand for the selected forecast period.</div>
                    </div>
                    <div class="process-item">
                        <div class="process-title">Inventory Recommendation</div>
                        <div class="process-text">Compare forecast demand with stock-on-hand and recommend restock actions.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="report-card mb-3">
        <div class="section-heading">
            <div>
                <h6 class="fw-bold mb-1" style="color:var(--gasgo-blue);">Inventory Recommendation</h6>
                <p class="section-note">Current inventory versus forecast demand from Triple Exponential Smoothing.</p>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-7">
                <div class="chart-shell short">
                    <canvas id="inventoryRecommendationChart"></canvas>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="table-responsive" style="max-height:260px;">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-end">Current</th>
                                <th class="text-end">Forecast</th>

                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($restockRecommendations as $row)
                                <tr>
                                    <td>{{ $row['product'] }}</td>
                                    <td class="text-end">{{ number_format($row['current_stock']) }}</td>
                                    <td class="text-end">{{ number_format($row['forecast_units'], 2) }}</td>

                                    <td>
                                        <span
                                            class="status-pill {{ str_contains(strtolower($row['status']), 'sufficient') ? 'good' : 'warn' }}">{{ $row['status'] }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">No restock recommendations available.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Details Modal -->
    <div class="modal fade" id="salesDetailsModal" tabindex="-1" aria-labelledby="salesDetailsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content" style="border-radius:16px;">
                <div class="modal-header" style="border-bottom:none;">
                    <h5 class="modal-title fw-bold" id="salesDetailsModalLabel" style="color:var(--gasgo-blue);">
                        Total Sales Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <p class="mb-0">Sold products and dates for the selected report period.</p>
                        </div>
                        <a href="#" id="salesDetailsExportButton" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-file-csv me-1"></i>Export CSV
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Product</th>
                                    <th class="text-end">Quantity</th>
                                    <th class="text-end">Amount</th>
                                    <th>Order / Txn</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($salesDetails ?? [] as $row)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($row['date'])->format('M d, Y') }}</td>
                                        <td>{{ $row['product'] }}</td>
                                        <td class="text-end">{{ number_format($row['quantity']) }}</td>
                                        <td class="text-end">₱{{ number_format($row['amount'], 2) }}</td>
                                        <td>{{ $row['reference'] ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">
                                            No sales detail available for the selected range.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:none;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius:10px;">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const periodSelect = document.getElementById('periodSelect');
            const forecastForm = document.getElementById('forecastForm');
            const forecastProductSelect = document.getElementById('forecastProductSelect');
            const forecastPeriodSelect = document.getElementById('forecastPeriodSelect');
            const forecastInsightChip = document.getElementById('forecastInsightChip');

            const forecastDemandValue = document.getElementById('forecastDemandValue');
            const forecastPeriodValue = document.getElementById('forecastPeriodValue');
            const expectedGrowthValue = document.getElementById('expectedGrowthValue');
            const forecastAccuracyValue = document.getElementById('forecastAccuracyValue');
            const forecastLevelValue = document.getElementById('forecastLevelValue');
            const forecastTrendValue = document.getElementById('forecastTrendValue');
            const forecastSeasonalityValue = document.getElementById('forecastSeasonalityValue');

            window.openSalesDetails = function () {
                const modalEl = document.getElementById('salesDetailsModal');
                if (!modalEl) return;
                new bootstrap.Modal(modalEl).show();
            };

            async function fetchForecastSection(formData) {
                const url = new URL('{{ route('admin.reports') }}', window.location.origin);
                url.search = formData.toString();

                const response = await fetch(url.toString(), {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' }
                });

                if (!response.ok) {
                    console.error('Forecast update failed', response.statusText);
                    return;
                }

                const data = await response.json();

                if (forecastChart) {
                    forecastChart.data.labels = data.forecastModel.chartLabels ?? [];
                    forecastChart.data.datasets[0].data = data.forecastModel.actualSeries ?? [];
                    forecastChart.data.datasets[1].data = data.forecastModel.forecastSeries ?? [];
                    forecastChart.data.datasets[2].data = data.forecastModel.forecastLower ?? [];
                    forecastChart.data.datasets[3].data = data.forecastModel.forecastUpper ?? [];
                    forecastChart.update();
                }

                forecastDemandValue.textContent = `${Number(data.forecastSummary.forecast_demand || 0).toFixed(2)} Units`;
                forecastPeriodValue.textContent = data.forecastSummary.forecast_period || '';
                expectedGrowthValue.textContent = `${data.forecastSummary.expected_growth >= 0 ? '+' : ''}${Number(data.forecastSummary.expected_growth || 0).toFixed(1)}%`;
                forecastAccuracyValue.textContent = `${Number(data.forecastSummary.forecast_accuracy || 0).toFixed(1)}% (MAPE ${Number(data.forecastSummary.mape || 0).toFixed(2)})`;
                forecastLevelValue.textContent = Number(data.forecastModel.level || 0).toFixed(2);
                forecastTrendValue.textContent = Number(data.forecastModel.trend || 0).toFixed(2);
                forecastSeasonalityValue.textContent = Number(data.forecastModel.seasonalityAverage || 0).toFixed(2);

                const selectedProduct = forecastProductSelect.options[forecastProductSelect.selectedIndex]?.text || '';
                const selectedPeriodText = forecastPeriodSelect.options[forecastPeriodSelect.selectedIndex]?.text || '';
                forecastInsightChip.innerHTML = `<i class="fas fa-filter"></i>${selectedPeriodText} for ${selectedProduct}`;
            }

            if (forecastForm) {
                forecastForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    fetchForecastSection(new URLSearchParams(new FormData(this)));
                });
            }

            [forecastProductSelect, forecastPeriodSelect].forEach((el) => {
                if (el) {
                    el.addEventListener('change', function () {
                        fetchForecastSection(new URLSearchParams(new FormData(forecastForm)));
                    });
                }
            });

            if (periodSelect) {
                periodSelect.addEventListener('change', function () {
                    this.form.submit();
                });
            }

            const revenueLabels = @json(collect($revenueTrend)->pluck('label')->values()->all());
            const revenueData = @json(collect($revenueTrend)->pluck('value')->values()->all());

            const categoryLabels = @json(collect($salesByCategory)->pluck('category')->values()->all());
            const categoryData = @json(collect($salesByCategory)->pluck('units_sold')->values()->all());

            const forecastLabels = @json(data_get($forecastModel, 'chartLabels', []));
            const forecastActual = @json(data_get($forecastModel, 'actualSeries', []));
            const forecastSeries = @json(data_get($forecastModel, 'forecastSeries', []));
            const forecastLower = @json(data_get($forecastModel, 'forecastLower', []));
            const forecastUpper = @json(data_get($forecastModel, 'forecastUpper', []));

            const inventoryLabels = @json(collect($restockRecommendations)->pluck('product')->values()->all());
            const inventoryStock = @json(collect($restockRecommendations)->pluck('current_stock')->values()->all());
            const inventoryForecast = @json(collect($restockRecommendations)->pluck('forecast_units')->values()->all());

            const moneyTick = (v) => '₱' + Number(v || 0).toLocaleString();

            const revenueCtx = document.getElementById('revenueTrendChart');
            if (revenueCtx) {
                new Chart(revenueCtx, {
                    type: 'line',
                    data: {
                        labels: revenueLabels,
                        datasets: [{
                            label: 'Historical Revenue',
                            data: revenueData,
                            borderColor: '#1a6db0',
                            backgroundColor: 'rgba(26,109,176,0.12)',
                            fill: true,
                            tension: 0.35,
                            pointRadius: 3,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true, ticks: { callback: moneyTick } } }
                    }
                });
            }

            const categoryCtx = document.getElementById('salesCategoryChart');
            if (categoryCtx) {
                new Chart(categoryCtx, {
                    type: 'doughnut',
                    data: {
                        labels: categoryLabels,
                        datasets: [{
                            data: categoryData,
                            backgroundColor: ['#1a6db0', '#f7941d', '#27ae60', '#9b59b6'],
                            borderWidth: 0,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom' } }
                    }
                });
            }

            const forecastCtx = document.getElementById('forecastChart');
            let forecastChart = null;
            if (forecastCtx) {
                forecastChart = new Chart(forecastCtx, {
                    type: 'line',
                    data: {
                        labels: forecastLabels,
                        datasets: [{
                            label: 'Historical Sales',
                            data: forecastActual,
                            borderColor: '#1a6db0',
                            backgroundColor: 'rgba(26,109,176,0.10)',
                            fill: false,
                            spanGaps: true,
                            tension: 0.35,
                            pointRadius: 2,
                        }, {
                            label: 'Forecast Sales',
                            data: forecastSeries,
                            borderColor: '#f7941d',
                            borderDash: [8, 6],
                            backgroundColor: 'rgba(247,148,29,0.12)',
                            fill: false,
                            spanGaps: true,
                            tension: 0.35,
                            pointRadius: 2,
                        }, {
                            label: '95% Lower',
                            data: forecastLower,
                            borderColor: 'rgba(247,148,29,0.0)',
                            backgroundColor: 'rgba(247,148,29,0.08)',
                            fill: '+1',
                            pointRadius: 0,
                            spanGaps: true,
                        }, {
                            label: '95% Upper',
                            data: forecastUpper,
                            borderColor: 'rgba(247,148,29,0.0)',
                            backgroundColor: 'rgba(247,148,29,0.08)',
                            fill: false,
                            pointRadius: 0,
                            spanGaps: true,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom' },
                            tooltip: {
                                callbacks: {
                                    label: (ctx) => `${ctx.dataset.label}: ${Number(ctx.parsed.y || 0).toLocaleString()}`
                                }
                            }
                        },
                        scales: {
                            y: { beginAtZero: true }
                        }
                    }
                });
            }

            const inventoryCtx = document.getElementById('inventoryRecommendationChart');
            if (inventoryCtx) {
                new Chart(inventoryCtx, {
                    type: 'bar',
                    data: {
                        labels: inventoryLabels,
                        datasets: [{
                            label: 'Current Inventory',
                            data: inventoryStock,
                            backgroundColor: 'rgba(26,109,176,0.65)',
                            borderColor: '#1a6db0',
                            borderWidth: 1,
                            borderRadius: 8,
                        }, {
                            label: 'Forecast Demand',
                            data: inventoryForecast,
                            backgroundColor: 'rgba(247,148,29,0.65)',
                            borderColor: '#f7941d',
                            borderWidth: 1,
                            borderRadius: 8,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y',
                        plugins: { legend: { position: 'bottom' } },
                        scales: { x: { beginAtZero: true } }
                    }
                });
            }
        });

        function exportSalesForecastCsv() {
            const productsData = @json($salesByProduct ?? []);
            const datesData = @json($salesByDate ?? []);
            const periodLabel = @json($periodLabel ?? 'Sales Period');
            const dateFrom = @json($dateFrom ?? '');
            const dateTo = @json($dateTo ?? '');

            let csvContent = "data:text/csv;charset=utf-8,";
            csvContent += "GasGo Sales Report & Forecast Analytics\r\n";
            csvContent += `Period:,"${periodLabel}"\r\n`;
            if (dateFrom && dateTo) {
                csvContent += `Date Range:,"${dateFrom} to ${dateTo}"\r\n`;
            }
            csvContent += `Generated:,"${new Date().toLocaleString()}"\r\n\r\n`;

            // Section: Product Sales Breakdown
            csvContent += "PRODUCT SALES BREAKDOWN\r\n";
            csvContent += "Product Name,Quantity (Units Sold),Sales Amount (PHP),Period\r\n";
            if (productsData && productsData.length > 0) {
                productsData.forEach(row => {
                    const name = (row.product || '').replace(/"/g, '""');
                    const qty = row.units_sold || 0;
                    const amt = (Number(row.sales_amount) || 0).toFixed(2);
                    csvContent += `"${name}",${qty},${amt},"${periodLabel}"\r\n`;
                });
            } else {
                csvContent += "No product sales data available,0,0.00\r\n";
            }

            csvContent += "\r\n";

            // Section: Daily Sales Breakdown
            csvContent += "DAILY SALES BREAKDOWN\r\n";
            csvContent += "Date,Transactions,Sales Amount (PHP)\r\n";
            if (datesData && datesData.length > 0) {
                datesData.forEach(row => {
                    const d = row.date || '';
                    const tx = row.transactions || 0;
                    const amt = (Number(row.sales_amount) || 0).toFixed(2);
                    csvContent += `"${d}",${tx},${amt}\r\n`;
                });
            } else {
                csvContent += "No daily sales data available,0,0.00\r\n";
            }

            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", `GasGo_Sales_Report_${new Date().toISOString().slice(0, 10)}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
@endsection