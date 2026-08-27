@extends('layouts.admin')

@section('title', 'Products')
@section('nav-products', 'active')
@section('page-title', 'Product Management')

@section('admin-styles')
    <style>
        .product-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, .06);
            overflow: hidden;
            transition: transform .3s;
        }

        .product-card:hover {
            transform: translateY(-4px);
        }

        .product-card img {
            width: 100%;
            height: 300px;
            object-fit: contain;
            background-color: #fff;
            padding: 12px;
        }

        .product-card .card-body {
            padding: 18px;
        }

        .product-card .card-body h6 {
            font-weight: 700;
            color: var(--gasgo-blue);
        }

        .product-card .price {
            color: var(--gasgo-orange);
            font-weight: 700;
            font-size: 1.15rem;
        }

        .product-card .stock-badge {
            font-size: .72rem;
        }

        .modal-form label {
            font-weight: 600;
            font-size: .88rem;
            color: #555;
        }

        .modal-form .form-control,
        .modal-form .form-select {
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            padding: 10px 16px;
        }

        .modal-form .form-control:focus,
        .modal-form .form-select:focus {
            border-color: var(--gasgo-blue);
            box-shadow: none;
        }

        .section-tabs {
            display: flex;
            gap: 12px;
            margin-bottom: 28px;
            border-bottom: 2px solid #e0e0e0;
            flex-wrap: wrap;
        }

        .section-tab {
            padding: 12px 20px;
            font-weight: 600;
            font-size: .95rem;
            cursor: pointer;
            color: #999;
            border: none;
            background: none;
            border-bottom: 3px solid transparent;
            transition: all .3s;
            display: inline-flex;
            align-items: center;
        }

        .section-tab.active {
            color: var(--gasgo-blue);
            border-bottom-color: var(--gasgo-blue);
        }

        .freebie-card {
            background: linear-gradient(135deg, #fff3cd 0%, #fff9e6 100%);
            border: 2px solid #ffc107;
        }

        .freebie-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: #ffc107;
            color: #333;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .archived-card:hover {
            border-color: var(--gasgo-blue);
        }

        @media (max-width: 768px) {
            .section-tabs {
                display: flex !important;
                flex-wrap: nowrap !important;
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch;
                margin-bottom: 16px !important;
                gap: 6px !important;
                scrollbar-width: none;
            }
            .section-tabs::-webkit-scrollbar { display: none; }
            .section-tab {
                flex-shrink: 0;
                padding: 8px 12px;
                font-size: 0.82rem;
            }
            .product-card {
                padding: 14px 12px;
            }
            .product-card img {
                height: 120px;
            }
        }
    </style>
@endsection

