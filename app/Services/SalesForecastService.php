<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SalesForecastService
{
    public function buildInventoryAnalytics(array $options = []): array
    {
        $period = (string) ($options['period'] ?? 'this_month');
        $dateFrom = $options['date_from'] ?? null;
        $dateTo = $options['date_to'] ?? null;

        [$rangeStart, $rangeEnd, $periodLabel] = $this->resolveDateRange($period, $dateFrom, $dateTo);

        $forecastPeriod = (string) ($options['forecast_period'] ?? 'next_month');
        [$forecastHorizon, $forecastPeriodLabel] = $this->resolveForecastPeriod($forecastPeriod);

        $products = Product::query()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('category')
                  ->orWhereRaw('LOWER(category) != ?', ['freebie']);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'category']);

        $selectedProductId = (int) ($options['forecast_product_id'] ?? 0);
        if ($selectedProductId <= 0 || !$products->contains('id', $selectedProductId)) {
            $selectedProductId = (int) ($products->first()->id ?? 0);
        }

        $summary = $this->summaryMetrics($rangeStart, $rangeEnd);
        $salesByProduct = $this->salesByProduct($rangeStart, $rangeEnd);
        $salesByCategory = $this->salesByCategory($rangeStart, $rangeEnd);
        $salesByCustomer = $this->salesByCustomer($rangeStart, $rangeEnd);
        $salesByDate = $this->salesByDate($rangeStart, $rangeEnd);
        $revenueTrend = $this->revenueTrend($rangeStart, $rangeEnd);

        $selectedForecast = $this->productForecast($selectedProductId, $forecastHorizon);
        $topForecastProducts = $this->topForecastProducts($forecastHorizon, $products);
        $seasonalProducts = $this->seasonalProducts();
        $forecastInsights = $this->forecastInsights($selectedForecast, $topForecastProducts, $seasonalProducts);
        $restockRecommendations = $this->restockRecommendations($topForecastProducts);

        $salesForecastPayload = [
            'summary' => [
                'todayQuantity' => (float) $summary['todayItemsSold'],
                'weekQuantity' => (float) $summary['weekItemsSold'],
                'monthQuantity' => (float) $summary['monthItemsSold'],
                'totalRevenue' => (float) $summary['totalSales'],
                'totalOrders' => (int) $summary['totalOrders'],
            ],
            'forecast' => [
                'dates' => collect($selectedForecast['forecastDates'])->map(function (array $item) {
                    return ['label' => $item['label'], 'date' => $item['date']];
                })->values(),
                'values' => collect($selectedForecast['forecastValues'])->values(),
                'level' => (float) $selectedForecast['level'],
                'trend' => (float) $selectedForecast['trend'],
                'seasonality' => collect($selectedForecast['seasonality'])->values(),
                'seasonalityAverage' => (float) $selectedForecast['seasonalityAverage'],
                'totalForecastDemand' => (float) $selectedForecast['forecastDemand'],
            ],
            'charts' => [
                'actualVsForecast' => [
                    'labels' => collect($selectedForecast['chartLabels'])->values(),
                    'actual' => collect($selectedForecast['actualSeries'])->values(),
                    'forecast' => collect($selectedForecast['forecastSeries'])->values(),
                    'forecastLower' => collect($selectedForecast['forecastLower'])->values(),
                    'forecastUpper' => collect($selectedForecast['forecastUpper'])->values(),
                ],
                'stockVsForecast' => [
                    'labels' => $restockRecommendations->pluck('product')->values(),
                    'stock' => $restockRecommendations->pluck('current_stock')->values(),
                    'forecast' => $restockRecommendations->pluck('forecast_units')->values(),
                ],
                'productShare' => [
                    'labels' => $salesByCategory->pluck('category')->values(),
                    'values' => $salesByCategory->pluck('units_sold')->values(),
                ],
            ],
            'topProducts' => $salesByProduct->take(5)->map(function (array $row) use ($salesByProduct) {
                $total = max(1.0, (float) $salesByProduct->sum('units_sold'));

                return [
                    'name' => $row['product'],
                    'sold_quantity' => (float) $row['units_sold'],
                    'share' => round(((float) $row['units_sold'] / $total) * 100, 1),
                ];
            })->values(),
            'recommendations' => $restockRecommendations->map(function (array $row) {
                return [
                    'name' => $row['product'],
                    'current_stock' => $row['current_stock'],
                    'forecasted_demand' => $row['forecast_units'],
                    'suggested_restock_quantity' => $row['restock_qty'],
                    'status' => $row['status'],
                ];
            })->values(),
        ];

        return [
            'selectedPeriod' => $period,
            'periodLabel' => $periodLabel,
            'dateFrom' => $rangeStart?->toDateString(),
            'dateTo' => $rangeEnd?->toDateString(),
            'forecastProductId' => $selectedProductId,
            'forecastPeriod' => $forecastPeriod,
            'forecastPeriodLabel' => $forecastPeriodLabel,
            'productsForForecast' => $products,
            'salesSummary' => $summary,
            'salesByProduct' => $salesByProduct,
            'salesByCategory' => $salesByCategory,
            'salesByCustomer' => $salesByCustomer,
            'salesByDate' => $salesByDate,
            'revenueTrend' => $revenueTrend,
            'forecastModel' => $selectedForecast,
            'forecastSummary' => [
                'forecast_demand' => round((float) $selectedForecast['forecastDemand'], 2),
                'forecast_period' => $forecastPeriodLabel,
                'expected_growth' => round((float) $selectedForecast['growthPercent'], 1),
                'forecast_accuracy' => round((float) $selectedForecast['accuracyPercent'], 1),
                'mape' => round((float) $selectedForecast['mape'], 2),
            ],
            'topForecastProducts' => $topForecastProducts,
            'seasonalProducts' => $seasonalProducts,
            'forecastInsights' => $forecastInsights,
            'restockRecommendations' => $restockRecommendations,
            'salesForecast' => $salesForecastPayload,
        ];
    }

    private function resolveDateRange(string $period, ?string $dateFrom, ?string $dateTo): array
    {
        $now = Carbon::now();

        return match ($period) {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay(), 'Today'],
            'this_week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek(), 'This Week'],
            'this_month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth(), 'This Month'],
            'custom' => [
                $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : $now->copy()->startOfMonth(),
                $dateTo ? Carbon::parse($dateTo)->endOfDay() : $now->copy()->endOfMonth(),
                'Custom Range',
            ],
            default => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth(), 'This Month'],
        };
    }

    private function resolveForecastPeriod(string $period): array
    {
        return match ($period) {
            'next_7_days' => [7, 'Next 7 Days'],
            'next_30_days' => [30, 'Next 30 Days'],
            'next_month' => [Carbon::now()->addMonthNoOverflow()->daysInMonth, 'Next Month'],
            default => [Carbon::now()->addMonthNoOverflow()->daysInMonth, 'Next Month'],
        };
    }

    private function baseOutMovementsQuery(): Builder
    {
        return DB::table('stock_movements as sm')
            ->join('inventory as inv', 'inv.id', '=', 'sm.inventory_id')
            ->join('products as p', 'p.id', '=', 'inv.product_id')
            ->leftJoin('orders as o', function ($join) {
                $join->whereRaw("o.order_number = sm.reference OR CONCAT('ORD-', o.id) = sm.reference");
            })
            ->leftJoin('users as u', 'u.id', '=', 'o.user_id')
            ->where('sm.full_out', '>', 0)
            ->whereNotNull('o.id')
            ->whereIn('o.status', ['delivered', 'completed'])
            ->where(function ($q) {
                $q->whereNull('p.category')
                  ->orWhereRaw('LOWER(p.category) != ?', ['freebie']);
            });
    }

    private function applyMovementDateRange(Builder $query, Carbon $start, Carbon $end): Builder
    {
        return $query->whereBetween(DB::raw('COALESCE(sm.movement_date, sm.created_at)'), [$start, $end]);
    }

    private function summaryMetrics(Carbon $start, Carbon $end): array
    {
        $itemsSold = (float) $this->applyMovementDateRange($this->baseOutMovementsQuery(), $start, $end)->sum('sm.full_out');

        $orderIds = $this->applyMovementDateRange($this->baseOutMovementsQuery(), $start, $end)
            ->distinct()
            ->pluck('o.id');

        $totalOrders = (int) $orderIds->count();
        $totalSales = (float) Order::whereIn('id', $orderIds)->sum(DB::raw('COALESCE(total_amount, subtotal - discount)'));
        $avgOrderValue = $totalOrders > 0 ? ($totalSales / $totalOrders) : 0.0;

        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $todayItems = (float) $this->applyMovementDateRange($this->baseOutMovementsQuery(), $todayStart, $todayEnd)->sum('sm.full_out');
        $weekItems = (float) $this->applyMovementDateRange($this->baseOutMovementsQuery(), $weekStart, $weekEnd)->sum('sm.full_out');
        $monthItems = (float) $this->applyMovementDateRange($this->baseOutMovementsQuery(), $monthStart, $monthEnd)->sum('sm.full_out');

        $ordersInRange = Order::query()->whereBetween('created_at', [$start, $end]);
        $deliveryBaseCount = (clone $ordersInRange)
            ->whereIn('status', ['approved', 'assigned', 'out_for_delivery', 'delivered', 'completed', 'cancelled'])
            ->count();
        $deliveredCount = (clone $ordersInRange)
            ->whereIn('status', ['delivered', 'completed'])
            ->count();

        $deliverySuccessRate = $deliveryBaseCount > 0
            ? round(($deliveredCount / $deliveryBaseCount) * 100, 1)
            : 0.0;

        return [
            'totalSales' => round($totalSales, 2),
            'totalOrders' => $totalOrders,
            'avgOrderValue' => round($avgOrderValue, 2),
            'totalItemsSold' => round($itemsSold, 2),
            'deliverySuccessRate' => $deliverySuccessRate,
            'todayItemsSold' => round($todayItems, 2),
            'weekItemsSold' => round($weekItems, 2),
            'monthItemsSold' => round($monthItems, 2),
        ];
    }

    private function salesByProduct(Carbon $start, Carbon $end): Collection
    {
        $rows = $this->applyMovementDateRange($this->baseOutMovementsQuery(), $start, $end)
            ->leftJoin('order_items as oi', function ($join) {
                $join->on('oi.order_id', '=', 'o.id')
                    ->on('oi.product_id', '=', 'p.id');
            })
            ->selectRaw('p.id as product_id, p.name as product_name, SUM(sm.full_out) as units_sold, COALESCE(SUM(COALESCE(oi.subtotal, oi.price * sm.full_out)), 0) as sales_amount')
            ->groupBy('p.id', 'p.name')
            ->orderByDesc('units_sold')
            ->get();

        return $rows->map(function ($row) {
            return [
                'product_id' => (int) $row->product_id,
                'product' => (string) $row->product_name,
                'units_sold' => (float) $row->units_sold,
                'sales_amount' => (float) $row->sales_amount,
            ];
        })->values();
    }

    private function salesByCategory(Carbon $start, Carbon $end): Collection
    {
        $rows = $this->applyMovementDateRange($this->baseOutMovementsQuery(), $start, $end)
            ->selectRaw("CASE
                    WHEN LOWER(COALESCE(p.category, '')) LIKE '%tank%' THEN 'LPG Tanks'
                    WHEN LOWER(COALESCE(p.category, '')) LIKE '%appliance%' OR LOWER(COALESCE(p.category, '')) LIKE '%stove%' THEN 'Appliances'
                    WHEN LOWER(COALESCE(p.category, '')) LIKE '%accessor%' OR LOWER(COALESCE(p.category, '')) LIKE '%regulator%' OR LOWER(COALESCE(p.category, '')) LIKE '%hose%' OR LOWER(COALESCE(p.category, '')) LIKE '%clamp%' THEN 'Accessories'
                    ELSE 'Others'
                END as category_bucket,
                SUM(sm.full_out) as units_sold")
            ->groupBy('category_bucket')
            ->orderByDesc('units_sold')
            ->get();

        return $rows->map(function ($row) {
            return [
                'category' => (string) $row->category_bucket,
                'units_sold' => (float) $row->units_sold,
            ];
        })->values();
    }

    private function salesByCustomer(Carbon $start, Carbon $end): Collection
    {
        $rows = $this->applyMovementDateRange($this->baseOutMovementsQuery(), $start, $end)
            ->leftJoin('order_items as oi', function ($join) {
                $join->on('oi.order_id', '=', 'o.id')
                    ->on('oi.product_id', '=', 'p.id');
            })
            ->selectRaw('u.id as customer_id, COALESCE(u.name, "Guest") as customer_name, COUNT(DISTINCT o.id) as orders_count, COALESCE(SUM(COALESCE(oi.subtotal, oi.price * sm.full_out)), 0) as sales_amount')
            ->groupBy('u.id', 'u.name')
            ->orderByDesc('sales_amount')
            ->limit(20)
            ->get();

        return $rows->map(function ($row) {
            return [
                'customer_id' => (int) ($row->customer_id ?? 0),
                'customer' => (string) $row->customer_name,
                'orders' => (int) $row->orders_count,
                'sales_amount' => (float) $row->sales_amount,
            ];
        })->values();
    }

    private function salesByDate(Carbon $start, Carbon $end): Collection
    {
        $rows = $this->applyMovementDateRange($this->baseOutMovementsQuery(), $start, $end)
            ->leftJoin('order_items as oi', function ($join) {
                $join->on('oi.order_id', '=', 'o.id')
                    ->on('oi.product_id', '=', 'p.id');
            })
            ->selectRaw('DATE(COALESCE(sm.movement_date, sm.created_at)) as sales_date, COUNT(DISTINCT sm.id) as transactions, COALESCE(SUM(COALESCE(oi.subtotal, oi.price * sm.full_out)), 0) as sales_amount')
            ->groupBy('sales_date')
            ->orderBy('sales_date')
            ->get();

        return $rows->map(function ($row) {
            return [
                'date' => (string) $row->sales_date,
                'transactions' => (int) $row->transactions,
                'sales_amount' => (float) $row->sales_amount,
            ];
        })->values();
    }

    private function revenueTrend(Carbon $start, Carbon $end): Collection
    {
        return $this->salesByDate($start, $end)->map(function (array $row) {
            return [
                'label' => Carbon::parse($row['date'])->format('M d'),
                'value' => round((float) $row['sales_amount'], 2),
            ];
        })->values();
    }

    private function productForecast(int $productId, int $horizon): array
    {
        $historyDays = 180;
        $startDate = Carbon::today()->subDays($historyDays - 1)->startOfDay();

        $raw = $this->baseOutMovementsQuery()
            ->where('p.id', $productId)
            ->whereRaw('COALESCE(sm.movement_date, sm.created_at) >= ?', [$startDate])
            ->selectRaw('DATE(COALESCE(sm.movement_date, sm.created_at)) as d, SUM(sm.full_out) as qty')
            ->groupBy('d')
            ->orderBy('d')
            ->pluck('qty', 'd');

        $historySeries = [];
        for ($i = $historyDays - 1; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);
            $key = $day->toDateString();
            $historySeries[] = [
                'date' => $key,
                'label' => $day->format('M d'),
                'quantity' => (float) ($raw[$key] ?? 0),
            ];
        }

        $seriesValues = array_map(static fn (array $row) => (float) $row['quantity'], $historySeries);
        $model = $this->holtWinters($seriesValues, $horizon, 7, 0.4, 0.2, 0.3);

        $forecastDates = collect(range(1, $horizon))->map(function (int $offset) {
            $date = Carbon::today()->addDays($offset);

            return [
                'date' => $date->toDateString(),
                'label' => $date->format('M d'),
            ];
        })->values()->all();

        $historyForChart = collect($historySeries)->slice(max(0, count($historySeries) - 60))->values();
        $labels = $historyForChart->pluck('label')->merge(collect($forecastDates)->pluck('label'))->values();

        $actualSeries = $historyForChart->pluck('quantity')->merge(array_fill(0, $horizon, null))->values()->all();
        $forecastSeries = array_merge(
            array_fill(0, $historyForChart->count(), null),
            array_map(static fn ($v) => round((float) $v, 2), $model['forecast'])
        );

        $stdDev = $this->stdDeviation($seriesValues);
        $forecastLower = array_merge(
            array_fill(0, $historyForChart->count(), null),
            array_map(static fn ($v) => max(0, round((float) $v - 1.96 * $stdDev, 2)), $model['forecast'])
        );
        $forecastUpper = array_merge(
            array_fill(0, $historyForChart->count(), null),
            array_map(static fn ($v) => round((float) $v + 1.96 * $stdDev, 2), $model['forecast'])
        );

        $forecastDemand = array_sum($model['forecast']);
        $recentActual = array_sum(array_slice($seriesValues, -$horizon));
        $growthPercent = $recentActual > 0 ? (($forecastDemand - $recentActual) / $recentActual) * 100 : 0;

        $mape = $this->backtestMape($seriesValues);
        $accuracy = max(0, 100 - $mape);

        return [
            'productId' => $productId,
            'history' => $historySeries,
            'chartLabels' => $labels,
            'actualSeries' => $actualSeries,
            'forecastSeries' => $forecastSeries,
            'forecastLower' => $forecastLower,
            'forecastUpper' => $forecastUpper,
            'forecastDates' => $forecastDates,
            'forecastValues' => array_map(static fn ($v) => round((float) $v, 2), $model['forecast']),
            'level' => round((float) $model['level'], 2),
            'trend' => round((float) $model['trend'], 2),
            'seasonality' => collect($model['seasonality'])->map(fn ($v) => round((float) $v, 2))->values(),
            'seasonalityAverage' => round((float) (collect($model['seasonality'])->avg() ?? 0), 2),
            'forecastDemand' => round((float) $forecastDemand, 2),
            'growthPercent' => round((float) $growthPercent, 2),
            'mape' => round((float) $mape, 2),
            'accuracyPercent' => round((float) $accuracy, 2),
        ];
    }

    private function topForecastProducts(int $horizon, Collection $products): Collection
    {
        return $products->map(function ($product) use ($horizon) {
            $forecast = $this->productForecast((int) $product->id, $horizon);

            return [
                'product_id' => (int) $product->id,
                'product' => (string) $product->name,
                'forecast_units' => (float) $forecast['forecastDemand'],
                'growth' => (float) $forecast['growthPercent'],
                'level' => (float) $forecast['level'],
                'trend' => (float) $forecast['trend'],
            ];
        })
            ->sortByDesc('forecast_units')
            ->take(8)
            ->values()
            ->map(function (array $row, int $index) {
                $row['rank'] = $index + 1;

                return $row;
            });
    }

    private function seasonalProducts(): Collection
    {
        $startDate = Carbon::now()->subMonths(18)->startOfMonth();

        $rows = $this->baseOutMovementsQuery()
            ->whereRaw('COALESCE(sm.movement_date, sm.created_at) >= ?', [$startDate])
            ->selectRaw('p.id as product_id, p.name as product_name, MONTH(COALESCE(sm.movement_date, sm.created_at)) as month_num, SUM(sm.full_out) as qty')
            ->groupBy('p.id', 'p.name', 'month_num')
            ->get();

        $monthNames = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June',
            7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ];

        return $rows->groupBy('product_id')->map(function (Collection $group) use ($monthNames) {
            $productName = (string) ($group->first()->product_name ?? 'Unknown');
            $avg = (float) $group->avg('qty');
            $peak = $group->sortByDesc('qty')->first();

            $increase = $avg > 0 ? (($peak->qty - $avg) / $avg) * 100 : 0;

            return [
                'product' => $productName,
                'peak_season' => $monthNames[(int) $peak->month_num] ?? 'N/A',
                'expected_increase' => round((float) $increase, 1),
            ];
        })
            ->sortByDesc('expected_increase')
            ->take(8)
            ->values();
    }

    private function forecastInsights(array $selectedForecast, Collection $topForecastProducts, Collection $seasonalProducts): array
    {
        $trending = $topForecastProducts->sortByDesc('growth')->first();
        $highest = $topForecastProducts->sortByDesc('forecast_units')->first();
        $seasonal = $seasonalProducts->first();

        $selectedProduct = Product::query()->with('inventory')->find($selectedForecast['productId']);
        $currentStock = (float) ($selectedProduct?->quantity_on_hand ?? $selectedProduct?->stock ?? 0);
        $forecastDemand = (float) ($selectedForecast['forecastDemand'] ?? 0);

        $recommendation = 'Monitor Inventory';
        if ($currentStock < $forecastDemand * 0.8) {
            $recommendation = 'Restock Soon';
        } elseif ($currentStock >= $forecastDemand) {
            $recommendation = 'Stock Sufficient';
        }

        return [
            'trending_product' => $trending['product'] ?? 'N/A',
            'highest_forecast_product' => $highest['product'] ?? 'N/A',
            'seasonal_product' => $seasonal['product'] ?? 'N/A',
            'peak_season' => $seasonal['peak_season'] ?? 'N/A',
            'forecast_demand' => round($forecastDemand, 2),
            'inventory_recommendation' => $recommendation,
        ];
    }

    private function restockRecommendations(Collection $topForecastProducts): Collection
    {
        $productIds = $topForecastProducts->pluck('product_id')->all();
        $products = Product::query()->with('inventory')->whereIn('id', $productIds)->get()->keyBy('id');

        return $topForecastProducts->map(function (array $item) use ($products) {
            $product = $products->get((int) $item['product_id']);
            $currentStock = (float) ($product?->quantity_on_hand ?? $product?->stock ?? 0);
            $forecastUnits = (float) ($item['forecast_units'] ?? 0);
            $restockQty = max(0, (int) ceil($forecastUnits - $currentStock));

            return [
                'rank' => (int) ($item['rank'] ?? 0),
                'product' => (string) ($item['product'] ?? 'N/A'),
                'current_stock' => $currentStock,
                'forecast_units' => round($forecastUnits, 2),
                'restock_qty' => $restockQty,
                'status' => $currentStock >= $forecastUnits ? 'Stock Sufficient' : 'Restock Soon',
            ];
        })->values();
    }

    /**
     * Holt-Winters triple exponential smoothing.
     *
     * Level: Lt = α(Yt - St-s) + (1 - α)(Lt-1 + Tt-1)
     * Trend: Tt = β(Lt - Lt-1) + (1 - β)Tt-1
     * Seasonality: St = γ(Yt - Lt) + (1 - γ)St-s
     */
    private function holtWinters(array $series, int $horizon, int $seasonLength, float $alpha, float $beta, float $gamma): array
    {
        $series = array_values(array_map(static fn ($v) => (float) $v, $series));
        $horizon = max(1, $horizon);
        $seasonLength = max(1, $seasonLength);

        if (count($series) === 0) {
            return [
                'level' => 0.0,
                'trend' => 0.0,
                'seasonality' => array_fill(0, $seasonLength, 0.0),
                'forecast' => array_fill(0, $horizon, 0.0),
            ];
        }

        $seasonals = array_fill(0, $seasonLength, 0.0);
        $firstSeasonCount = min($seasonLength, count($series));
        $firstSeasonAvg = array_sum(array_slice($series, 0, $firstSeasonCount)) / max(1, $firstSeasonCount);

        for ($i = 0; $i < $firstSeasonCount; $i++) {
            $seasonals[$i] = $series[$i] - $firstSeasonAvg;
        }

        $level = $firstSeasonAvg;
        $trend = 0.0;
        if (count($series) > 1) {
            $trend = ($series[count($series) - 1] - $series[0]) / max(1, count($series) - 1);
        }

        for ($t = $seasonLength; $t < count($series); $t++) {
            $value = $series[$t];
            $seasonal = $seasonals[$t % $seasonLength];
            $prevLevel = $level;
            $prevTrend = $trend;

            $level = $alpha * ($value - $seasonal) + (1 - $alpha) * ($prevLevel + $prevTrend);
            $trend = $beta * ($level - $prevLevel) + (1 - $beta) * $prevTrend;
            $seasonals[$t % $seasonLength] = $gamma * ($value - $level) + (1 - $gamma) * $seasonal;
        }

        $forecast = [];
        for ($m = 1; $m <= $horizon; $m++) {
            $seasonalIndex = (count($series) + $m - 1) % $seasonLength;
            $forecast[] = max(0, $level + ($m * $trend) + $seasonals[$seasonalIndex]);
        }

        return [
            'level' => $level,
            'trend' => $trend,
            'seasonality' => $seasonals,
            'forecast' => $forecast,
        ];
    }

    private function backtestMape(array $series): float
    {
        $series = array_values(array_map(static fn ($v) => (float) $v, $series));
        $n = count($series);

        if ($n < 30) {
            return 12.0;
        }

        $holdout = min(14, max(7, intdiv($n, 5)));
        $train = array_slice($series, 0, $n - $holdout);
        $actual = array_slice($series, -$holdout);
        $forecast = $this->holtWinters($train, $holdout, 7, 0.4, 0.2, 0.3)['forecast'];

        $errors = [];
        foreach ($actual as $idx => $actualValue) {
            if ($actualValue <= 0) {
                continue;
            }
            $predicted = (float) ($forecast[$idx] ?? 0);
            $errors[] = abs(($actualValue - $predicted) / $actualValue);
        }

        if (count($errors) === 0) {
            return 12.0;
        }

        return (array_sum($errors) / count($errors)) * 100;
    }

    private function stdDeviation(array $values): float
    {
        $values = array_values(array_map(static fn ($v) => (float) $v, $values));
        $count = count($values);
        if ($count <= 1) {
            return 0.0;
        }

        $mean = array_sum($values) / $count;
        $variance = array_sum(array_map(static fn ($v) => ($v - $mean) ** 2, $values)) / ($count - 1);

        return sqrt(max(0.0, $variance));
    }
}
