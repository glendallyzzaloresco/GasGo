@extends('layouts.admin')

@section('title', 'GasGo Admin - Products')
@section('nav-products', 'active')
@section('page-title', 'Product Management')

@section('admin-styles')
<style>
    .product-card {
        background:#fff; border-radius:16px; box-shadow:0 4px 15px rgba(0,0,0,.06);
        overflow:hidden; transition:transform .3s;
    }
    .product-card:hover { transform:translateY(-4px); }
    .product-card img { width:100%; height:240px; object-fit:contain; background-color:#fff; padding:12px; }
    .product-card .card-body { padding:18px; }
    .product-card .card-body h6 { font-weight:700; color:var(--gasgo-blue); }
    .product-card .price { color:var(--gasgo-orange); font-weight:700; font-size:1.15rem; }
    .product-card .stock-badge { font-size:.72rem; }
    .modal-form label { font-weight:600; font-size:.88rem; color:#555; }
    .modal-form .form-control, .modal-form .form-select {
        border-radius:10px; border:2px solid #e0e0e0; padding:10px 16px;
    }
    .modal-form .form-control:focus, .modal-form .form-select:focus { border-color:var(--gasgo-blue); box-shadow:none; }
    
    .section-tabs {
        display:flex; gap:12px; margin-bottom:28px; border-bottom:2px solid #e0e0e0;
    }
    .section-tab {
        padding:12px 20px; font-weight:600; font-size:.95rem; cursor:pointer;
        color:#999; border:none; background:none; border-bottom:3px solid transparent;
        transition:all .3s;
    }
    .section-tab.active {
        color:var(--gasgo-blue); border-bottom-color:var(--gasgo-blue);
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
</style>
@endsection

@section('content')
<div id="productsAlerts">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger">
        <div class="fw-bold mb-1">Please fix the following:</div>
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
</div>

@php
    $resolveImageUrl = function (?string $path): ?string {
        if (! $path) {
            return null;
        }

        $normalized = ltrim($path, '/');

        if (str_starts_with($normalized, 'http://') || str_starts_with($normalized, 'https://')) {
            return $path;
        }

        if (str_starts_with($normalized, 'storage/') || str_starts_with($normalized, 'images/')) {
            return asset($normalized);
        }

        return asset('storage/' . $normalized);
    };

    $productsCollection = collect($products ?? []);
    $freebiesCollection = collect($freebies ?? []);

    $saleProducts = $productsCollection
        ->filter(fn ($p) => strtolower((string) ($p->category ?? '')) !== 'freebie')
        ->values();

    $productFreebies = $productsCollection
        ->filter(fn ($p) => strtolower((string) ($p->category ?? '')) === 'freebie')
        ->values();

    $saleProductsInStock = $saleProducts
        ->filter(fn ($p) => (int) ($p->quantity_on_hand ?? 0) > 0)
        ->values();

    $freebieCatalog = $freebiesCollection
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
            $productFreebies->map(function ($p) {
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

    $freebiesInStock = $freebieCatalog
        ->filter(fn ($row) => (int) ($row['stock'] ?? 0) > 0)
        ->values();

    $outOfStockProducts = $saleProducts
        ->filter(fn ($p) => (int) ($p->quantity_on_hand ?? 0) <= 0)
        ->map(function ($p) {
            return [
                'kind' => 'product',
                'item' => $p,
                'name' => $p->name,
                'description' => $p->description,
                'stock' => (int) ($p->quantity_on_hand ?? 0),
                'price' => (float) ($p->price ?? 0),
                'image' => $p->image,
            ];
        });

    $outOfStockFreebies = $freebieCatalog
        ->filter(fn ($row) => (int) ($row['stock'] ?? 0) <= 0)
        ->map(function ($row) {
            $item = $row['item'];
            return [
                'kind' => $row['source'] === 'product' ? 'product-freebie' : 'freebie',
                'item' => $item,
                'name' => $row['name'],
                'description' => $row['description'],
                'stock' => (int) ($row['stock'] ?? 0),
                'price' => $row['source'] === 'product' ? (float) ($item->price ?? 0) : 0,
                'image' => $row['image'],
            ];
        });

    $outOfStockItems = $outOfStockProducts
        ->concat($outOfStockFreebies)
        ->sortBy(fn ($row) => strtolower((string) ($row['name'] ?? '')))
        ->values();
@endphp

<!-- Section Tabs -->
<div class="section-tabs">
    <button class="section-tab active" data-section="products" onclick="switchSection('products', this)">
        <i class="fas fa-bag-shopping me-2"></i>Products For Sale
    </button>
    <button class="section-tab" data-section="freebies" onclick="switchSection('freebies', this)">
        <i class="fas fa-gift me-2"></i>Freebies & Rewards
    </button>
    <button class="section-tab" data-section="outOfStock" onclick="switchSection('outOfStock', this)">
        <i class="fas fa-box-open me-2"></i>Out of Stock
    </button>
</div>

<!-- Products Section -->
<div id="productsSection" class="section-content">
    <!-- Top Actions -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h5 class="fw-bold" style="color:var(--gasgo-blue);margin-bottom:10px;">Products For Sale</h5>
            <p class="text-muted mb-0" style="font-size:.88rem;">Manage products that customers can purchase</p>
        </div>
        <button class="btn" style="background:var(--gasgo-orange);color:#fff;border-radius:12px;font-weight:600;padding:10px 22px;" data-bs-toggle="modal" data-bs-target="#productModal" onclick="openAddProduct()">
            <i class="fas fa-plus me-2"></i>Add Product
        </button>
    </div>

<!-- Products Grid -->
<div class="row g-4" id="productsGrid">
    @forelse($saleProducts as $product)
    <div class="col-lg-3 col-md-4 col-sm-6 product-item">
        <div class="product-card">
            @php $productImageUrl = $resolveImageUrl($product->image); @endphp
            @php $qty = (int) ($product->quantity_on_hand ?? 0); @endphp
            @if($productImageUrl)
                <img src="{{ $productImageUrl }}" alt="{{ $product->name }}">
            @else
                <div style="width:100%;height:240px;background:linear-gradient(135deg,var(--gasgo-blue-light),#fff);display:flex;align-items:center;justify-content:center;">
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
                <p class="text-muted mb-2" style="font-size:.82rem;">{{ $product->description ?? 'No description' }}</p>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="price">₱{{ number_format($product->price, 2) }}</span>
                        <span class="text-muted" style="font-size:.82rem;">Stock: <strong class="{{ $qty <= 5 ? 'text-danger' : '' }}">{{ $qty }}</strong></span>
                </div>
                <div class="d-flex gap-2">
                    <button
                        class="btn btn-sm flex-grow-1"
                        style="background:var(--gasgo-blue);color:#fff;border-radius:8px;font-weight:600;"
                        data-bs-toggle="modal"
                        data-bs-target="#productModal"
                        onclick="openEditProduct(this)"
                        data-id="{{ $product->id }}"
                        data-name="{{ $product->name }}"
                        data-category="{{ $product->category ?? 'tank' }}"
                        data-description="{{ $product->description }}"
                        data-price="{{ $product->price }}"
                        data-stock="{{ $qty }}"
                        data-weight="{{ $product->weight }}"
                        data-is-active="{{ $product->is_active ? '1' : '0' }}"
                        data-update-url="{{ route('admin.products.update', $product) }}"
                    ><i class="fas fa-edit me-1"></i>Edit</button>
                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST" onsubmit="return confirm('Delete this product?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm" type="submit" style="background:#f8d7da;color:#dc3545;border-radius:8px;" title="Delete"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <p class="text-muted text-center py-5">No products found. <a href="#" onclick="openAddProduct()" class="text-decoration-none">Add your first product</a></p>
    </div>
    @endforelse
</div>
</div>

<!-- Freebies Section -->
<div id="freebiesSection" class="section-content" style="display:none;">
    <!-- Top Actions -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h5 class="fw-bold" style="color:#ffc107;margin-bottom:10px;">Freebies & Rewards</h5>
            <p class="text-muted mb-0" style="font-size:.88rem;">Manage promotional items and loyalty rewards</p>
        </div>
        <button class="btn" style="background:var(--gasgo-orange);color:#fff;border-radius:12px;font-weight:600;padding:10px 22px;" data-bs-toggle="modal" data-bs-target="#freebieModal" onclick="openAddFreebie()">
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
                    <div style="width:100%;height:240px;background:linear-gradient(135deg,#ffe8a8,#fff5d9);display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-gift" style="font-size:3rem;color:#ffc107;opacity:.6;"></i>
                    </div>
                @endif
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="mb-0">{{ $freebie->name }} @if($isProductFreebie)<small class="text-muted" style="font-size:.72rem;">(Product)</small>@endif</h6>
                        @if($freebie->stock <= 5)
                            <span class="badge bg-warning text-dark stock-badge">Low</span>
                        @elseif($freebie->stock == 0)
                            <span class="badge bg-danger stock-badge">Out</span>
                        @else
                            <span class="badge bg-success stock-badge">OK</span>
                        @endif
                    </div>
                    <p class="text-muted mb-2" style="font-size:.82rem;">{{ $freebie->description ?? 'Complimentary item' }}</p>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="price" style="color:#ffc107;">{{ $isProductFreebie ? '₱' . number_format((float) ($freebie->price ?? 0), 2) : 'FREE' }}</span>
                        <span class="text-muted" style="font-size:.82rem;">Stock: <strong class="{{ $freebie->stock <= 5 ? 'text-danger' : '' }}">{{ $freebie->stock }}</strong></span>
                    </div>
                    <div class="d-flex gap-2">
                        @if($isProductFreebie)
                            @php $qty = (int) ($freebie->quantity_on_hand ?? 0); @endphp
                            <button
                                class="btn btn-sm flex-grow-1"
                                style="background:var(--gasgo-blue);color:#fff;border-radius:8px;font-weight:600;"
                                data-bs-toggle="modal"
                                data-bs-target="#productModal"
                                onclick="openEditProduct(this)"
                                data-id="{{ $freebie->id }}"
                                data-name="{{ $freebie->name }}"
                                data-category="{{ $freebie->category ?? 'freebie' }}"
                                data-description="{{ $freebie->description }}"
                                data-price="{{ $freebie->price }}"
                                data-stock="{{ $qty }}"
                                data-weight="{{ $freebie->weight }}"
                                data-is-active="{{ $freebie->is_active ? '1' : '0' }}"
                                data-update-url="{{ route('admin.products.update', $freebie) }}"
                            ><i class="fas fa-edit me-1"></i>Edit</button>
                            <form action="{{ route('admin.products.destroy', $freebie) }}" method="POST" onsubmit="return confirm('Delete this item?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm" type="submit" style="background:#f8d7da;color:#dc3545;border-radius:8px;" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                        @else
                            <button
                                class="btn btn-sm flex-grow-1"
                                style="background:var(--gasgo-blue);color:#fff;border-radius:8px;font-weight:600;"
                                data-bs-toggle="modal"
                                data-bs-target="#freebieModal"
                                onclick="openEditFreebie(this)"
                                data-id="{{ $freebie->id }}"
                                data-name="{{ $freebie->name }}"
                                data-description="{{ $freebie->description }}"
                                data-stock="{{ $freebie->stock }}"
                                data-category="{{ $freebie->category }}"
                                data-reward-points="{{ $freebie->reward_points_required }}"
                                data-redemption-type="{{ $freebie->redemption_type }}"
                                data-is-active="{{ $freebie->is_active ? '1' : '0' }}"
                                data-update-url="{{ route('admin.freebies.update', $freebie) }}"
                            ><i class="fas fa-edit me-1"></i>Edit</button>
                            <form action="{{ route('admin.freebies.destroy', $freebie) }}" method="POST" onsubmit="return confirm('Delete this freebie?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm" type="submit" style="background:#f8d7da;color:#dc3545;border-radius:8px;" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <p class="text-muted text-center py-5">No freebies found. <a href="#" onclick="openAddFreebie()" class="text-decoration-none">Add your first freebie</a></p>
        </div>
        @endforelse
    </div>
</div>

<!-- Out Of Stock Section -->
<div id="outOfStockSection" class="section-content" style="display:none;">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h5 class="fw-bold" style="color:#dc3545;margin-bottom:10px;"><i class="fas fa-box-open me-2"></i>Out of Stock Items</h5>
            <p class="text-muted mb-0" style="font-size:.88rem;">Products and freebies that currently have zero stock</p>
        </div>
    </div>

    <div class="row g-4" id="outOfStockGrid">
        @forelse($outOfStockItems as $row)
        @php
            $item = $row['item'];
            $itemImageUrl = $resolveImageUrl($row['image']);
            $isProductLike = in_array($row['kind'], ['product', 'product-freebie'], true);
        @endphp
        <div class="col-lg-3 col-md-4 col-sm-6 out-of-stock-item">
            <div class="product-card" style="position:relative;border:2px solid #f8d7da;">
                <span class="freebie-badge" style="background:#dc3545;color:#fff;"><i class="fas fa-times-circle me-1"></i>OUT</span>
                @if($itemImageUrl)
                    <img src="{{ $itemImageUrl }}" alt="{{ $row['name'] }}" style="height:240px;">
                @else
                    <div style="width:100%;height:240px;background:linear-gradient(135deg,#fbe9eb,#fff);display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-box-open" style="font-size:3rem;color:#dc3545;opacity:.55;"></i>
                    </div>
                @endif
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="mb-0">{{ $row['name'] }}</h6>
                        <span class="badge bg-danger stock-badge">Out</span>
                    </div>
                    <p class="text-muted mb-2" style="font-size:.82rem;">{{ $row['description'] ?? 'No description' }}</p>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="price">{{ $isProductLike ? ('₱' . number_format((float) ($row['price'] ?? 0), 2)) : 'FREE' }}</span>
                        <span class="text-muted" style="font-size:.82rem;">Stock: <strong class="text-danger">0</strong></span>
                    </div>
                    <div class="d-flex gap-2">
                        @if($isProductLike)
                            @php $qty = (int) ($item->quantity_on_hand ?? 0); @endphp
                            <button
                                class="btn btn-sm flex-grow-1"
                                style="background:var(--gasgo-blue);color:#fff;border-radius:8px;font-weight:600;"
                                data-bs-toggle="modal"
                                data-bs-target="#productModal"
                                onclick="openEditProduct(this)"
                                data-id="{{ $item->id }}"
                                data-name="{{ $item->name }}"
                                data-category="{{ $item->category ?? 'tank' }}"
                                data-description="{{ $item->description }}"
                                data-price="{{ $item->price }}"
                                data-stock="{{ $qty }}"
                                data-weight="{{ $item->weight }}"
                                data-is-active="{{ $item->is_active ? '1' : '0' }}"
                                data-update-url="{{ route('admin.products.update', $item) }}"
                            ><i class="fas fa-edit me-1"></i>Restock</button>
                            <form action="{{ route('admin.products.destroy', $item) }}" method="POST" onsubmit="return confirm('Delete this item?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm" type="submit" style="background:#f8d7da;color:#dc3545;border-radius:8px;" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                        @else
                            <button
                                class="btn btn-sm flex-grow-1"
                                style="background:var(--gasgo-blue);color:#fff;border-radius:8px;font-weight:600;"
                                data-bs-toggle="modal"
                                data-bs-target="#freebieModal"
                                onclick="openEditFreebie(this)"
                                data-id="{{ $item->id }}"
                                data-name="{{ $item->name }}"
                                data-description="{{ $item->description }}"
                                data-stock="{{ $item->stock }}"
                                data-category="{{ $item->category }}"
                                data-reward-points="{{ $item->reward_points_required }}"
                                data-redemption-type="{{ $item->redemption_type }}"
                                data-is-active="{{ $item->is_active ? '1' : '0' }}"
                                data-update-url="{{ route('admin.freebies.update', $item) }}"
                            ><i class="fas fa-edit me-1"></i>Restock</button>
                            <form action="{{ route('admin.freebies.destroy', $item) }}" method="POST" onsubmit="return confirm('Delete this freebie?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm" type="submit" style="background:#f8d7da;color:#dc3545;border-radius:8px;" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <p class="text-muted text-center py-5">No out-of-stock items found.</p>
        </div>
        @endforelse
    </div>
</div>

<!-- Add/Edit Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header" style="border-bottom:none;padding:24px 24px 0;">
                <h5 class="modal-title fw-bold" style="color:var(--gasgo-blue);" id="productModalTitle">Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body modal-form" style="padding:24px;">
                <form id="productForm" method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_method" id="productFormMethod" value="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="mb-1">Product Name</label>
                            <input type="text" class="form-control" name="name" id="productName" placeholder="e.g. Solane 11kg" required>
                        </div>
                        <div class="col-md-3">
                            <label class="mb-1">Price (₱)</label>
                            <input type="number" class="form-control" name="price" id="productPrice" placeholder="0.00" min="0" step="0.01" required>
                        </div>
                        <div class="col-md-3">
                            <label class="mb-1">Category</label>
                            <select class="form-select" name="category" id="productCategory" required>
                                <option value="tank">Tank</option>
                                <option value="freebie">Freebie</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="mb-1">Weight (kg)</label>
                            <input type="text" class="form-control" name="weight" id="productWeight" placeholder="e.g. 11kg">
                        </div>
                        <div class="col-md-4">
                            <label class="mb-1">Stock Quantity</label>
                            <input type="number" class="form-control" name="stock" id="productStock" placeholder="0" min="0" required>
                        </div>
                        <div class="col-12">
                            <label class="mb-1">Description</label>
                            <textarea class="form-control" name="description" id="productDescription" rows="3" placeholder="Product description..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="mb-1">Product Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="prodActive" name="is_active" value="1" checked>
                                <label class="form-check-label" for="prodActive">Active (visible to customers)</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="border-top:none;padding:0 24px 24px;">
                <button class="btn" data-bs-dismiss="modal" style="border-radius:10px;">Cancel</button>
                <button class="btn" type="submit" form="productForm" style="background:var(--gasgo-orange);color:#fff;border-radius:10px;font-weight:600;padding:10px 28px;">Save Product</button>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Freebie Modal -->
<div class="modal fade" id="freebieModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:16px; border: 2px solid #ffc107;">
            <div class="modal-header" style="border-bottom:none;padding:24px 24px 0;background:#fffbf0;">
                <h5 class="modal-title fw-bold" style="color:#ffc107;" id="freebieModalTitle"><i class="fas fa-gift me-2"></i>Add New Freebie</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body modal-form" style="padding:24px;">
                <form id="freebieForm" method="POST" action="{{ route('admin.freebies.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_method" id="freebieFormMethod" value="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="mb-1">Freebie Name</label>
                            <input type="text" class="form-control" name="name" id="freebieName" placeholder="e.g. Free Lighter" required>
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
                            <label class="mb-1">Stock Quantity</label>
                            <input type="number" class="form-control" name="stock" id="freebieStock" placeholder="0" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="mb-1">Reward Points Required</label>
                            <input type="number" class="form-control" name="reward_points_required" id="freebieRewardPoints" placeholder="e.g. 50 points" min="0">
                        </div>
                        <div class="col-12">
                            <label class="mb-1">Description</label>
                            <textarea class="form-control" name="description" id="freebieDescription" rows="3" placeholder="Freebie description..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="mb-1">Freebie Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                        </div>
                        <div class="col-12">
                            <label class="mb-1">Redemption Type</label>
                            <select class="form-select" name="redemption_type" id="freebieRedemptionType" required>
                                <option value="loyalty_points">Loyalty Points</option>
                                <option value="auto_included">Auto-included with Order</option>
                                <option value="promotional">Promotional</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="freebieActive" name="is_active" value="1" checked>
                                <label class="form-check-label" for="freebieActive">Active (available for redemption)</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="border-top:none;padding:0 24px 24px;background:#fffbf0;">
                <button class="btn" data-bs-dismiss="modal" style="border-radius:10px;">Cancel</button>
                <button class="btn" type="submit" form="freebieForm" style="background:#ffc107;color:#333;border-radius:10px;font-weight:600;padding:10px 28px;">Save Freebie</button>
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
        document.getElementById('outOfStockSection').style.display = 'none';
        
        // Show selected section
        document.getElementById(section + 'Section').style.display = 'block';
        
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
        const visibleSection = ['products', 'freebies', 'outOfStock'].find((section) => {
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

        ['productsSection', 'freebiesSection', 'outOfStockSection'].forEach((sectionId) => {
            const currentSection = document.getElementById(sectionId);
            const nextSection = nextDoc.getElementById(sectionId);
            if (currentSection && nextSection) {
                currentSection.innerHTML = nextSection.innerHTML;
            }
        });
    }

    // Product functions
    function openAddProduct() {
        document.getElementById('productModalTitle').textContent = 'Add New Product';
        document.getElementById('productForm').action = "{{ route('admin.products.store') }}";
        document.getElementById('productFormMethod').value = 'POST';
        document.getElementById('productName').value = '';
        const defaultCategoryOption = document.querySelector('#productCategory option');
        document.getElementById('productCategory').value = defaultCategoryOption ? defaultCategoryOption.value : '';
        document.getElementById('productDescription').value = '';
        document.getElementById('productPrice').value = '';
        document.getElementById('productStock').value = '0';
        document.getElementById('productWeight').value = '';
        document.getElementById('prodActive').checked = true;
    }

    function openEditProduct(button) {
        document.getElementById('productModalTitle').textContent = 'Edit Product';
        document.getElementById('productForm').action = button.dataset.updateUrl;
        document.getElementById('productFormMethod').value = 'PUT';
        document.getElementById('productName').value = button.dataset.name || '';
        document.getElementById('productCategory').value = button.dataset.category || 'tank';
        document.getElementById('productDescription').value = button.dataset.description || '';
        document.getElementById('productPrice').value = button.dataset.price || '';
        document.getElementById('productStock').value = button.dataset.stock || '0';
        document.getElementById('productWeight').value = button.dataset.weight || '';
        document.getElementById('prodActive').checked = (button.dataset.isActive === '1');
    }
    
    function filterProducts() {
        const q = document.getElementById('searchProducts').value.toLowerCase();
        const cat = document.getElementById('categoryFilter').value.toLowerCase();
        document.querySelectorAll('.product-item').forEach(item => {
            const matchesText = item.textContent.toLowerCase().includes(q);
            const matchesCat = !cat || (item.dataset.category || '').toLowerCase() === cat;
            item.style.display = (matchesText && matchesCat) ? '' : 'none';
        });
    }

    // Freebie functions
    function openAddFreebie() {
        document.getElementById('freebieModalTitle').innerHTML = '<i class="fas fa-gift me-2"></i>Add New Freebie';
        document.getElementById('freebieForm').action = "{{ route('admin.freebies.store') }}";
        document.getElementById('freebieFormMethod').value = 'POST';
        document.getElementById('freebieName').value = '';
        document.getElementById('freebieDescription').value = '';
        document.getElementById('freebieStock').value = '';
        document.getElementById('freebieRewardPoints').value = '0';
        document.getElementById('freebieCategory').value = 'Promotional Gifts';
        document.getElementById('freebieRedemptionType').value = 'promotional';
        document.getElementById('freebieActive').checked = true;
    }

    function openEditFreebie(button) {
        document.getElementById('freebieModalTitle').innerHTML = '<i class="fas fa-gift me-2"></i>Edit Freebie';
        document.getElementById('freebieForm').action = button.dataset.updateUrl;
        document.getElementById('freebieFormMethod').value = 'PUT';
        document.getElementById('freebieName').value = button.dataset.name || '';
        document.getElementById('freebieDescription').value = button.dataset.description || '';
        document.getElementById('freebieStock').value = button.dataset.stock || '';
        document.getElementById('freebieRewardPoints').value = button.dataset.rewardPoints || '0';
        document.getElementById('freebieCategory').value = button.dataset.category || 'Promotional Gifts';
        document.getElementById('freebieRedemptionType').value = button.dataset.redemptionType || 'promotional';
        document.getElementById('freebieActive').checked = (button.dataset.isActive === '1');
    }

    function filterFreebies() {
        const q = document.getElementById('searchFreebies').value.toLowerCase();
        document.querySelectorAll('.freebie-item').forEach(item => {
            const matchesText = item.textContent.toLowerCase().includes(q);
            item.style.display = matchesText ? '' : 'none';
        });
    }

    async function submitFreebieFormAjax(event) {
        event.preventDefault();

        const form = event.target;
        const submitButton = document.querySelector('button[form="freebieForm"]');
        const methodField = document.getElementById('freebieFormMethod');
        const activeSectionBeforeSubmit = getActiveSection();
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

            const sectionToShow = activeSectionBeforeSubmit === 'outOfStock' ? 'outOfStock' : 'freebies';
            switchSection(sectionToShow);
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
        const submitButton = document.querySelector('button[form="productForm"]');
        const methodField = document.getElementById('productFormMethod');
        const activeSectionBeforeSubmit = getActiveSection();
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
                    showProductsAlert('danger', payload.message || 'Failed to save product.');
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

            const sectionToShow = activeSectionBeforeSubmit === 'outOfStock' ? 'outOfStock' : 'products';
            switchSection(sectionToShow);
        } catch (error) {
            showProductsAlert('danger', 'Network error while saving product. Please try again.');
        } finally {
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = 'Save Product';
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