@section('content')
    <div id="productsAlerts">
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="fw-bold mb-1">Please fix the following:</div>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    @php
        $resolveImageUrl = function (?string $path): ?string {
            if (!$path) {
                return null;
            }

            $normalized = ltrim($path, '/');

            if (str_starts_with($normalized, 'http://') || str_starts_with($normalized, 'https://')) {
                return $path;
            }

            if (str_starts_with($normalized, 'images/')) {
                return asset($normalized);
            }

            if (str_starts_with($normalized, 'storage/')) {
                $normalized = substr($normalized, 8);
            }

            return \Illuminate\Support\Facades\Storage::url($normalized);
        };

        $productsCollection = collect($products ?? []);
        $freebiesCollection = collect($freebies ?? []);

        // Active Products for Sale
        $saleProducts = $productsCollection
            ->filter(fn($p) => strtolower((string) ($p->category ?? '')) !== 'freebie' && (bool) $p->is_active)
            ->values();

        // Archived / Inactive Products
        $archivedProducts = $productsCollection
            ->filter(fn($p) => strtolower((string) ($p->category ?? '')) !== 'freebie' && !(bool) $p->is_active)
            ->values();

        $activeProductFreebies = $productsCollection
            ->filter(fn($p) => strtolower((string) ($p->category ?? '')) === 'freebie' && (bool) $p->is_active)
            ->values();

        $archivedProductFreebies = $productsCollection
            ->filter(fn($p) => strtolower((string) ($p->category ?? '')) === 'freebie' && !(bool) $p->is_active)
            ->values();

        $activeFreebies = $freebiesCollection
            ->filter(fn($f) => (bool) $f->is_active)
            ->values();

        $archivedFreebies = $freebiesCollection
            ->filter(fn($f) => !(bool) $f->is_active)
            ->values();

        $industryNoun = $settings->industry_noun ?? 'LPG Tanks';
        $isWaterNiche = str_contains(strtolower($industryNoun), 'water');
        $isFoodNiche = str_contains(strtolower($industryNoun), 'food') || str_contains(strtolower($industryNoun), 'meal');
        $isApplianceNiche = str_contains(strtolower($industryNoun), 'appliance');
        $nicheKey = \App\Services\CategoryService::detectNicheKey($industryNoun);
        $nicheConfig = \App\Services\CategoryService::getCurrentNicheConfig($industryNoun);
        $nicheItemNoun = $nicheConfig['item_noun'] ?? 'Product';
        $nicheInfoTitle = $nicheConfig['info_section_title'] ?? 'Product Information & Specs';
        $nicheName = $nicheConfig['name'] ?? 'Store Item';
        $nicheIcon = match(true) {
            $isApplianceNiche => 'fas fa-blender',
            $isWaterNiche => 'fas fa-tint',
            $isFoodNiche => 'fas fa-utensils',
            default => 'fas fa-fire',
        };

        $isTankCategory = function ($cat) {
            return in_array(strtolower(trim((string) $cat)), ['tank', 'tanks', 'cylinder', 'cylinders', 'lpg', 'lpg-tanks', 'lpg tank', 'lpg tanks', 'water', 'container', 'gallon', 'food', 'foods', 'meal', 'meals']);
        };

        $isAccessoryCategory = function ($cat) {
            return in_array(strtolower(trim((string) $cat)), ['accessories', 'accessory', 'tools', 'tool', 'hanger', 'hangers', 'hardware', 'part', 'parts']);
        };

        $isApplianceCategory = function ($cat) {
            return in_array(strtolower(trim((string) $cat)), ['appliances', 'appliance', 'stove', 'burner', 'burners', 'kitchen']);
        };

        $lpgTankProducts = $saleProducts
            ->filter(fn($p) => $isTankCategory($p->category))
            ->values();

        $accessoriesProducts = $saleProducts
            ->filter(fn($p) => $isAccessoryCategory($p->category))
            ->values();

        $appliancesProducts = $saleProducts
            ->filter(fn($p) => $isApplianceCategory($p->category))
            ->values();

        $otherProducts = $saleProducts
            ->reject(fn($p) => $isTankCategory($p->category) || $isAccessoryCategory($p->category) || $isApplianceCategory($p->category))
            ->values();

        // Active Freebies Catalog
        $freebieCatalog = $activeFreebies
            ->map(function ($f) {
                return [
                    'source' => 'freebie',
                    'item' => $f,
                    'name' => $f->name,
                    'description' => $f->description,
                    'stock' => (int) ($f->stock ?? 0),
                    'image' => $f->image,
                ];
            })
            ->concat(
                $activeProductFreebies->map(function ($p) {
                    return [
                        'source' => 'product',
                        'item' => $p,
                        'name' => $p->name,
                        'description' => $p->description,
                        'stock' => (int) ($p->quantity_on_hand ?? 0),
                        'image' => $p->image,
                    ];
                })
            )
            ->values();

        // Archived Catalog (products + freebies)
        $archivedCatalog = $archivedProducts
            ->map(function ($p) {
                return [
                    'type' => 'product',
                    'item' => $p,
                    'id' => $p->id,
                    'name' => $p->name,
                    'category' => $p->category ?? 'tank',
                    'price' => $p->price ?? $p->selling_price ?? 0,
                    'stock' => (int) ($p->quantity_on_hand ?? $p->stock ?? 0),
                    'image' => $p->image,
                    'description' => $p->description,
                    'orders_count' => $p->orderItems ? $p->orderItems->count() : 0,
                    'restore_url' => route('admin.products.restore', $p),
                    'delete_url' => route('admin.products.destroy', $p),
                ];
            })
            ->concat(
                $archivedProductFreebies->map(function ($p) {
                    return [
                        'type' => 'product',
                        'item' => $p,
                        'id' => $p->id,
                        'name' => $p->name,
                        'category' => 'freebie',
                        'price' => $p->price ?? 0,
                        'stock' => (int) ($p->quantity_on_hand ?? $p->stock ?? 0),
                        'image' => $p->image,
                        'description' => $p->description,
                        'orders_count' => $p->orderItems ? $p->orderItems->count() : 0,
                        'restore_url' => route('admin.products.restore', $p),
                        'delete_url' => route('admin.products.destroy', $p),
                    ];
                })
            )
            ->concat(
                $archivedFreebies->map(function ($f) {
                    return [
                        'type' => 'freebie',
                        'item' => $f,
                        'id' => $f->id,
                        'name' => $f->name,
                        'category' => 'freebie',
                        'price' => 0,
                        'stock' => (int) ($f->stock ?? 0),
                        'image' => $f->image,
                        'description' => $f->description,
                        'orders_count' => 0,
                        'restore_url' => route('admin.freebies.restore', $f),
                        'delete_url' => route('admin.freebies.destroy', $f),
                    ];
                })
            )
            ->values();
    @endphp

    <!-- Section Tabs -->
    <div class="section-tabs">
        <button class="section-tab active" data-section="products" onclick="switchSection('products', this)">
            <i class="fas fa-bag-shopping me-2"></i>Products For Sale
            <span class="badge bg-primary rounded-pill ms-2" style="font-size:0.75rem;">{{ $saleProducts->count() }}</span>
        </button>
        <button class="section-tab" data-section="freebies" onclick="switchSection('freebies', this)">
            <i class="fas fa-gift me-2"></i>Freebies
            <span class="badge bg-warning text-dark rounded-pill ms-2"
                style="font-size:0.75rem;">{{ $freebieCatalog->count() }}</span>
        </button>
        <button class="section-tab" data-section="archived" onclick="switchSection('archived', this)">
            <i class="fas fa-archive me-2"></i>Archived Products
            <span class="badge bg-secondary rounded-pill ms-2" style="font-size:0.75rem;"
                id="archivedTabCount">{{ $archivedCatalog->count() }}</span>
        </button>
    </div>

    <!-- Products Section -->
    <div id="productsSection" class="section-content">
        <!-- Top Actions -->
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h5 class="fw-bold" style="color:var(--gasgo-blue);margin-bottom:10px;">Products For Sale</h5>
                <p class="text-muted mb-0" style="font-size:.88rem;">Manage active products that customers can view and
                    purchase</p>
            </div>
            <button class="btn"
                style="background:var(--gasgo-orange);color:#fff;border-radius:12px;font-weight:600;padding:10px 22px;"
                data-bs-toggle="modal" data-bs-target="#productModal" onclick="openAddProduct()">
                <i class="fas fa-plus me-2"></i>Add {{ $nicheItemNoun }}
            </button>
        </div>

        <!-- Products Grid -->
        <div class="row g-4" id="productsGrid">
            <!-- Primary Products Section -->
            @if($lpgTankProducts->count() > 0)
                <div class="col-12">
                    <div
                        style="display:flex;align-items:center;gap:12px;margin-bottom:20px;padding-bottom:12px;border-bottom:2px solid #e0e0e0;">
                        <i class="{{ $isWaterNiche ? 'fas fa-tint' : ($isFoodNiche ? 'fas fa-utensils' : ($isApplianceNiche ? 'fas fa-blender' : 'fas fa-fire')) }}" style="font-size:1.5rem;color:var(--gasgo-orange);"></i>
                        <h6 style="margin:0;color:var(--gasgo-blue);font-weight:700;font-size:1.1rem;">{{ $industryNoun }}</h6>
                        <span
                            style="margin-left:auto;background:#f0f0f0;padding:6px 12px;border-radius:20px;font-size:0.85rem;color:#666;font-weight:600;">{{ $lpgTankProducts->count() }}
                            items</span>
                    </div>
                </div>
                @foreach($lpgTankProducts as $product)
                    <div class="col-lg-3 col-md-4 col-sm-6 product-item">
                        <div class="product-card">
                            @php $productImageUrl = $resolveImageUrl($product->image); @endphp
                            @php $qty = (int) ($product->quantity_on_hand ?? 0); @endphp
                            @if($productImageUrl)
                                <img src="{{ $productImageUrl }}" alt="{{ $product->name }}">
                            @else
                                <div
                                    style="width:100%;height:240px;background:linear-gradient(135deg,var(--gasgo-blue-light),#fff);display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-box" style="font-size:3rem;color:var(--gasgo-blue);opacity:.5;"></i>
                                </div>
                            @endif
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0">{{ $product->name }}</h6>
                                    @if($qty == 0)
                                        <span class="badge bg-danger stock-badge">Out</span>
                                    @elseif($qty <= 5)
                                        <span class="badge bg-warning text-dark stock-badge">Low Stock</span>
                                    @else
                                        <span class="badge bg-success stock-badge">In Stock</span>
                                    @endif
                                </div>
                                <p class="text-muted mb-2" style="font-size:.82rem;">{{ $product->description ?? 'No description' }}
                                </p>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="price">₱{{ number_format($product->price, 2) }}</span>
                                    <span class="text-muted" style="font-size:.82rem;">Stock: <strong
                                            class="{{ $qty <= 5 ? 'text-danger' : '' }}">{{ $qty }}</strong></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3" style="font-size:.78rem;">
                                    <span class="text-muted">Cost: <strong class="text-dark">₱{{ number_format($product->cost_price ?? 0, 2) }}</strong></span>
                                    @php
                                        $margin = ($product->price ?? 0) - ($product->cost_price ?? 0);
                                        $marginPct = ($product->price > 0) ? round(($margin / $product->price) * 100, 1) : 0;
                                    @endphp
                                    <span class="badge {{ $margin >= 0 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' }}">
                                        {{ $margin >= 0 ? '+' : '' }}₱{{ number_format($margin, 2) }} ({{ $marginPct }}%)
                                    </span>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm flex-grow-1"
                                        style="background:var(--gasgo-blue);color:#fff;border-radius:8px;font-weight:600;"
                                        data-bs-toggle="modal" data-bs-target="#productModal" onclick="openEditProduct(this)"
                                        data-id="{{ $product->id }}" data-name="{{ $product->name }}"
                                        data-category="{{ $product->category ?? 'tank' }}"
                                        data-description="{{ $product->description }}"
                                        data-cost-price="{{ $product->cost_price ?? 0 }}"
                                        data-selling-price="{{ $product->selling_price ?? $product->price ?? 0 }}"
                                        data-stock="{{ $qty }}"
                                        data-reorder-level="{{ $product->inventory->reorder_level ?? 5 }}"
                                        data-weight="{{ $product->weight }}"
                                        data-image-url="{{ $productImageUrl ?? '' }}"
                                        data-requires-exchange="{{ $product->requires_exchange ? '1' : '0' }}"
                                        data-is-active="{{ $product->is_active ? '1' : '0' }}"
                                        data-update-url="{{ route('admin.products.update', $product) }}"><i
                                            class="fas fa-edit me-1"></i>Edit</button>
                                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST"
                                        data-confirm="Delete this product? If it is linked to past orders, it will be moved to Archived Products safely.">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm" type="submit"
                                            style="background:#f8d7da;color:#dc3545;border-radius:8px;" title="Delete"><i
                                                class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

            <!-- Accessories Section -->
            @if($accessoriesProducts->count() > 0)
                <div class="col-12">
                    <div
                        style="display:flex;align-items:center;gap:12px;margin-bottom:20px;padding-bottom:12px;border-bottom:2px solid #e0e0e0;margin-top:20px;">
                        <i class="fas fa-tools" style="font-size:1.5rem;color:#6C757D;"></i>
                        <h6 style="margin:0;color:var(--gasgo-blue);font-weight:700;font-size:1.1rem;">Accessories</h6>
                        <span
                            style="margin-left:auto;background:#f0f0f0;padding:6px 12px;border-radius:20px;font-size:0.85rem;color:#666;font-weight:600;">{{ $accessoriesProducts->count() }}
                            items</span>
                    </div>
                </div>
                @foreach($accessoriesProducts as $product)
                    <div class="col-lg-3 col-md-4 col-sm-6 product-item">
                        <div class="product-card">
                            @php $productImageUrl = $resolveImageUrl($product->image); @endphp
                            @php $qty = (int) ($product->quantity_on_hand ?? 0); @endphp
                            @if($productImageUrl)
                                <img src="{{ $productImageUrl }}" alt="{{ $product->name }}">
                            @else
                                <div
                                    style="width:100%;height:240px;background:linear-gradient(135deg,var(--gasgo-blue-light),#fff);display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-box" style="font-size:3rem;color:var(--gasgo-blue);opacity:.5;"></i>
                                </div>
                            @endif
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0">{{ $product->name }}</h6>
                                    @if($qty == 0)
                                        <span class="badge bg-danger stock-badge">Out</span>
                                    @elseif($qty <= 5)
                                        <span class="badge bg-warning text-dark stock-badge">Low Stock</span>
                                    @else
                                        <span class="badge bg-success stock-badge">In Stock</span>
                                    @endif
                                </div>
                                <p class="text-muted mb-2" style="font-size:.82rem;">{{ $product->description ?? 'No description' }}
                                </p>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="price">₱{{ number_format($product->price, 2) }}</span>
                                    <span class="text-muted" style="font-size:.82rem;">Stock: <strong
                                            class="{{ $qty <= 5 ? 'text-danger' : '' }}">{{ $qty }}</strong></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3" style="font-size:.78rem;">
                                    <span class="text-muted">Cost: <strong class="text-dark">₱{{ number_format($product->cost_price ?? 0, 2) }}</strong></span>
                                    @php
                                        $margin = ($product->price ?? 0) - ($product->cost_price ?? 0);
                                        $marginPct = ($product->price > 0) ? round(($margin / $product->price) * 100, 1) : 0;
                                    @endphp
                                    <span class="badge {{ $margin >= 0 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' }}">
                                        {{ $margin >= 0 ? '+' : '' }}₱{{ number_format($margin, 2) }} ({{ $marginPct }}%)
                                    </span>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm flex-grow-1"
                                        style="background:var(--gasgo-blue);color:#fff;border-radius:8px;font-weight:600;"
                                        data-bs-toggle="modal" data-bs-target="#productModal" onclick="openEditProduct(this)"
                                        data-id="{{ $product->id }}" data-name="{{ $product->name }}"
                                        data-category="{{ $product->category ?? 'accessories' }}"
                                        data-description="{{ $product->description }}"
                                        data-cost-price="{{ $product->cost_price ?? 0 }}"
                                        data-selling-price="{{ $product->selling_price ?? $product->price ?? 0 }}"
                                        data-stock="{{ $qty }}"
                                        data-reorder-level="{{ $product->inventory->reorder_level ?? 5 }}"
                                        data-weight="{{ $product->weight }}"
                                        data-image-url="{{ $productImageUrl ?? '' }}"
                                        data-requires-exchange="{{ $product->requires_exchange ? '1' : '0' }}"
                                        data-is-active="{{ $product->is_active ? '1' : '0' }}"
                                        data-update-url="{{ route('admin.products.update', $product) }}"><i
                                            class="fas fa-edit me-1"></i>Edit</button>
                                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST"
                                        data-confirm="Delete this product? If it is linked to past orders, it will be moved to Archived Products safely.">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm" type="submit"
                                            style="background:#f8d7da;color:#dc3545;border-radius:8px;" title="Delete"><i
                                                class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

            <!-- Appliances Section -->
            @if($appliancesProducts->count() > 0)
                <div class="col-12">
                    <div
                        style="display:flex;align-items:center;gap:12px;margin-bottom:20px;padding-bottom:12px;border-bottom:2px solid #e0e0e0;margin-top:20px;">
                        <i class="fas fa-blender" style="font-size:1.5rem;color:#e74c3c;"></i>
                        <h6 style="margin:0;color:var(--gasgo-blue);font-weight:700;font-size:1.1rem;">Appliances</h6>
                        <span
                            style="margin-left:auto;background:#f0f0f0;padding:6px 12px;border-radius:20px;font-size:0.85rem;color:#666;font-weight:600;">{{ $appliancesProducts->count() }}
                            items</span>
                    </div>
                </div>
                @foreach($appliancesProducts as $product)
                    <div class="col-lg-3 col-md-4 col-sm-6 product-item">
                        <div class="product-card">
                            @php $productImageUrl = $resolveImageUrl($product->image); @endphp
                            @php $qty = (int) ($product->quantity_on_hand ?? 0); @endphp
                            @if($productImageUrl)
                                <img src="{{ $productImageUrl }}" alt="{{ $product->name }}">
                            @else
                                <div
                                    style="width:100%;height:240px;background:linear-gradient(135deg,var(--gasgo-blue-light),#fff);display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-box" style="font-size:3rem;color:var(--gasgo-blue);opacity:.5;"></i>
                                </div>
                            @endif
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0">{{ $product->name }}</h6>
                                    @if($qty == 0)
                                        <span class="badge bg-danger stock-badge">Out</span>
                                    @elseif($qty <= 5)
                                        <span class="badge bg-warning text-dark stock-badge">Low Stock</span>
                                    @else
                                        <span class="badge bg-success stock-badge">In Stock</span>
                                    @endif
                                </div>
                                <p class="text-muted mb-2" style="font-size:.82rem;">{{ $product->description ?? 'No description' }}
                                </p>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="price">₱{{ number_format($product->price, 2) }}</span>
                                    <span class="text-muted" style="font-size:.82rem;">Stock: <strong
                                            class="{{ $qty <= 5 ? 'text-danger' : '' }}">{{ $qty }}</strong></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3" style="font-size:.78rem;">
                                    <span class="text-muted">Cost: <strong class="text-dark">₱{{ number_format($product->cost_price ?? 0, 2) }}</strong></span>
                                    @php
                                        $margin = ($product->price ?? 0) - ($product->cost_price ?? 0);
                                        $marginPct = ($product->price > 0) ? round(($margin / $product->price) * 100, 1) : 0;
                                    @endphp
                                    <span class="badge {{ $margin >= 0 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' }}">
                                        {{ $margin >= 0 ? '+' : '' }}₱{{ number_format($margin, 2) }} ({{ $marginPct }}%)
                                    </span>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm flex-grow-1"
                                        style="background:var(--gasgo-blue);color:#fff;border-radius:8px;font-weight:600;"
                                        data-bs-toggle="modal" data-bs-target="#productModal" onclick="openEditProduct(this)"
                                        data-id="{{ $product->id }}" data-name="{{ $product->name }}"
                                        data-category="{{ $product->category ?? 'appliances' }}"
                                        data-description="{{ $product->description }}"
                                        data-cost-price="{{ $product->cost_price ?? 0 }}"
                                        data-selling-price="{{ $product->selling_price ?? $product->price ?? 0 }}"
                                        data-stock="{{ $qty }}"
                                        data-reorder-level="{{ $product->inventory->reorder_level ?? 5 }}"
                                        data-weight="{{ $product->weight }}"
                                        data-image-url="{{ $productImageUrl ?? '' }}"
                                        data-requires-exchange="{{ $product->requires_exchange ? '1' : '0' }}"
                                        data-is-active="{{ $product->is_active ? '1' : '0' }}"
                                        data-update-url="{{ route('admin.products.update', $product) }}"><i
                                            class="fas fa-edit me-1"></i>Edit</button>
                                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST"
                                        data-confirm="Delete this product? If it is linked to past orders, it will be moved to Archived Products safely.">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm" type="submit"
                                            style="background:#f8d7da;color:#dc3545;border-radius:8px;" title="Delete"><i
                                                class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

            <!-- Other Products Section -->
            @if($otherProducts->count() > 0)
                <div class="col-12">
                    <div
                        style="display:flex;align-items:center;gap:12px;margin-bottom:20px;padding-bottom:12px;border-bottom:2px solid #e0e0e0;margin-top:20px;">
                        <i class="fas fa-boxes-stacked" style="font-size:1.5rem;color:#8e44ad;"></i>
                        <h6 style="margin:0;color:var(--gasgo-blue);font-weight:700;font-size:1.1rem;">Other Products</h6>
                        <span
                            style="margin-left:auto;background:#f0f0f0;padding:6px 12px;border-radius:20px;font-size:0.85rem;color:#666;font-weight:600;">{{ $otherProducts->count() }}
                            items</span>
                    </div>
                </div>
                @foreach($otherProducts as $product)
                    <div class="col-lg-3 col-md-4 col-sm-6 product-item">
                        <div class="product-card">
                            @php $productImageUrl = $resolveImageUrl($product->image); @endphp
                            @php $qty = (int) ($product->quantity_on_hand ?? 0); @endphp
                            @if($productImageUrl)
                                <img src="{{ $productImageUrl }}" alt="{{ $product->name }}">
                            @else
                                <div
                                    style="width:100%;height:240px;background:linear-gradient(135deg,var(--gasgo-blue-light),#fff);display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-box" style="font-size:3rem;color:var(--gasgo-blue);opacity:.5;"></i>
                                </div>
                            @endif
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0">{{ $product->name }}</h6>
                                    @if($qty == 0)
                                        <span class="badge bg-danger stock-badge">Out</span>
                                    @elseif($qty <= 5)
                                        <span class="badge bg-warning text-dark stock-badge">Low Stock</span>
                                    @else
                                        <span class="badge bg-success stock-badge">In Stock</span>
                                    @endif
                                </div>
                                <p class="text-muted mb-2" style="font-size:.82rem;">{{ $product->description ?? 'No description' }}
                                </p>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="price">₱{{ number_format($product->price, 2) }}</span>
                                    <span class="text-muted" style="font-size:.82rem;">Stock: <strong
                                            class="{{ $qty <= 5 ? 'text-danger' : '' }}">{{ $qty }}</strong></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3" style="font-size:.78rem;">
                                    <span class="text-muted">Cost: <strong class="text-dark">₱{{ number_format($product->cost_price ?? 0, 2) }}</strong></span>
                                    @php
                                        $margin = ($product->price ?? 0) - ($product->cost_price ?? 0);
                                        $marginPct = ($product->price > 0) ? round(($margin / $product->price) * 100, 1) : 0;
                                    @endphp
                                    <span class="badge {{ $margin >= 0 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' }}">
                                        {{ $margin >= 0 ? '+' : '' }}₱{{ number_format($margin, 2) }} ({{ $marginPct }}%)
                                    </span>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm flex-grow-1"
                                        style="background:var(--gasgo-blue);color:#fff;border-radius:8px;font-weight:600;"
                                        data-bs-toggle="modal" data-bs-target="#productModal" onclick="openEditProduct(this)"
                                        data-id="{{ $product->id }}" data-name="{{ $product->name }}"
                                        data-category="{{ $product->category ?? 'accessories' }}"
                                        data-description="{{ $product->description }}"
                                        data-cost-price="{{ $product->cost_price ?? 0 }}"
                                        data-selling-price="{{ $product->selling_price ?? $product->price ?? 0 }}"
                                        data-stock="{{ $qty }}"
                                        data-reorder-level="{{ $product->inventory->reorder_level ?? 5 }}"
                                        data-weight="{{ $product->weight }}"
                                        data-image-url="{{ $productImageUrl ?? '' }}"
                                        data-requires-exchange="{{ $product->requires_exchange ? '1' : '0' }}"
                                        data-is-active="{{ $product->is_active ? '1' : '0' }}"
                                        data-update-url="{{ route('admin.products.update', $product) }}"><i
                                            class="fas fa-edit me-1"></i>Edit</button>
                                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST"
                                        data-confirm="Delete this product? If it is linked to past orders, it will be moved to Archived Products safely.">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm" type="submit"
                                            style="background:#f8d7da;color:#dc3545;border-radius:8px;" title="Delete"><i
                                                class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

            <!-- Empty State -->
            @if($saleProducts->count() === 0)
                <div class="col-12">
                    <p class="text-muted text-center py-5">No active products found. <a href="#" onclick="openAddProduct()"
                            class="text-decoration-none">Add your first product</a></p>
                </div>
            @endif
        </div>
    </div>

    <!-- Freebies Section -->
    <div id="freebiesSection" class="section-content" style="display:none;">
        <!-- Top Actions -->
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h5 class="fw-bold" style="color:#ffc107;margin-bottom:10px;">Freebies & Rewards</h5>
                <p class="text-muted mb-0" style="font-size:.88rem;">Manage active promotional items and loyalty rewards</p>
            </div>
            <button class="btn"
                style="background:var(--gasgo-orange);color:#fff;border-radius:12px;font-weight:600;padding:10px 22px;"
                data-bs-toggle="modal" data-bs-target="#freebieModal" onclick="openAddFreebie()">
                <i class="fas fa-plus me-2"></i>Add Freebie
            </button>
        </div>

        <!-- Freebies Grid -->
        <div class="row g-4" id="freebiesGrid">
            @forelse($freebieCatalog as $row)
                @php
                    $freebie = $row['item'];
                    $isProductFreebie = $row['source'] === 'product';
                @endphp
                <div class="col-lg-3 col-md-4 col-sm-6 freebie-item">
                    <div class="product-card freebie-card" style="position:relative;">
                        <span class="freebie-badge"><i class="fas fa-star me-1"></i>FREEBIE</span>
                        @php $freebieImageUrl = $resolveImageUrl($freebie->image); @endphp
                        @if($freebieImageUrl)
                            <img src="{{ $freebieImageUrl }}" alt="{{ $freebie->name }}" style="height:240px;">
                        @else
                            <div
                                style="width:100%;height:240px;background:linear-gradient(135deg,#ffe8a8,#fff5d9);display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-gift" style="font-size:3rem;color:#ffc107;opacity:.6;"></i>
                            </div>
                        @endif
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-0">{{ $freebie->name }} @if($isProductFreebie)<small class="text-muted"
                                style="font-size:.72rem;">(Product)</small>@endif</h6>
                                @if($freebie->stock <= 5)
                                    <span class="badge bg-warning text-dark stock-badge">Low</span>
                                @elseif($freebie->stock == 0)
                                    <span class="badge bg-danger stock-badge">Out</span>
                                @else
                                    <span class="badge bg-success stock-badge">OK</span>
                                @endif
                            </div>
                            <p class="text-muted mb-2" style="font-size:.82rem;">
                                {{ $freebie->description ?? 'Complimentary item' }}</p>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="price"
                                    style="color:#ffc107;">{{ $isProductFreebie ? '₱' . number_format((float) ($freebie->price ?? 0), 2) : 'FREE' }}</span>
                                <span class="text-muted" style="font-size:.82rem;">Stock: <strong
                                        class="{{ $freebie->stock <= 5 ? 'text-danger' : '' }}">{{ $freebie->stock }}</strong></span>
                            </div>
                            <div class="d-flex gap-2">
                                @if($isProductFreebie)
                                    @php $qty = (int) ($freebie->quantity_on_hand ?? 0); @endphp
                                    <button class="btn btn-sm flex-grow-1"
                                        style="background:var(--gasgo-blue);color:#fff;border-radius:8px;font-weight:600;"
                                        data-bs-toggle="modal" data-bs-target="#productModal" onclick="openEditProduct(this)"
                                        data-id="{{ $freebie->id }}" data-name="{{ $freebie->name }}"
                                        data-category="{{ $freebie->category ?? 'freebie' }}"
                                        data-description="{{ $freebie->description }}"
                                        data-cost-price="{{ $freebie->cost_price ?? 0 }}"
                                        data-selling-price="{{ $freebie->selling_price ?? $freebie->price ?? 0 }}"
                                        data-stock="{{ $qty }}"
                                        data-reorder-level="{{ $freebie->inventory->reorder_level ?? 5 }}"
                                        data-weight="{{ $freebie->weight }}"
                                        data-image-url="{{ $freebieImageUrl ?? '' }}"
                                        data-requires-exchange="0"
                                        data-is-active="{{ $freebie->is_active ? '1' : '0' }}"
                                        data-update-url="{{ route('admin.products.update', $freebie) }}"><i
                                            class="fas fa-edit me-1"></i>Edit</button>
                                    <form action="{{ route('admin.products.destroy', $freebie) }}" method="POST"
                                        data-confirm="Delete this item? If it is linked to past orders, it will be moved to Archived Products safely.">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm" type="submit"
                                            style="background:#f8d7da;color:#dc3545;border-radius:8px;" title="Delete"><i
                                                class="fas fa-trash"></i></button>
                                    </form>
                                @else
                                    <button class="btn btn-sm flex-grow-1"
                                        style="background:var(--gasgo-blue);color:#fff;border-radius:8px;font-weight:600;"
                                        data-bs-toggle="modal" data-bs-target="#freebieModal" onclick="openEditFreebie(this)"
                                        data-id="{{ $freebie->id }}" data-name="{{ $freebie->name }}"
                                        data-description="{{ $freebie->description }}" data-stock="{{ $freebie->stock }}"
                                        data-category="{{ $freebie->category }}"
                                        data-reward-points="{{ $freebie->reward_points_required }}"
                                        data-redemption-type="{{ $freebie->redemption_type }}"
                                        data-is-active="{{ $freebie->is_active ? '1' : '0' }}"
                                        data-update-url="{{ route('admin.freebies.update', $freebie) }}"><i
                                            class="fas fa-edit me-1"></i>Edit</button>
                                    <form action="{{ route('admin.freebies.destroy', $freebie) }}" method="POST"
                                        data-confirm="Are you sure you want to delete this freebie?">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm" type="submit"
                                            style="background:#f8d7da;color:#dc3545;border-radius:8px;" title="Delete"><i
                                                class="fas fa-trash"></i></button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <p class="text-muted text-center py-5">No freebies found. <a href="#" onclick="openAddFreebie()"
                            class="text-decoration-none">Add your first freebie</a></p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Archived Products Section -->
    <div id="archivedSection" class="section-content" style="display:none;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h5 class="fw-bold" style="color:#475569;margin-bottom:6px;"><i
                        class="fas fa-archive me-2 text-secondary"></i>Archived Products & Items</h5>
                <p class="text-muted mb-0" style="font-size:.88rem;">Products that are preserved to protect historical
                    customer orders and sales logs. You can restore them to active anytime.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="input-group input-group-sm" style="width:260px;">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="searchArchived" class="form-control border-start-0"
                        placeholder="Search archived..." onkeyup="filterArchived()">
                </div>
            </div>
        </div>

        <!-- Archived Grid -->
        <div class="row g-4" id="archivedGrid">
            @forelse($archivedCatalog as $item)
                <div class="col-lg-3 col-md-4 col-sm-6 archived-item" data-name="{{ strtolower($item['name']) }}">
                    <div class="product-card archived-card" style="position:relative;">
                        @php $itemImageUrl = $resolveImageUrl($item['image']); @endphp
                        @if($itemImageUrl)
                            <img src="{{ $itemImageUrl }}" alt="{{ $item['name'] }}" style="filter: grayscale(40%); opacity: 0.85;">
                        @else
                            <div
                                style="width:100%;height:240px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-box-archive" style="font-size:3rem;color:#94a3b8;"></i>
                            </div>
                        @endif
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-0 text-truncate" title="{{ $item['name'] }}">{{ $item['name'] }}</h6>
                                <span class="badge bg-secondary stock-badge"><i class="fas fa-archive me-1"></i>Archived</span>
                            </div>
                            <p class="text-muted mb-2 text-truncate" style="font-size:.82rem;">
                                {{ $item['description'] ?? 'No description' }}</p>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="price"
                                    style="color:#64748b;font-size:1.05rem;">₱{{ number_format((float) ($item['price'] ?? 0), 2) }}</span>
                                <span class="badge bg-light text-dark border"
                                    style="font-size:.72rem;">{{ ucfirst($item['category']) }}</span>
                            </div>
                            <div class="mb-3">
                                @if($item['orders_count'] > 0)
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle"
                                        style="font-size:.72rem;">
                                        <i class="fas fa-receipt me-1"></i>Linked to {{ $item['orders_count'] }} order(s)
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted border" style="font-size:.72rem;">
                                        <i class="fas fa-info-circle me-1"></i>No past orders
                                    </span>
                                @endif
                            </div>
                            <div class="d-flex gap-2">
                                @if($item['type'] === 'product')
                                    <button class="btn btn-sm flex-grow-1"
                                        style="background:var(--gasgo-blue);color:#fff;border-radius:8px;font-weight:600;"
                                        data-bs-toggle="modal" data-bs-target="#restoreProductModal"
                                        onclick="openRestoreProductModal('{{ addslashes($item['name']) }}', '{{ $item['restore_url'] }}')"><i
                                            class="fas fa-undo me-1"></i>Bring Back</button>
                                    @if($item['orders_count'] === 0)
                                        <form action="{{ $item['delete_url'] }}" method="POST"
                                            data-confirm="Permanently delete this product? This action cannot be undone.">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm" type="submit"
                                                style="background:#f8d7da;color:#dc3545;border-radius:8px;"
                                                title="Permanently Delete"><i class="fas fa-trash"></i></button>
                                        </form>
                                    @else
                                        <button class="btn btn-sm" type="button"
                                            style="background:#f1f5f9;color:#94a3b8;border-radius:8px;cursor:not-allowed;"
                                            title="Protected: This product is part of previous customer orders and cannot be permanently removed."
                                            disabled><i class="fas fa-lock"></i></button>
                                    @endif
                                @else
                                    <form action="{{ $item['restore_url'] }}" method="POST" class="flex-grow-1">
                                        @csrf
                                        <button class="btn btn-sm w-100"
                                            style="background:var(--gasgo-blue);color:#fff;border-radius:8px;font-weight:600;"
                                            type="submit">
                                            <i class="fas fa-undo me-1"></i>Bring Back
                                        </button>
                                    </form>
                                    <form action="{{ $item['delete_url'] }}" method="POST"
                                        data-confirm="Permanently delete this freebie?">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm" type="submit"
                                            style="background:#f8d7da;color:#dc3545;border-radius:8px;" title="Delete"><i
                                                class="fas fa-trash"></i></button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center py-5" style="background:#fff;border-radius:16px;border:1px dashed #cbd5e1;">
                        <i class="fas fa-box-open" style="font-size:2.8rem;color:#94a3b8;margin-bottom:12px;"></i>
                        <h6 class="fw-bold text-muted">No Archived Products</h6>
                        <p class="text-muted mb-0" style="font-size:.85rem;">When products linked to previous orders are
                            removed, they will be safely kept here.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Restore Product Modal -->
    <div class="modal fade" id="restoreProductModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius:16px;">
                <div class="modal-header" style="border-bottom:none;padding:24px 24px 0;">
                    <h5 class="modal-title fw-bold" style="color:var(--gasgo-blue);"><i class="fas fa-undo me-2"></i>Restore
                        Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="restoreProductForm" method="POST" action="">
                    @csrf
                    <div class="modal-body modal-form" style="padding:20px 24px;">
                        <p class="text-muted mb-3" style="font-size:.9rem;">
                            You are about to restore <strong id="restoreProductName" class="text-dark"></strong> back to
                            active products so customers can view and purchase it.
                        </p>
                        <div class="mb-3">
                            <label class="mb-1">Starting Stock Quantity <small class="text-muted">(optional, enter current
                                    stock or leave 0)</small></label>
                            <input type="number" class="form-control" name="stock" id="restoreProductStock" placeholder="0"
                                min="0" value="0">
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top:none;padding:0 24px 24px;">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                            style="border-radius:10px;">Cancel</button>
                        <button type="submit" class="btn"
                            style="background:var(--gasgo-blue);color:#fff;border-radius:10px;font-weight:600;padding:10px 24px;">
                            <i class="fas fa-check me-1"></i>Bring Back to Active
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add/Edit Product Modal -->
    <div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="border-radius:16px;border:none;box-shadow:0 25px 50px -12px rgba(0,0,0,0.15);">
                <!-- Modal Header -->
                <div class="modal-header bg-white" style="border-bottom:1px solid #f1f5f9;padding:20px 24px;border-radius:16px 16px 0 0;">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:44px;height:44px;border-radius:12px;background:rgba(26,109,176,0.08);display:flex;align-items:center;justify-content:center;color:var(--gasgo-blue);font-size:1.25rem;">
                            <i class="{{ $nicheIcon }}" id="productModalHeaderIcon"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="modal-title fw-bold mb-0 text-dark" id="productModalTitle">
                                    <span id="productModalAction">Add New</span> <span id="productModalNicheNoun">{{ $nicheItemNoun }}</span>
                                </h5>
                                <span class="badge rounded-pill bg-light text-primary border px-2 py-1" style="font-size:0.75rem;font-weight:600;">
                                    <i class="{{ $nicheIcon }} me-1"></i>{{ $nicheName }}
                                </span>
                            </div>
                            <small class="text-muted" id="productModalSub" style="font-size:0.85rem;">Configure product specifications, pricing, and stock inventory.</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body modal-form" style="padding:24px;background:#fafbfc;">
                    <div id="productModalAlerts" class="mb-3"></div>
                    
                    <form id="productForm" method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="_method" id="productFormMethod" value="POST">

                        <!-- Section: Product Details -->
                        <div class="card border-0 shadow-sm rounded-3 mb-3 p-3 bg-white">
                            <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                                <i class="{{ $nicheIcon }} text-primary"></i>
                                <span class="fw-bold text-dark" id="productModalSectionTitle" style="font-size:0.92rem;">{{ $nicheInfoTitle }}</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold mb-1" for="productName" id="productNameLabel">{{ $nicheItemNoun }} Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" id="productName"
                                        placeholder="{{ $nicheConfig['product_placeholder'] ?? 'e.g. Product Name' }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold mb-1" for="productCategory">Category <span class="text-danger">*</span></label>
                                    <select class="form-select" name="category" id="productCategory"
                                        onchange="handleProductCategoryChange(true)" required>
                                        @php
                                            $currentNicheCats = $nicheCategories ?? \App\Services\CategoryService::getCategoriesForCurrentNiche();
                                        @endphp
                                        @foreach($currentNicheCats as $ncat)
                                            <option value="{{ $ncat['slug'] }}" 
                                                data-exchange="{{ !empty($ncat['requires_exchange']) ? '1' : '0' }}"
                                                data-placeholder="{{ $ncat['placeholder'] ?? '' }}">
                                                {{ $ncat['name'] }}
                                            </option>
                                        @endforeach
                                        <option value="freebie" data-exchange="0" data-placeholder="e.g. Promotional Item">Freebie / Promotional Item</option>
                                    </select>
                                </div>
                                <div class="col-md-12" id="productWeightCol">
                                    <label class="form-label fw-semibold mb-1" id="productWeightLabel">{{ $nicheConfig['spec_label'] ?? 'Model / Specifications' }}</label>
                                    <input type="text" class="form-control" name="weight" id="productWeight"
                                        placeholder="{{ $nicheConfig['spec_placeholder'] ?? 'e.g. Specifications' }}">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold mb-1" for="productDescription" id="productDescriptionLabel">Description <small class="text-muted">(Optional)</small></label>
                                    <textarea class="form-control" name="description" id="productDescription" rows="3"
                                        style="resize:vertical;font-size:0.9rem;"
                                        placeholder="{{ $nicheConfig['desc_placeholder'] ?? 'Add product features, warranty, or description...' }}"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Section: Pricing & Profit Margin -->
                        <div class="card border-0 shadow-sm rounded-3 mb-3 p-3 bg-white" id="productPricingSection">
                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-tags text-success"></i>
                                    <span class="fw-bold text-dark" style="font-size:0.92rem;">Pricing & Profit Margin</span>
                                </div>
                                <div id="productMarginBadge" class="badge rounded-pill bg-light text-muted border px-2.5 py-1" style="font-size:0.78rem;">
                                    Est. Margin: --
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6" id="productSellingPriceCol">
                                    <label class="form-label fw-semibold mb-1" for="productSellingPrice">Selling Price (₱) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light fw-bold text-muted">₱</span>
                                        <input type="number" class="form-control" name="selling_price" id="productSellingPrice"
                                            placeholder="0.00" min="0" step="0.01" oninput="calculateProductMargin()" required>
                                    </div>
                                </div>
                                <div class="col-md-6" id="productCostPriceCol">
                                    <label class="form-label fw-semibold mb-1" for="productCostPrice">Cost Price (₱) <small class="text-muted">(Capital)</small></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light fw-bold text-muted">₱</span>
                                        <input type="number" class="form-control" name="cost_price" id="productCostPrice"
                                            placeholder="0.00" min="0" step="0.01" oninput="calculateProductMargin()">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section: Stock & Inventory Control -->
                        <div class="card border-0 shadow-sm rounded-3 mb-3 p-3 bg-white" id="productStockSection">
                            <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                                <i class="fas fa-boxes-stacked text-warning"></i>
                                <span class="fw-bold text-dark" id="productModalStockTitle" style="font-size:0.92rem;">Stock & Inventory Control</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold mb-1" for="productStock">Initial Stock Quantity <small class="text-muted">(Units)</small></label>
                                    <input type="number" class="form-control" name="stock" id="productStock"
                                        placeholder="0" min="0" value="0">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold mb-1" for="productReorderLevel">Low Stock Alert Threshold</label>
                                    <input type="number" class="form-control" name="reorder_level" id="productReorderLevel"
                                        placeholder="5" min="0" value="5">
                                </div>
                            </div>
                        </div>

                        <!-- Section: Media & Visibility -->
                        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                            <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                                <i class="fas fa-sliders text-info"></i>
                                <span class="fw-bold text-dark" style="font-size:0.92rem;">Media & Visibility</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold mb-1">Product Image</label>
                                    <div class="d-flex align-items-center gap-3 flex-wrap">
                                        <div id="productImagePreviewWrapper" class="position-relative" style="display:none;">
                                            <img id="productImagePreview" src="" alt="Preview" 
                                                style="width:80px;height:80px;object-fit:contain;background:#f8fafc;border:2px solid #e2e8f0;border-radius:12px;padding:4px;">
                                            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 rounded-circle p-0" 
                                                style="width:22px;height:22px;transform:translate(30%,-30%);font-size:10px;" 
                                                onclick="clearProductImagePreview()" title="Remove Image">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <div class="flex-grow-1">
                                            <input type="file" class="form-control" name="image" id="productImageInput" accept="image/*" onchange="previewProductImage(this)">
                                            <small class="text-muted">Accepted formats: JPG, PNG, WEBP. Max size: 2MB.</small>
                                        </div>
                                    </div>
                                </div>

                                @php
                                    $nicheNeedsExchange = $isWaterNiche || (!$isApplianceNiche && !$isFoodNiche);
                                @endphp
                                <div class="col-12" id="productRequiresExchangeWrapper" style="{{ $nicheNeedsExchange ? '' : 'display:none;' }}">
                                    <div class="p-3 rounded-3" style="background:#f8fafc;border:1px solid #e2e8f0;">
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input" type="checkbox" id="productRequiresExchange"
                                                name="requires_exchange" value="1">
                                            <label class="form-check-label fw-semibold ms-2" for="productRequiresExchange" id="productRequiresExchangeLabel">
                                                Requires Empty Container Exchange
                                            </label>
                                            <div class="text-muted mt-1" style="font-size:0.82rem;" id="productRequiresExchangeSub">
                                                Customers must return an empty container upon delivery.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="prodActive" name="is_active"
                                            value="1" checked>
                                        <label class="form-check-label fw-semibold" for="prodActive">Active (visible in store catalog)</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer bg-white" style="border-top:1px solid #f1f5f9;padding:16px 24px;border-radius:0 0 16px 16px;">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal" style="border-radius:10px;font-weight:600;">Cancel</button>
                    <button type="submit" form="productForm" id="productSubmitBtn" class="btn shadow-sm"
                        style="background:var(--gasgo-orange);color:#fff;border-radius:10px;font-weight:600;padding:10px 28px;">
                        <span id="productSubmitSpinner" class="spinner-border spinner-border-sm me-1 d-none" role="status" aria-hidden="true"></span>
                        <span id="productSubmitText">Save {{ $nicheItemNoun }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Freebie Modal -->
    <div class="modal fade" id="freebieModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius:16px; border: 2px solid #ffc107;">
                <div class="modal-header" style="border-bottom:none;padding:24px 24px 0;background:#fffbf0;">
                    <h5 class="modal-title fw-bold" style="color:#ffc107;" id="freebieModalTitle"><i
                            class="fas fa-gift me-2"></i>Add New Freebie</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body modal-form" style="padding:24px;">
                    <form id="freebieForm" method="POST" action="{{ route('admin.freebies.store') }}"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="_method" id="freebieFormMethod" value="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="mb-1">Freebie Name</label>
                                <input type="text" class="form-control" name="name" id="freebieName"
                                    placeholder="e.g. Free Lighter" required>
                            </div>
                            <div class="col-md-6">
                                <label class="mb-1">Category</label>
                                <select class="form-select" name="category" id="freebieCategory">
                                    <option value="Promotional Gifts">Promotional Gifts</option>
                                    <option value="Accessories">Accessories</option>
                                    <option value="Safety Items">Safety Items</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="mb-1">Reward Points Required</label>
                                <input type="number" class="form-control" name="reward_points_required"
                                    id="freebieRewardPoints" placeholder="e.g. 50 points" min="0">
                            </div>
                            <div class="col-12">
                                <label class="mb-1">Description</label>
                                <textarea class="form-control" name="description" id="freebieDescription" rows="3"
                                    placeholder="Freebie description..."></textarea>
                            </div>
                            <div class="col-12">
                                <label class="mb-1">Freebie Image</label>
                                <input type="file" class="form-control" name="image" accept="image/*">
                            </div>
                            <div class="col-12">
                                <label class="mb-1">Stock</label>
                                <input type="number" class="form-control" name="stock" id="freebieStock"
                                    placeholder="Quantity available" value="0" min="0" required>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="freebieActive" name="is_active"
                                        value="1" checked>
                                    <label class="form-check-label" for="freebieActive">Active (available for
                                        redemption)</label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer" style="border-top:none;padding:0 24px 24px;background:#fffbf0;">
                    <button class="btn" data-bs-dismiss="modal" style="border-radius:10px;">Cancel</button>
                    <button class="btn" type="submit" form="freebieForm"
                        style="background:#ffc107;color:#333;border-radius:10px;font-weight:600;padding:10px 28px;">Save
                        Freebie</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const initialSectionFromServer = "{{ request('tab') }}";

        // Section switching
        function switchSection(section, tabEl) {
            // Hide all sections
            document.getElementById('productsSection').style.display = 'none';
            document.getElementById('freebiesSection').style.display = 'none';
            const archivedSec = document.getElementById('archivedSection');
            if (archivedSec) archivedSec.style.display = 'none';

            // Show selected section
            const targetSection = document.getElementById(section + 'Section');
            if (targetSection) {
                targetSection.style.display = 'block';
            }

            // Update tab active state
            document.querySelectorAll('.section-tab').forEach(tab => tab.classList.remove('active'));
            if (tabEl) {
                tabEl.classList.add('active');
            } else {
                const targetTab = document.querySelector('.section-tab[data-section="' + section + '"]');
                if (targetTab) {
                    targetTab.classList.add('active');
                }
            }

            try {
                localStorage.setItem('adminProductsActiveSection', section);
            } catch (e) {
                // Ignore browser storage errors.
            }
        }

        function getActiveSection() {
            const visibleSection = ['products', 'freebies', 'archived'].find((section) => {
                const el = document.getElementById(section + 'Section');
                return el && el.style.display !== 'none';
            });

            return visibleSection || 'products';
        }

        function showProductsAlert(type, message) {
            const container = document.getElementById('productsAlerts');
            if (!container) {
                return;
            }

            const level = type === 'danger' ? 'danger' : 'success';
            const alert = document.createElement('div');
            alert.className = 'alert alert-' + level + ' alert-dismissible fade show';
            alert.setAttribute('role', 'alert');
            alert.innerHTML = message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';

            container.prepend(alert);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        async function refreshAdminProductSections() {
            const response = await fetch(window.location.pathname, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error('Unable to refresh product sections.');
            }

            const html = await response.text();
            const parser = new DOMParser();
            const nextDoc = parser.parseFromString(html, 'text/html');

            ['productsSection', 'freebiesSection', 'archivedSection'].forEach((sectionId) => {
                const currentSection = document.getElementById(sectionId);
                const nextSection = nextDoc.getElementById(sectionId);
                if (currentSection && nextSection) {
                    currentSection.innerHTML = nextSection.innerHTML;
                }
            });
        }

        // Product functions and dynamic configuration
        const currentActiveNicheNoun = @json($nicheItemNoun);
        const currentActiveNicheInfo = @json($nicheInfoTitle);
        const currentActiveNicheName = @json($nicheName);
        const currentActiveNicheIcon = @json($nicheIcon);

        const nicheCategoryMeta = {
            // LPG Niches
            'tank': {
                name: 'e.g. Solane 11kg LPG Tank (Refill)',
                itemNoun: 'LPG Tank',
                infoTitle: 'LPG Tank Information & Specs',
                descPlaceholder: 'e.g. 11kg Solane LPG tank with safety cap and seal, ideal for domestic kitchens...',
                iconClass: 'fas fa-fire',
                specLabel: 'Weight (kg)',
                specPlaceholder: 'e.g. 11kg / 2.7kg / 50kg',
                exchangeDefault: true,
                exchangeLabel: 'Requires Empty LPG Tank Exchange',
                exchangeSub: 'Customer must return an empty LPG cylinder upon delivery.'
            },
            'accessories': {
                name: 'e.g. Heavy Duty LPG Regulator w/ Gauge & 1.5m Hose',
                itemNoun: 'Safety Accessory',
                infoTitle: 'Safety Accessory Details & Specs',
                descPlaceholder: 'e.g. Heavy-duty certified regulator with excess flow valve and 1.5-meter reinforced hose...',
                iconClass: 'fas fa-tools',
                specLabel: 'Length / Specs',
                specPlaceholder: 'e.g. 1.5 Meters / Heavy Duty / Universal',
                exchangeDefault: false,
                exchangeLabel: 'Requires Parts Exchange',
                exchangeSub: 'Enable if old accessory replacement is required.'
            },

            // Water Niches
            'water': {
                name: 'e.g. 5-Gallon Round Purified Water Refill',
                itemNoun: 'Water Container / Refill',
                infoTitle: 'Water Container & Refill Details',
                descPlaceholder: 'e.g. 16-stage purified drinking water, sealed and sanitized 5-gallon bottle...',
                iconClass: 'fas fa-tint',
                specLabel: 'Container Capacity / Volume',
                specPlaceholder: 'e.g. 5-Gallon (18.9L) / 350ml / 500ml',
                exchangeDefault: true,
                exchangeLabel: 'Requires Empty Water Container Exchange',
                exchangeSub: 'Customer must return an empty 5-gallon container upon delivery.'
            },
            'dispensers': {
                name: 'e.g. Tabletop Water Dispenser w/ Faucet',
                itemNoun: 'Water Dispenser',
                infoTitle: 'Dispenser Specifications & Details',
                descPlaceholder: 'e.g. Tabletop water dispenser with non-drip faucet and durable build...',
                iconClass: 'fas fa-faucet',
                specLabel: 'Dimensions / Specs',
                specPlaceholder: 'e.g. Tabletop / Floor Standing / Plastic',
                exchangeDefault: false,
                exchangeLabel: 'Requires Equipment Exchange',
                exchangeSub: 'Enable if this unit requires an equipment swap.'
            },

            // Food & Meals Niches
            'meals': {
                name: 'e.g. Special Beef Pares with Garlic Rice',
                itemNoun: 'Main Meal Dish',
                infoTitle: 'Food & Meal Dish Details',
                descPlaceholder: 'e.g. Slow-cooked tender beef pares served with fragrant garlic fried rice and hot soup...',
                iconClass: 'fas fa-utensils',
                specLabel: 'Portion / Serving Size',
                specPlaceholder: 'e.g. 1-2 Persons / 1 Pax / Solo Meal',
                exchangeDefault: false,
                exchangeLabel: 'Requires Food Container / Tiffin Exchange',
                exchangeSub: 'Enable if dishes are served in reusable food containers/tiffins.'
            },
            'snacks': {
                name: 'e.g. Crispy Chicken Burger with Fries',
                itemNoun: 'Snack / Short Order',
                infoTitle: 'Snack & Finger Food Details',
                descPlaceholder: 'e.g. Crispy chicken burger served with signature garlic dip and fries...',
                iconClass: 'fas fa-burger',
                specLabel: 'Portion / Quantity',
                specPlaceholder: 'e.g. 1 Pc / Regular / Large',
                exchangeDefault: false,
                exchangeLabel: 'Requires Container Exchange',
                exchangeSub: 'Enable if packaging swap is needed.'
            },
            'beverages': {
                name: 'e.g. Signature Iced Milk Tea (16oz)',
                itemNoun: 'Beverage / Drink',
                infoTitle: 'Beverage & Drink Details',
                descPlaceholder: 'e.g. Freshly brewed iced milk tea with black pearls and brown sugar...',
                iconClass: 'fas fa-mug-hot',
                specLabel: 'Volume / Size',
                specPlaceholder: 'e.g. 16oz / 22oz / 500ml',
                exchangeDefault: false,
                exchangeLabel: 'Requires Reusable Bottle / Tumbler Exchange',
                exchangeSub: 'Enable if drinks use refillable bottles.'
            },
            'bilao': {
                name: 'e.g. Pancit Canton Party Bilao (Medium)',
                itemNoun: 'Party Bilao / Tray',
                infoTitle: 'Party Bilao Package Details',
                descPlaceholder: 'e.g. Authentic pancit canton topped with crispy pork lechon and vegetables...',
                iconClass: 'fas fa-bowl-rice',
                specLabel: 'Serving Size / Tray Size',
                specPlaceholder: 'e.g. Medium (6-8 Pax) / Large (10-12 Pax)',
                exchangeDefault: false,
                exchangeLabel: 'Requires Wooden Bilao / Tray Deposit',
                exchangeSub: 'Enable if customer must return the party tray.'
            },

            // Pure Appliances Niches
            'appliances': {
                name: 'e.g. Inverter Refrigerator 2-Door 250L',
                itemNoun: 'Major Appliance',
                infoTitle: 'Major Home Appliance Details',
                descPlaceholder: 'e.g. High-efficiency 2-door inverter refrigerator with smart cooling, no-frost technology...',
                iconClass: 'fas fa-tv',
                specLabel: 'Model / Power Specs',
                specPlaceholder: 'e.g. 220V / Inverter / 250L / 1200W',
                exchangeDefault: false,
                exchangeLabel: 'Requires Trade-in / Box Exchange',
                exchangeSub: 'Enable if unit trade-in or original box return applies.'
            },
            'kitchen': {
                name: 'e.g. Digital Air Fryer 4.5L w/ Touch Screen',
                itemNoun: 'Kitchen Appliance',
                infoTitle: 'Kitchen Appliance Specifications',
                descPlaceholder: 'e.g. 4.5L digital air fryer with 8 preset cooking modes, non-stick basket, and touch display...',
                iconClass: 'fas fa-blender',
                specLabel: 'Capacity / Power Specs',
                specPlaceholder: 'e.g. 4.5L / 1400W / 220V',
                exchangeDefault: false,
                exchangeLabel: 'Requires Equipment Exchange',
                exchangeSub: 'Enable if equipment exchange applies.'
            },
            'living': {
                name: 'e.g. 16-Inch High Velocity Stand Fan',
                itemNoun: 'Living Appliance',
                infoTitle: 'Living & Cooling Appliance Details',
                descPlaceholder: 'e.g. 16-inch aerodynamic stand fan with 3-speed control, oscillating head, and thermal safety fuse...',
                iconClass: 'fas fa-fan',
                specLabel: 'Speed / Power Specs',
                specPlaceholder: 'e.g. 16-Inch / 60W / 3-Speed',
                exchangeDefault: false,
                exchangeLabel: 'Requires Equipment Exchange',
                exchangeSub: 'Enable if equipment exchange applies.'
            },
            'parts': {
                name: 'e.g. Universal Remote Control / Air Filter',
                itemNoun: 'Appliance Part',
                infoTitle: 'Part & Accessory Details',
                descPlaceholder: 'e.g. Universal remote control compatible with major inverter split aircon models...',
                iconClass: 'fas fa-screwdriver-wrench',
                specLabel: 'Model Fit / Compatibility',
                specPlaceholder: 'e.g. Universal Fit / Standard Model',
                exchangeDefault: false,
                exchangeLabel: 'Requires Core Part Return',
                exchangeSub: 'Enable if old part return is required.'
            },

            // Promotional Items
            'freebie': {
                name: 'e.g. Promotional Gift / Accessory',
                itemNoun: 'Promotional Freebie',
                infoTitle: 'Promotional Item Details',
                descPlaceholder: 'e.g. Complimentary gift or promotional accessory for loyalty reward redemptions...',
                iconClass: 'fas fa-gift',
                specLabel: 'Specifications',
                specPlaceholder: 'e.g. Promotional Item',
                exchangeDefault: false,
                exchangeLabel: 'Requires Exchange',
                exchangeSub: 'Not required for promotional freebies.'
            }
        };

        function calculateProductMargin() {
            const sellPrice = parseFloat(document.getElementById('productSellingPrice')?.value || 0);
            const costPrice = parseFloat(document.getElementById('productCostPrice')?.value || 0);
            const badge = document.getElementById('productMarginBadge');
            if (!badge) return;

            if (sellPrice <= 0) {
                badge.className = 'badge rounded-pill bg-light text-muted border px-2.5 py-1';
                badge.textContent = 'Est. Margin: --';
                return;
            }

            const margin = sellPrice - costPrice;
            const marginPct = (margin / sellPrice) * 100;
            const formattedMargin = (margin >= 0 ? '+' : '') + '₱' + margin.toFixed(2);
            const formattedPct = marginPct.toFixed(1) + '%';

            if (margin >= 0) {
                badge.className = 'badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2.5 py-1';
                badge.textContent = `Est. Margin: ${formattedMargin} (${formattedPct})`;
            } else {
                badge.className = 'badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1';
                badge.textContent = `Loss Margin: ${formattedMargin} (${formattedPct})`;
            }
        }

        function previewProductImage(input) {
            const wrapper = document.getElementById('productImagePreviewWrapper');
            const preview = document.getElementById('productImagePreview');
            if (!wrapper || !preview) return;

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    wrapper.style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function clearProductImagePreview() {
            const input = document.getElementById('productImageInput');
            const wrapper = document.getElementById('productImagePreviewWrapper');
            const preview = document.getElementById('productImagePreview');
            if (input) input.value = '';
            if (preview) preview.src = '';
            if (wrapper) wrapper.style.display = 'none';
        }

        function handleProductCategoryChange(isManual = false) {
            const catSelect = document.getElementById('productCategory');
            const category = (catSelect?.value || '').toLowerCase();
            const selectedOption = catSelect?.options[catSelect.selectedIndex];
            const meta = nicheCategoryMeta[category] || {};

            const infoTitle = meta.infoTitle || currentActiveNicheInfo;

            // Update section header
            const sectionTitle = document.getElementById('productModalSectionTitle');
            if (sectionTitle) sectionTitle.textContent = infoTitle;

            // Description placeholder
            const descInput = document.getElementById('productDescription');
            if (descInput && meta.descPlaceholder) {
                descInput.placeholder = meta.descPlaceholder;
            }

            // Dynamic placeholder for product name
            const nameInput = document.getElementById('productName');
            if (nameInput) {
                const placeholder = selectedOption?.dataset?.placeholder || meta.name || 'e.g. Product Name';
                nameInput.placeholder = placeholder;
            }

            // Container exchange handling
            const isExchangeCategory = selectedOption?.dataset?.exchange === '1' || meta.exchangeDefault;
            const exchangeWrapper = document.getElementById('productRequiresExchangeWrapper');
            const exchangeCheckbox = document.getElementById('productRequiresExchange');
            const exchangeLabel = document.getElementById('productRequiresExchangeLabel');
            const exchangeSub = document.getElementById('productRequiresExchangeSub');
            
            if (isManual && exchangeCheckbox) {
                exchangeCheckbox.checked = isExchangeCategory;
            }
            if (exchangeLabel && meta.exchangeLabel) {
                exchangeLabel.textContent = meta.exchangeLabel;
            }
            if (exchangeSub && meta.exchangeSub) {
                exchangeSub.textContent = meta.exchangeSub;
            }

            // Specs / Weight label and placeholder
            const specLabel = document.getElementById('productWeightLabel');
            const specInput = document.getElementById('productWeight');
            if (specLabel && specInput) {
                specLabel.textContent = meta.specLabel || 'Specifications';
                specInput.placeholder = meta.specPlaceholder || 'e.g. Specifications';
            }

            // Freebie vs Selling Product mode
            const pricingSection = document.getElementById('productPricingSection');
            const sellInput = document.getElementById('productSellingPrice');

            if (category === 'freebie') {
                if (pricingSection) pricingSection.style.display = 'none';
                if (sellInput) {
                    sellInput.removeAttribute('required');
                    sellInput.value = '0';
                }
            } else {
                if (pricingSection) pricingSection.style.display = 'block';
                if (sellInput) {
                    sellInput.setAttribute('required', 'required');
                }
            }

            calculateProductMargin();
        }

        function openAddProduct() {
            const actionSpan = document.getElementById('productModalAction');
            if (actionSpan) actionSpan.textContent = 'Add New';
            
            document.getElementById('productModalNicheNoun').textContent = currentActiveNicheNoun;
            document.getElementById('productModalSub').textContent = `Configure ${currentActiveNicheNoun.toLowerCase()} specifications, pricing, and stock inventory.`;
            document.getElementById('productModalHeaderIcon').className = currentActiveNicheIcon;
            document.getElementById('productForm').action = "{{ route('admin.products.store') }}";
            document.getElementById('productFormMethod').value = 'POST';
            document.getElementById('productModalAlerts').innerHTML = '';
            
            document.getElementById('productName').value = '';
            const defaultCategoryOption = document.querySelector('#productCategory option');
            if (defaultCategoryOption) {
                document.getElementById('productCategory').value = defaultCategoryOption.value;
            }
            document.getElementById('productRequiresExchange').checked = (defaultCategoryOption?.dataset?.exchange === '1');
            document.getElementById('productDescription').value = '';
            document.getElementById('productSellingPrice').value = '';
            document.getElementById('productCostPrice').value = '';
            document.getElementById('productStock').value = '0';
            document.getElementById('productReorderLevel').value = '5';
            document.getElementById('productWeight').value = '';
            document.getElementById('prodActive').checked = true;
            
            const submitText = document.getElementById('productSubmitText');
            if (submitText) submitText.textContent = `Save ${currentActiveNicheNoun}`;

            clearProductImagePreview();
            handleProductCategoryChange(false);
        }

        function openEditProduct(button) {
            const actionSpan = document.getElementById('productModalAction');
            if (actionSpan) actionSpan.textContent = 'Edit';

            document.getElementById('productModalNicheNoun').textContent = currentActiveNicheNoun;
            document.getElementById('productModalSub').textContent = `Update ${currentActiveNicheNoun.toLowerCase()} details, pricing, and stock inventory.`;
            document.getElementById('productModalHeaderIcon').className = 'fas fa-pen-to-square';
            document.getElementById('productForm').action = button.dataset.updateUrl;
            document.getElementById('productFormMethod').value = 'PUT';
            document.getElementById('productModalAlerts').innerHTML = '';

            document.getElementById('productName').value = button.dataset.name || '';
            document.getElementById('productCategory').value = button.dataset.category || 'appliances';
            document.getElementById('productRequiresExchange').checked = button.dataset.requiresExchange === '1';
            document.getElementById('productDescription').value = button.dataset.description || '';
            document.getElementById('productSellingPrice').value = button.dataset.sellingPrice || '';
            document.getElementById('productCostPrice').value = button.dataset.costPrice || '';
            document.getElementById('productStock').value = button.dataset.stock || '0';
            document.getElementById('productReorderLevel').value = button.dataset.reorderLevel || '5';
            document.getElementById('productWeight').value = button.dataset.weight || '';
            document.getElementById('prodActive').checked = (button.dataset.isActive === '1');

            const submitText = document.getElementById('productSubmitText');
            if (submitText) submitText.textContent = 'Save Changes';

            // Handle image preview
            const imageUrl = button.dataset.imageUrl;
            const previewWrapper = document.getElementById('productImagePreviewWrapper');
            const preview = document.getElementById('productImagePreview');
            const fileInput = document.getElementById('productImageInput');
            if (fileInput) fileInput.value = '';

            if (imageUrl && imageUrl.trim() !== '') {
                preview.src = imageUrl;
                previewWrapper.style.display = 'block';
            } else {
                clearProductImagePreview();
            }

            handleProductCategoryChange(false);
        }

        function openRestoreProductModal(name, restoreUrl) {
            document.getElementById('restoreProductName').textContent = name;
            document.getElementById('restoreProductForm').action = restoreUrl;
            document.getElementById('restoreProductStock').value = '0';
        }

        function filterArchived() {
            const q = (document.getElementById('searchArchived')?.value || '').toLowerCase().trim();
            document.querySelectorAll('.archived-item').forEach(item => {
                const name = item.dataset.name || '';
                item.style.display = (!q || name.includes(q)) ? '' : 'none';
            });
        }

        // Freebie functions
        function openAddFreebie() {
            document.getElementById('freebieModalTitle').innerHTML = '<i class="fas fa-gift me-2"></i>Add New Freebie';
            document.getElementById('freebieForm').action = "{{ route('admin.freebies.store') }}";
            document.getElementById('freebieFormMethod').value = 'POST';
            document.getElementById('freebieName').value = '';
            document.getElementById('freebieDescription').value = '';
            document.getElementById('freebieRewardPoints').value = '0';
            document.getElementById('freebieCategory').value = 'Promotional Gifts';
            document.getElementById('freebieStock').value = '0';
            document.getElementById('freebieActive').checked = true;
        }

        function openEditFreebie(button) {
            document.getElementById('freebieModalTitle').innerHTML = '<i class="fas fa-gift me-2"></i>Edit Freebie';
            document.getElementById('freebieForm').action = button.dataset.updateUrl;
            document.getElementById('freebieFormMethod').value = 'PUT';
            document.getElementById('freebieName').value = button.dataset.name || '';
            document.getElementById('freebieDescription').value = button.dataset.description || '';
            document.getElementById('freebieRewardPoints').value = button.dataset.rewardPoints || '0';
            document.getElementById('freebieCategory').value = button.dataset.category || 'Promotional Gifts';
            document.getElementById('freebieStock').value = button.dataset.stock || '0';
            document.getElementById('freebieActive').checked = (button.dataset.isActive === '1');
        }

        async function submitFreebieFormAjax(event) {
            event.preventDefault();

            const form = event.target;
            const submitButton = document.querySelector('button[form="freebieForm"]');
            const methodField = document.getElementById('freebieFormMethod');
            const isUpdate = (methodField?.value || 'POST').toUpperCase() === 'PUT';

            const formData = new FormData(form);

            if (isUpdate) {
                formData.set('_method', 'PUT');
            } else {
                formData.delete('_method');
            }

            try {
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.textContent = isUpdate ? 'Saving...' : 'Creating...';
                }

                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    if (response.status === 422 && payload.errors) {
                        const validationMessage = Object.values(payload.errors).flat().join('<br>');
                        showProductsAlert('danger', validationMessage || 'Please check the form fields.');
                    } else {
                        showProductsAlert('danger', payload.message || 'Failed to save freebie.');
                    }
                    return;
                }

                await refreshAdminProductSections();

                const modalElement = document.getElementById('freebieModal');
                const modalInstance = bootstrap.Modal.getInstance(modalElement);
                if (modalInstance) {
                    modalInstance.hide();
                }

                showProductsAlert('success', payload.message || (isUpdate ? 'Freebie updated successfully.' : 'Freebie created successfully.'));
                switchSection('freebies');
            } catch (error) {
                showProductsAlert('danger', 'Network error while saving freebie. Please try again.');
            } finally {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = 'Save Freebie';
                }
            }
        }

        async function submitProductFormAjax(event) {
            event.preventDefault();

            const form = event.target;
            const submitButton = document.getElementById('productSubmitBtn');
            const spinner = document.getElementById('productSubmitSpinner');
            const textSpan = document.getElementById('productSubmitText');
            const methodField = document.getElementById('productFormMethod');
            const isUpdate = (methodField?.value || 'POST').toUpperCase() === 'PUT';
            const modalAlerts = document.getElementById('productModalAlerts');

            if (modalAlerts) modalAlerts.innerHTML = '';

            const formData = new FormData(form);

            if (isUpdate) {
                formData.set('_method', 'PUT');
            } else {
                formData.delete('_method');
            }

            try {
                if (submitButton) {
                    submitButton.disabled = true;
                    if (spinner) spinner.classList.remove('d-none');
                    if (textSpan) textSpan.textContent = isUpdate ? 'Saving Changes...' : 'Creating Product...';
                }

                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    if (response.status === 422 && payload.errors) {
                        const errorList = Object.values(payload.errors).flat();
                        const validationHtml = '<div class="alert alert-danger alert-dismissible fade show p-3" role="alert">' +
                            '<strong class="d-block mb-1"><i class="fas fa-exclamation-triangle me-1"></i>Please correct the following errors:</strong>' +
                            '<ul class="mb-0 ps-3">' + errorList.map(e => `<li>${e}</li>`).join('') + '</ul>' +
                            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                            '</div>';
                        if (modalAlerts) {
                            modalAlerts.innerHTML = validationHtml;
                        } else {
                            showProductsAlert('danger', errorList.join('<br>'));
                        }
                    } else {
                        const errMsg = payload.message || 'Failed to save product. Please try again.';
                        if (modalAlerts) {
                            modalAlerts.innerHTML = `<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-exclamation-circle me-1"></i>${errMsg}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
                        } else {
                            showProductsAlert('danger', errMsg);
                        }
                    }
                    return;
                }

                await refreshAdminProductSections();

                const modalElement = document.getElementById('productModal');
                const modalInstance = bootstrap.Modal.getInstance(modalElement);
                if (modalInstance) {
                    modalInstance.hide();
                }

                showProductsAlert('success', payload.message || (isUpdate ? 'Product updated successfully.' : 'Product created successfully.'));
                switchSection('products');
            } catch (error) {
                if (modalAlerts) {
                    modalAlerts.innerHTML = '<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-plug-circle-xmark me-1"></i>Network error while saving product. Please try again.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
                } else {
                    showProductsAlert('danger', 'Network error while saving product. Please try again.');
                }
            } finally {
                if (submitButton) {
                    submitButton.disabled = false;
                    if (spinner) spinner.classList.add('d-none');
                    if (textSpan) textSpan.textContent = 'Save Product';
                }
            }
        }

        document.getElementById('productForm').addEventListener('submit', submitProductFormAjax);
        document.getElementById('freebieForm').addEventListener('submit', submitFreebieFormAjax);

        document.addEventListener('DOMContentLoaded', () => {
            let preferredSection = null;

            try {
                preferredSection = localStorage.getItem('adminProductsActiveSection');
            } catch (e) {
                preferredSection = null;
            }

            const initialSection = initialSectionFromServer || preferredSection || 'products';
            switchSection(initialSection);
        });
    </script>
@endsection