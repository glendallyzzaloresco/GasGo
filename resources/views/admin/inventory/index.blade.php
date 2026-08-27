@extends('layouts.admin')

@section('title', 'Inventory Management')
@section('nav-inventory', 'active')

@section('admin-styles')
<style>
    :root {
        --primary-blue: var(--gasgo-blue);
        --primary-orange: var(--gasgo-orange);
    }

    .inventory-header {
        background: linear-gradient(135deg, #1a6db0 0%, #2196f3 100%);
        color: white;
        padding: 35px 30px;
        border-radius: 12px;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(26, 109, 176, 0.2);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .inventory-header h1 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .inventory-header p {
        font-size: 0.95rem;
        opacity: 0.95;
        margin: 0;
    }
    
    .filter-section {
        background: white;
        padding: 24px;
        border-radius: 8px;
        margin-bottom: 24px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        border-left: 4px solid var(--primary-blue);
    }

    .filter-section .form-label {
        font-weight: 600;
        font-size: 0.9rem;
        color: #333;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .filter-section .form-control,
    .filter-section .form-select {
        border-radius: 6px;
        border: 1.5px solid #e0e0e0;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }

    .filter-section .form-control:focus,
    .filter-section .form-select:focus {
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 3px rgba(26, 109, 176, 0.1);
    }

    .filter-section .btn {
        font-weight: 600;
        border-radius: 6px;
        padding: 10px 20px;
        transition: all 0.3s ease;
        font-size: 0.9rem;
    }

    .filter-section .btn-gasgo {
        background: linear-gradient(135deg, var(--primary-blue) 0%, #1e5090 100%);
        border: none;
        box-shadow: 0 4px 12px rgba(26, 109, 176, 0.3);
    }

    .filter-section .btn-gasgo:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(26, 109, 176, 0.4);
    }

    .filter-section .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(39, 174, 96, 0.4) !important;
    }

    .filter-section .btn-primary {
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(39, 174, 96, 0.3);
    }

    /* Table Layout */
    .inventory-table-wrapper {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        overflow: hidden;
        margin-bottom: 30px;
    }

    .inventory-section {
        margin-bottom: 18px;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        background: #fff;
    }

    .inventory-section summary {
        list-style: none;
        cursor: pointer;
        padding: 14px 20px;
        color: #fff;
        font-weight: 700;
        font-size: 1.02rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        user-select: none;
        transition: all 0.3s ease;
    }



    .inventory-section summary::-webkit-details-marker {
        display: none;
    }

    .section-summary-left {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    .section-summary-right {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-size: 0.82rem;
        opacity: 0.95;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .section-chevron {
        transition: transform 0.3s ease;
    }

    details[open] .section-chevron {
        transform: rotate(180deg);
    }

    .section-body {
        background: #fff;
        animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .history-scroll-wrap {
        max-height: 280px;
        overflow-y: auto;
    }

    .table {
        margin: 0;
        font-size: 0.95rem;
    }

    .table thead {
        background: linear-gradient(135deg, #1a6db0 0%, #2196f3 100%);
        color: white;
    }

    .table thead th {
        border: none;
        padding: 16px 12px;
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        vertical-align: middle;
    }

    .table tbody tr {
        border-bottom: 1px solid #f0f0f0;
    }

    .table tbody td {
        padding: 14px 12px;
        vertical-align: middle;
    }

    .product-name-cell {
        font-weight: 600;
        color: #333;
        max-width: 100%;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .product-table-image {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #e0e0e0;
        flex-shrink: 0;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    .product-table-image-placeholder {
        width: 50px;
        height: 50px;
        background: #f0f0f0;
        border-radius: 6px;
        border: 1px solid #e0e0e0;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .stock-status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        align-self: flex-start;
    }

    .stock-status-badge.in-stock {
        background: #d4f4dd;
        color: #0d5f2a;
    }

    .stock-status-badge.low-stock {
        background: #ffe5e5;
        color: #c41e3a;
    }

    .stock-status-badge.out-of-stock {
        background: #fce4ec;
        color: #880e4f;
    }

    .inventory-status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 16px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-active {
        background: #d4edda;
        color: #155724;
    }

    .status-discontinued {
        background: #f8d7da;
        color: #721c24;
    }

    .status-damaged {
        background: #fff3cd;
        color: #856404;
    }

    .inventory-card-actions {
        display: flex;
        gap: 8px;
    }

    /* Category Badge Styles */
    [class^="category-badge-"] {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: capitalize;
    }

    .category-badge-tank {
        background: #e3f2fd;
        color: #1565c0;
    }

    .category-badge-accessories {
        background: #f3e5f5;
        color: #6a1b9a;
    }

    .category-badge-freebie {
        background: #e8f5e9;
        color: #2e7d32;
    }

    .category-badge-stove,
    .category-badge-burner,
    .category-badge-appliance,
    .category-badge-appliances {
        background: #ff6f00;
        color: white;
    }

    .product-category {
        display: block;
    }

    .category-header {
        display: block;
    }

    .empty-inventory {
        text-align: center;
        padding: 60px 20px;
        color: #999;
        background: white;
        border-radius: 8px;
    }

    .empty-inventory i {
        font-size: 3.5rem;
        color: #ddd;
        margin-bottom: 16px;
    }

    .empty-inventory h5 {
        color: #666;
        font-weight: 600;
    }

    .loading-spinner {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 9999;
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        text-align: center;
    }

    .loading-spinner.show {
        display: block;
    }

    .spinner-border-custom {
        width: 50px;
        height: 50px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid var(--gasgo-blue);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-bottom: 12px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.3);
        z-index: 9998;
    }

    .overlay.show {
        display: block;
    }

    /* Action Button Hover Effects */
    .btn-sm {
        transition: all 0.2s ease !important;
    }
</style>
@endsection

@section('content')
<div class="container-fluid p-4">
    @php
        $settings = \App\Models\HomepageSetting::first();
        $industryNoun = $settings->industry_noun ?? 'LPG Tanks';
        $nicheKey = \App\Services\CategoryService::detectNicheKey($industryNoun);
        $isWaterNiche = ($nicheKey === 'water');
        $isFoodNiche = ($nicheKey === 'foods');
        $isApplianceNiche = ($nicheKey === 'appliances');

        $fullStockLabel = $isWaterNiche ? 'Full Gallons' : ($isFoodNiche ? 'Menu Items' : ($isApplianceNiche ? 'In-Stock Units' : 'Full Tanks'));
        $emptyStockLabel = $isWaterNiche ? 'Empty Gallons' : ($isFoodNiche ? 'Portions Sold' : ($isApplianceNiche ? 'Total Stocked' : 'Empty Tanks'));
        $receivedTodayLabel = $isWaterNiche ? 'Full Gallons Received Today' : ($isFoodNiche ? 'Stock Units Received Today' : ($isApplianceNiche ? 'Units Restocked Today' : 'Full Cylinders Received Today'));
        $releasedTodayLabel = $isWaterNiche ? 'Gallons Delivered Today' : ($isFoodNiche ? 'Orders Dispatched Today' : ($isApplianceNiche ? 'Units Dispatched / Sold Today' : 'Full Cylinders Released Today'));
        $emptyReceivedTodayLabel = $isWaterNiche ? 'Empty Gallons Returned Today' : ($isFoodNiche ? 'Orders Completed Today' : ($isApplianceNiche ? 'Registered / In Service' : 'Empty Cylinders Received Today'));
        $inventorySubtitle = $isWaterNiche ? 'Live stock overview by water container types and accessories.' : ($isFoodNiche ? 'Live stock overview by menu items, snacks, and beverages.' : ($isApplianceNiche ? 'Live stock overview by appliance models and parts.' : 'Live stock overview by product and cylinder size.'));
        $movementSubtitle = $isWaterNiche ? 'Recent water container movements with refill and return flow details.' : ($isFoodNiche ? 'Recent food portion movements and dispatch history.' : ($isApplianceNiche ? 'Recent appliance replenishment and distribution flow details.' : 'Recent cylinder movements with full and empty flow details.'));

        $totalFullCylinders = (int) \App\Models\Inventory::where('status', '!=', 'discontinued')
            ->whereHas('product', function ($query) {
                $query->where('is_active', true)->cylinders();
            })->sum('quantity_on_hand');
        $tankInventoryIds = \App\Models\Inventory::where('status', '!=', 'discontinued')
            ->whereHas('product', function ($query) {
                $query->where('is_active', true)->cylinders();
            })->pluck('id');
        $totalEmptyCylinders = (int) \App\Models\Inventory::whereIn('id', $tankInventoryIds)->sum('empty_on_hand');
        $totalProducts = (int) \App\Models\Inventory::where('status', '!=', 'discontinued')
            ->whereHas('product', function ($query) {
                $query->where('is_active', true)->where('price', '>', 0);
            })->count();
        $lowStockProducts = (int) \App\Models\Inventory::where('status', '!=', 'discontinued')
            ->whereHas('product', function ($query) {
                $query->where('is_active', true)->where('price', '>', 0);
            })
            ->where('quantity_on_hand', '>', 0)
            ->where('quantity_on_hand', '<=', 5)
            ->count();

        $movementSnapshot = $recentStockMovements ?? collect();
        
        // Today's Sales - total revenue amount from delivered orders today
        $todaySales = (float) \App\Models\Order::where('status', 'delivered')
            ->where(function ($query) {
                $query->whereDate('delivered_at', today())
                    ->orWhereDate('created_at', today());
            })
            ->selectRaw('COALESCE(SUM(CASE WHEN total_amount > 0 THEN total_amount ELSE (subtotal - discount) END), 0) as total_sales')
            ->value('total_sales') ?? 0;

        // Today's Deliveries - count only ongoing orders (assigned, out_for_delivery, pending)
        $todayDeliveries = (int) \App\Models\Delivery::whereDate('created_at', today())
            ->whereIn('status', ['assigned', 'out_for_delivery', 'pending'])
            ->count();
    @endphp

    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="bi bi-box-seam me-2 text-primary"></i>Inventory Dashboard</h2>
            <p class="text-muted mb-0">Warehouse overview for stock levels, movement activity, and replenishment needs.</p>
        </div>
    </div>

    <div class="row row-cols-2 row-cols-md-3 row-cols-xl-6 g-2 g-md-3 mb-3">
        <div class="col">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-uppercase small fw-semibold text-muted" style="font-size:0.7rem;"><i class="bi bi-box2-heart me-1"></i>{{ $fullStockLabel }}</div>
                            <div class="h4 fw-bold mt-1 mb-0">{{ number_format($totalFullCylinders) }}</div>
                        </div>
                        <div class="rounded-circle bg-success-subtle p-2 text-success"><i class="bi bi-box2-heart fs-6"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-uppercase small fw-semibold text-muted" style="font-size:0.7rem;"><i class="bi bi-bucket me-1"></i>{{ $emptyStockLabel }}</div>
                            <div class="h4 fw-bold mt-1 mb-0">{{ number_format($totalEmptyCylinders) }}</div>
                        </div>
                        <div class="rounded-circle bg-warning-subtle p-2 text-warning"><i class="bi bi-bucket fs-6"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-uppercase small fw-semibold text-muted" style="font-size:0.7rem;"><i class="bi bi-stack me-1"></i>Total Items</div>
                            <div class="h4 fw-bold mt-1 mb-0">{{ number_format($totalProducts) }}</div>
                        </div>
                        <div class="rounded-circle bg-primary-subtle p-2 text-primary"><i class="bi bi-stack fs-6"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-uppercase small fw-semibold text-muted" style="font-size:0.7rem;"><i class="bi bi-exclamation-triangle me-1"></i>Low Stock</div>
                            <div class="h4 fw-bold mt-1 mb-0 text-danger">{{ number_format($lowStockProducts) }}</div>
                        </div>
                        <div class="rounded-circle bg-danger-subtle p-2 text-danger"><i class="bi bi-exclamation-triangle fs-6"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-uppercase small fw-semibold text-muted" style="font-size:0.7rem;"><i class="bi bi-cash-stack me-1"></i>Today's Sales</div>
                            <div class="h4 fw-bold mt-1 mb-0 text-truncate">₱{{ number_format($todaySales, 2) }}</div>
                        </div>
                        <div class="rounded-circle bg-info-subtle p-2 text-info"><i class="bi bi-cash-stack fs-6"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-uppercase small fw-semibold text-muted" style="font-size:0.7rem;"><i class="bi bi-truck me-1"></i>Deliveries</div>
                            <div class="h4 fw-bold mt-1 mb-0">{{ number_format($todayDeliveries) }}</div>
                        </div>
                        <div class="rounded-circle bg-secondary-subtle p-2 text-secondary"><i class="bi bi-truck fs-6"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-2 g-md-3 mb-3">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 text-center">
                    <div class="text-muted small fw-semibold mb-1" style="font-size:0.75rem;">{{ $receivedTodayLabel }}</div>
                    <div class="h4 fw-bold text-success mb-0">{{ (int) ($dailyMovementTotals->full_in ?? 0) }}</div>
                    <div class="text-muted small" style="font-size:0.7rem;">Incoming stock</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 text-center">
                    <div class="text-muted small fw-semibold mb-1" style="font-size:0.75rem;">{{ $releasedTodayLabel }}</div>
                    <div class="h4 fw-bold text-primary mb-0">{{ (int) ($dailyMovementTotals->full_out ?? 0) }}</div>
                    <div class="text-muted small" style="font-size:0.7rem;">Distributed stock</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 text-center">
                    <div class="text-muted small fw-semibold mb-1" style="font-size:0.75rem;">{{ $emptyReceivedTodayLabel }}</div>
                    <div class="h4 fw-bold text-warning mb-0">{{ (int) ($dailyMovementTotals->empty_in ?? 0) }}</div>
                    <div class="text-muted small" style="font-size:0.7rem;">Returned empties</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-3">
                <div>
                    <h5 class="fw-bold mb-1"><i class="bi bi-cubes me-2 text-primary"></i>Current Inventory</h5>
                    <p class="text-muted mb-0">{{ $inventorySubtitle }}</p>
                </div>
                <div class="text-muted small">Showing {{ $inventories->count() }} of {{ $inventories->total() }} records</div>
            </div>

            <form method="GET" action="{{ route('admin.inventory.index') }}" class="row g-3 align-items-end mb-4">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Search</label>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search product name">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="in_stock" {{ request('status') === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                        <option value="out_of_stock" {{ request('status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Category</label>
                    <select name="category" class="form-select">
                        <option value="">All</option>
                        @php
                            $inventoryNicheCats = \App\Services\CategoryService::getCategoriesForCurrentNiche();
                        @endphp
                        @foreach($inventoryNicheCats as $icat)
                            <option value="{{ $icat['slug'] }}" {{ request('category') === $icat['slug'] ? 'selected' : '' }}>
                                {{ $icat['name'] }}
                            </option>
                        @endforeach
                        <option value="freebie" {{ request('category') === 'freebie' ? 'selected' : '' }}>Freebies</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Sort By</label>
                    <select name="sort_by" class="form-select">
                        <option value="name_asc" {{ request('sort_by', 'name_asc') === 'name_asc' ? 'selected' : '' }}>Name A-Z</option>
                        <option value="stock_low" {{ request('sort_by') === 'stock_low' ? 'selected' : '' }}>Stock Low-High</option>
                        <option value="stock_high" {{ request('sort_by') === 'stock_high' ? 'selected' : '' }}>Stock High-Low</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-funnel me-2"></i>Apply Filters</button>
                    <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise me-2"></i>Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                @php
                    $industryNoun = $settings->industry_noun ?? ($homepageSettings->industry_noun ?? 'LPG Tanks');
                    $isWaterNiche = str_contains(strtolower($industryNoun), 'water');
                    $isFoodNiche = str_contains(strtolower($industryNoun), 'food') || str_contains(strtolower($industryNoun), 'meal');
                    $isApplianceNiche = str_contains(strtolower($industryNoun), 'appliance');
                    $containerLabel = $isWaterNiche ? 'Empty Containers / Gallons' : ($isFoodNiche ? 'Returned Containers' : ($isApplianceNiche ? 'Units in Service' : 'Empty Cylinders'));
                    $containerNameSingular = $isWaterNiche ? 'Container' : ($isFoodNiche ? 'Container' : ($isApplianceNiche ? 'Unit' : 'Cylinder'));
                @endphp
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Qty On Hand</th>
                            <th>{{ $containerLabel }}</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $nicheCats = \App\Services\CategoryService::getCategoriesForCurrentNiche();
                            
                            $isMatchingCategory = function($product, $slug) {
                                if (!$product) return false;
                                $cat = strtolower(trim((string) $product->category));
                                $name = strtolower(trim((string) $product->name));
                                
                                return match ($slug) {
                                    'tank', 'tanks', 'cylinder', 'cylinders' => in_array($cat, ['tank', 'tanks', 'cylinder', 'cylinders', 'lpg', 'lpg-tanks']) || str_contains($name, 'tank') || str_contains($name, 'cylinder'),
                                    'appliances', 'appliance' => in_array($cat, ['appliances', 'appliance', 'stove', 'burner', 'burners', 'kitchen']) || str_contains($name, 'stove') || str_contains($name, 'burner'),
                                    'accessories', 'accessory' => in_array($cat, ['accessories', 'accessory', 'tools', 'tool', 'hanger', 'hangers', 'parts', 'part']) || str_contains($name, 'regulator') || str_contains($name, 'hose') || str_contains($name, 'clamp'),
                                    'water' => in_array($cat, ['water', 'container', 'gallon']) || str_contains($name, 'water') || str_contains($name, 'gallon'),
                                    'dispensers' => in_array($cat, ['dispensers', 'dispenser', 'stands', 'rack']),
                                    'meals' => in_array($cat, ['meals', 'meal', 'rice']),
                                    'snacks' => in_array($cat, ['snacks', 'snack', 'finger']),
                                    'beverages' => in_array($cat, ['beverages', 'beverage', 'drink', 'drinks']),
                                    'bilao' => in_array($cat, ['bilao', 'tray', 'package']),
                                    default => ($cat === strtolower($slug)),
                                };
                            };

                            // Group inventories by configured niche categories
                            $groupedInventories = collect();
                            $handledInventoryIds = collect();

                            foreach ($nicheCats as $ncat) {
                                $matched = $inventories->filter(function ($inv) use ($ncat, $isMatchingCategory, $handledInventoryIds) {
                                    if ($handledInventoryIds->contains($inv->id)) return false;
                                    if (strtolower((string) ($inv->product?->category ?? '')) === 'freebie') return false;
                                    return $isMatchingCategory($inv->product, $ncat['slug']);
                                });

                                if ($matched->isNotEmpty()) {
                                    $groupedInventories->put($ncat['name'], $matched);
                                    $handledInventoryIds = $handledInventoryIds->concat($matched->pluck('id'));
                                }
                            }

                            // Any unhandled non-freebie inventories
                            $unhandled = $inventories->reject(function ($inv) use ($handledInventoryIds) {
                                return $handledInventoryIds->contains($inv->id) || strtolower((string) ($inv->product?->category ?? '')) === 'freebie';
                            });
                            if ($unhandled->isNotEmpty()) {
                                $groupedInventories->put('Other Products', $unhandled);
                            }

                            $inventoryRowCount = $groupedInventories->sum(fn($group) => $group->count()) + (!$freebies->isEmpty() ? $freebies->count() : 0);
                        @endphp

                        @if($inventoryRowCount === 0)
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No inventory records found.</td>
                            </tr>
                        @else
                            @foreach($groupedInventories as $groupTitle => $groupItems)
                                <tr class="table-secondary">
                                    <td colspan="6" class="fw-bold">{{ $groupTitle }}</td>
                                </tr>
                                @foreach($groupItems as $inventory)
                                    @php
                                        $stockLevel = $inventory->quantity_on_hand <= 0 ? 'out_of_stock' : ($inventory->quantity_on_hand <= 5 ? 'low_stock' : 'in_stock');
                                        $statusLabel = match ($stockLevel) {
                                            'out_of_stock' => 'Out of Stock',
                                            'low_stock' => 'Low Stock',
                                            default => 'In Stock',
                                        };
                                        $statusClass = match ($stockLevel) {
                                            'out_of_stock' => 'bg-danger',
                                            'low_stock' => 'bg-warning text-dark',
                                            default => 'bg-success',
                                        };
                                        $productImage = data_get($inventory->product, 'resolved_image');
                                        $isExchangeable = $inventory->supportsEmptyCylinderTracking();
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="rounded-circle bg-light overflow-hidden d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                                    @if($productImage)
                                                        <img src="{{ $productImage }}" alt="{{ data_get($inventory->product, 'name', 'Product Image') }}" style="width: 100%; height: 100%; object-fit: cover;">
                                                    @else
                                                        <i class="bi bi-box2-heart text-primary"></i>
                                                    @endif
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">{{ data_get($inventory->product, 'name', 'Unnamed Product') }}</div>
                                                    <div class="text-muted small">{{ ucfirst(data_get($inventory->product, 'category', 'General')) }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="fw-semibold">{{ (int) $inventory->quantity_on_hand }}</td>
                                        <td class="fw-semibold">
                                            @if($isExchangeable)
                                                {{ (int) $inventory->empty_on_hand }}
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td><span class="badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                                        <td class="text-muted small">{{ $inventory->updated_at ? $inventory->updated_at->format('M d, Y') : '—' }}</td>
                                        <td>
                                            <div class="inventory-card-actions">
                                                <button type="button" class="btn btn-sm btn-success" onclick="setAdjustInventory('{{ $inventory->id }}', '{{ addslashes($inventory->product->name) }}', {{ $isExchangeable ? 'true' : 'false' }}, '{{ addslashes($inventory->supplier ?? '') }}')" data-bs-toggle="modal" data-bs-target="#adjustStockModal">
                                                    <i class="bi bi-plus-circle me-1"></i>Add Stock
                                                </button>
                                                <a href="{{ route('admin.inventory.show', $inventory) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye me-1"></i>Details
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach

                            @if(!$freebies->isEmpty())
                                <tr class="table-secondary">
                                    <td colspan="6" class="fw-bold">Freebies / Promotional Items</td>
                                </tr>
                                @foreach($freebies as $freebie)
                                    @php
                                        $stockLevel = $freebie->stock <= 0 ? 'out_of_stock' : ($freebie->stock <= 5 ? 'low_stock' : 'in_stock');
                                        $statusLabel = match ($stockLevel) {
                                            'out_of_stock' => 'Out of Stock',
                                            'low_stock' => 'Low Stock',
                                            default => 'In Stock',
                                        };
                                        $statusClass = match ($stockLevel) {
                                            'out_of_stock' => 'bg-danger',
                                            'low_stock' => 'bg-warning text-dark',
                                            default => 'bg-success',
                                        };
                                        $productImage = $freebie->image_url;
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="rounded-circle bg-light overflow-hidden d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                                    @if($productImage)
                                                        <img src="{{ $productImage }}" alt="{{ $freebie->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                                    @else
                                                        <i class="bi bi-gift text-primary"></i>
                                                    @endif
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">{{ $freebie->name }}</div>
                                                    <div class="text-muted small">Freebie</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="fw-semibold">{{ (int) $freebie->stock }}</td>
                                        <td class="fw-semibold"><span class="text-muted">N/A</span></td>
                                        <td><span class="badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                                        <td class="text-muted small">{{ $freebie->updated_at ? $freebie->updated_at->format('M d, Y') : '—' }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#adjustStockModal" onclick="setAdjustFreebie({{ $freebie->id }}, '{{ addslashes($freebie->name) }}', '{{ (int)$freebie->stock }}', '{{ addslashes($freebie->supplier ?? '') }}')">
                                                <i class="bi bi-plus-circle me-1"></i>Add Stock
                                            </button>
                                            <a href="{{ route('admin.products', ['tab' => 'freebies']) }}" class="btn btn-sm btn-outline-secondary ms-1" title="Edit details">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                {{ $inventories->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-3">
                <div>
                    <h5 class="fw-bold mb-1"><i class="bi bi-arrow-left-right me-2 text-info"></i>Inventory Movement History</h5>
                    <p class="text-muted mb-0">{{ $movementSubtitle }}</p>
                </div>
                <div class="text-muted small">Latest {{ $movementSnapshot->count() }} records</div>
            </div>

            <form method="GET" action="{{ route('admin.inventory.index') }}" class="row g-3 mb-4 align-items-end">
                <div class="col-md-2">
                    <label for="movement_date_from" class="form-label small fw-semibold">From Date</label>
                    <input type="date" name="movement_date_from" id="movement_date_from" class="form-control" 
                           value="{{ request('movement_date_from') }}">
                </div>
                <div class="col-md-2">
                    <label for="movement_date_to" class="form-label small fw-semibold">To Date</label>
                    <input type="date" name="movement_date_to" id="movement_date_to" class="form-control" 
                           value="{{ request('movement_date_to') }}">
                </div>
                <div class="col-md-2">
                    <label for="movement_type" class="form-label small fw-semibold">Type</label>
                    <select name="movement_type" id="movement_type" class="form-select">
                        <option value="">All Types</option>
                        <option value="stock_in" {{ request('movement_type') === 'stock_in' ? 'selected' : '' }}>Stock In</option>
                        <option value="stock_out" {{ request('movement_type') === 'stock_out' ? 'selected' : '' }}>Stock Out</option>
                        <option value="sale" {{ request('movement_type') === 'sale' ? 'selected' : '' }}>Sale</option>
                        <option value="return" {{ request('movement_type') === 'return' ? 'selected' : '' }}>Customer Return</option>
                        <option value="damage" {{ request('movement_type') === 'damage' ? 'selected' : '' }}>Damage</option>
                        <option value="adjustment" {{ request('movement_type') === 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="movement_product_id" class="form-label small fw-semibold">Product</label>
                    <select name="movement_product_id" id="movement_product_id" class="form-select">
                        <option value="">All Products</option>
                        @foreach(($allProductsForMovements ?? []) as $prod)
                            <option value="{{ $prod->id }}" {{ request('movement_product_id') == $prod->id ? 'selected' : '' }}>
                                {{ $prod->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="movement_search" class="form-label small fw-semibold">Search</label>
                    <input type="text" name="movement_search" id="movement_search" class="form-control" 
                           placeholder="Ref / Notes / Creator" value="{{ request('movement_search') }}">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-filter me-1"></i>Filter</button>
                    @if(request()->hasAny(['movement_date_from', 'movement_date_to', 'movement_type', 'movement_product_id', 'movement_search']))
                        <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline-secondary" title="Reset Filters"><i class="bi bi-x-circle"></i></a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date / Time</th>
                            <th>Reference</th>
                            <th>Type</th>
                            <th>Product</th>
                            <th>Full In</th>
                            <th>Full Out</th>
                            <th>Empty In</th>
                            <th>Empty Out</th>
                            <th>Performed By</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movementSnapshot as $movement)
                            @php
                                $movementType = strtolower((string) ($movement->type ?? ''));
                                $movementLabel = match ($movementType) {
                                    'stock_in', 'purchase' => 'Supplier Restock',
                                    'sale' => 'Sale',
                                    'delivery' => 'Online Delivery',
                                    'exchange' => 'Exchange',
                                    'adjustment' => 'Adjustment',
                                    'damage', 'damaged' => 'Damaged',
                                    default => ucwords(str_replace('_', ' ', $movementType ?: 'Movement')),
                                };
                                $movementBadge = match ($movementType) {
                                    'stock_in', 'purchase' => 'bg-success',
                                    'sale' => 'bg-primary',
                                    'delivery' => 'bg-secondary',
                                    'exchange' => 'bg-info',
                                    'adjustment' => 'bg-warning text-dark',
                                    'damage', 'damaged' => 'bg-danger',
                                    default => 'bg-light text-dark',
                                };
                                $movementDate = $movement->movement_date ?? $movement->created_at;
                            @endphp
                            <tr>
                                <td class="text-muted small">{{ $movementDate ? $movementDate->format('M d, Y h:i A') : '—' }}</td>
                                <td class="fw-semibold">{{ $movement->reference ?: '—' }}</td>
                                <td><span class="badge {{ $movementBadge }}">{{ $movementLabel }}</span></td>
                                <td>{{ data_get($movement->inventory->product, 'name', 'Unknown Product') }}</td>
                                <td>{{ (int) $movement->full_in }}</td>
                                <td>{{ (int) $movement->full_out }}</td>
                                <td>{{ (int) $movement->empty_in }}</td>
                                <td>{{ (int) $movement->empty_out }}</td>
                                <td>{{ data_get($movement->creator, 'name', 'System') }}</td>
                                <td class="text-muted small">{{ $movement->notes ?: '—' }}</td>
                                <td>
                                    @php
                                        $notesLower = strtolower((string) $movement->notes);
                                        $isSaleType = ($movement->type === 'sale' || strtolower((string) $movement->type) === 'sale');
                                        $isCylinderProduct = ($movement->inventory?->product?->isCylinder() ?? false);

                                        // If notes don't indicate new cylinder, try resolving linked order
                                        $notesIndicateNew = str_contains($notesLower, 'new_cylinder') || str_contains($notesLower, 'new cylinder');
                                        $orderTransactionIsNew = false;
                                        if (!$notesIndicateNew && $movement->reference) {
                                            $order = \App\Models\Order::where('order_number', $movement->reference)
                                                ->orWhere('id', (int) filter_var($movement->reference, FILTER_SANITIZE_NUMBER_INT))
                                                ->first();
                                            if ($order) {
                                                $orderTransactionIsNew = (($order->transaction_type ?? '') === 'new_cylinder');
                                            }
                                        }

                                        $showMarkReturned = $isSaleType && $isCylinderProduct && ($notesIndicateNew || $orderTransactionIsNew);
                                    @endphp

                                    @if($showMarkReturned)
                                        <form method="POST" action="{{ route('admin.inventory.movement.mark-returned', $movement) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Mark Returned</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">No movement history yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="adjustStockModal" tabindex="-1" aria-labelledby="adjustStockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="adjustStockModalLabel"><i class="bi bi-plus-circle me-2"></i>Add Stock</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="adjustStockForm" method="POST">
                @csrf
                <input type="hidden" name="inventory_id" id="adjustInventoryId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Product</label>
                        <div id="adjustProductName" class="form-control-plaintext fw-bold"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Adjustment Type</label>
                        <select name="type" id="adjustType" class="form-select" required>
                            <option value="">Select type</option>
                            <option value="stock_in">Stock In (Full Cylinders Received)</option>
                            <option value="stock_out">Stock Out (Full Cylinders Released)</option>
                            <option value="empty_in">Empty Cylinders Received (Returned Empties)</option>
                            <option value="empty_out">Empty Cylinders Released (Sent for Refill)</option>
                            <option value="return">Customer Return</option>
                            <option value="damage">Damage / Defective</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Supplier</label>
                        <input type="text" name="supplier" id="adjustSupplier" class="form-control" placeholder="e.g. Solane / Petron / Prycegas">
                        <div class="form-text text-muted">Optional: Update or set the supplier for this inventory.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Reference No. <span class="text-danger">*</span></label>
                        <input type="text" name="reference" id="adjustReference" class="form-control" placeholder="e.g. ADJ-2026-001 / PO-9812" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="quantity_change" id="adjustQuantity" class="form-control" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Remarks / Notes <span class="text-danger">*</span></label>
                        <textarea name="notes" id="adjustNotes" class="form-control" rows="2" placeholder="Enter reason or details for this adjustment..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-save me-2"></i>Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function setAdjustInventory(inventoryId, productName, isCylinder = false, supplier = '') {
    document.getElementById('adjustInventoryId').value = inventoryId;
    document.getElementById('adjustProductName').textContent = productName;
    document.getElementById('adjustSupplier').value = supplier || '';
    
    const supplierBox = document.getElementById('adjustSupplier')?.closest('.mb-3');
    if (supplierBox) supplierBox.style.display = 'block';

    const select = document.getElementById('adjustType');
    select.innerHTML = '';
    
    const defaultOpt = document.createElement('option');
    defaultOpt.value = '';
    defaultOpt.textContent = 'Select type';
    select.appendChild(defaultOpt);

    const containerWord = "{{ $containerNameSingular }}";
    if (isCylinder) {
        const options = [
            { value: 'stock_in', label: `Stock In (Full ${containerWord}s Received / Restocked)` },
            { value: 'stock_out', label: `Stock Out (Full ${containerWord}s Released / Sold)` },
            { value: 'empty_in', label: `Empty ${containerWord}s Received (Returned Empties)` },
            { value: 'empty_out', label: `Empty ${containerWord}s Released (Sent for Refill / Supplier)` },
            { value: 'return', label: 'Customer Return' },
            { value: 'damage', label: 'Damage / Defective' }
        ];
        options.forEach(opt => {
            const el = document.createElement('option');
            el.value = opt.value;
            el.textContent = opt.label;
            select.appendChild(el);
        });
    } else {
        const options = [
            { value: 'stock_in', label: 'Stock In (Add Stock / Restock)' },
            { value: 'stock_out', label: 'Stock Out (Manual Stock Out)' },
            { value: 'return', label: 'Customer Return' },
            { value: 'damage', label: 'Damage / Defective' }
        ];
        options.forEach(opt => {
            const el = document.createElement('option');
            el.value = opt.value;
            el.textContent = opt.label;
            select.appendChild(el);
        });
    }

    select.value = '';
    document.getElementById('adjustReference').value = '';
    document.getElementById('adjustQuantity').value = '';
    document.getElementById('adjustNotes').value = '';
    document.getElementById('adjustStockForm').setAttribute('action', `/admin/inventory/${inventoryId}/adjust`);
}

function setAdjustFreebie(freebieId, freebieName, currentStock, supplier = '') {
    document.getElementById('adjustInventoryId').value = freebieId;
    document.getElementById('adjustProductName').innerHTML = `<span class="badge bg-warning text-dark me-2">Freebie</span> ${freebieName} <small class="text-muted">(Current: ${currentStock})</small>`;
    
    document.getElementById('adjustSupplier').value = supplier || '';
    const supplierBox = document.getElementById('adjustSupplier')?.closest('.mb-3');
    if (supplierBox) supplierBox.style.display = 'block';

    const select = document.getElementById('adjustType');
    select.innerHTML = '';

    const defaultOpt = document.createElement('option');
    defaultOpt.value = '';
    defaultOpt.textContent = 'Select type';
    select.appendChild(defaultOpt);

    const options = [
        { value: 'stock_in', label: 'Stock In (Add Stock / Freebies Received)' },
        { value: 'stock_out', label: 'Stock Out (Manual Stock Out)' },
        { value: 'return', label: 'Customer Return' },
        { value: 'damage', label: 'Damage / Defective / Lost' },
        { value: 'adjustment', label: 'Direct Stock Count Adjustment' }
    ];
    options.forEach(opt => {
        const el = document.createElement('option');
        el.value = opt.value;
        el.textContent = opt.label;
        select.appendChild(el);
    });

    select.value = 'stock_in';
    document.getElementById('adjustReference').value = 'FRB-STK-' + Date.now().toString().slice(-6);
    document.getElementById('adjustQuantity').value = '';
    document.getElementById('adjustNotes').value = '';
    document.getElementById('adjustStockForm').setAttribute('action', `/admin/inventory/freebies/${freebieId}/adjust`);
}

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('adjustStockForm');
    if (form) {
        form.addEventListener('submit', function (event) {
            const type = document.getElementById('adjustType').value;
            const quantity = document.getElementById('adjustQuantity').value;

            if (!type) {
                event.preventDefault();
                alert('Please select an adjustment type.');
                return;
            }

            if (!quantity || Number(quantity) < 1) {
                event.preventDefault();
                alert('Please enter a valid quantity.');
            }
        });
    }
});
</script>

@endsection
