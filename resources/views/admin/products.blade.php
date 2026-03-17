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
<!-- Section Tabs -->
<div class="section-tabs">
    <button class="section-tab active" onclick="switchSection('products')">
        <i class="fas fa-bag-shopping me-2"></i>Products For Sale
    </button>
    <button class="section-tab" onclick="switchSection('freebies')">
        <i class="fas fa-gift me-2"></i>Freebies & Rewards
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
    @forelse($products as $product)
    <div class="col-lg-3 col-md-4 col-sm-6 product-item">
        <div class="product-card">
            @if($product->image)
                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
            @else
                <div style="width:100%;height:240px;background:linear-gradient(135deg,var(--gasgo-blue-light),#fff);display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-box" style="font-size:3rem;color:var(--gasgo-blue);opacity:.5;"></i>
                </div>
            @endif
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="mb-0">{{ $product->name }}</h6>
                    @if($product->stock <= 5)
                        <span class="badge bg-warning text-dark stock-badge">Low Stock</span>
                    @elseif($product->stock == 0)
                        <span class="badge bg-danger stock-badge">Out</span>
                    @else
                        <span class="badge bg-success stock-badge">In Stock</span>
                    @endif
                </div>
                <p class="text-muted mb-2" style="font-size:.82rem;">{{ $product->description ?? 'No description' }}</p>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="price">₱{{ number_format($product->price, 2) }}</span>
                    <span class="text-muted" style="font-size:.82rem;">Stock: <strong class="{{ $product->stock <= 5 ? 'text-danger' : '' }}">{{ $product->stock }}</strong></span>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm flex-grow-1" style="background:var(--gasgo-blue);color:#fff;border-radius:8px;font-weight:600;" data-bs-toggle="modal" data-bs-target="#productModal"><i class="fas fa-edit me-1"></i>Edit</button>
                    <button class="btn btn-sm" style="background:#f8d7da;color:#dc3545;border-radius:8px;" title="Delete"><i class="fas fa-trash"></i></button>
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
        @forelse($freebies as $freebie)
        <div class="col-lg-3 col-md-4 col-sm-6 freebie-item">
            <div class="product-card freebie-card" style="position:relative;">
                <span class="freebie-badge"><i class="fas fa-star me-1"></i>FREEBIE</span>
                @if($freebie->image)
                    <img src="{{ asset('storage/' . $freebie->image) }}" alt="{{ $freebie->name }}" style="height:240px;">
                @else
                    <div style="width:100%;height:240px;background:linear-gradient(135deg,#ffe8a8,#fff5d9);display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-gift" style="font-size:3rem;color:#ffc107;opacity:.6;"></i>
                    </div>
                @endif
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="mb-0">{{ $freebie->name }}</h6>
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
                        <span class="price" style="color:#ffc107;">FREE</span>
                        <span class="text-muted" style="font-size:.82rem;">Stock: <strong class="{{ $freebie->stock <= 5 ? 'text-danger' : '' }}">{{ $freebie->stock }}</strong></span>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm flex-grow-1" style="background:var(--gasgo-blue);color:#fff;border-radius:8px;font-weight:600;" data-bs-toggle="modal" data-bs-target="#freebieModal" onclick="openEditFreebie(this)"><i class="fas fa-edit me-1"></i>Edit</button>
                        <button class="btn btn-sm" style="background:#f8d7da;color:#dc3545;border-radius:8px;" title="Delete"><i class="fas fa-trash"></i></button>
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

<!-- Add/Edit Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header" style="border-bottom:none;padding:24px 24px 0;">
                <h5 class="modal-title fw-bold" style="color:var(--gasgo-blue);" id="productModalTitle">Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body modal-form" style="padding:24px;">
                <form>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="mb-1">Product Name</label>
                            <input type="text" class="form-control" placeholder="e.g. LPG 11kg">
                        </div>
                        <div class="col-md-6">
                            <label class="mb-1">Category</label>
                            <select class="form-select">
                                <option>LPG Tanks</option>
                                <option>Accessories</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="mb-1">Price (₱)</label>
                            <input type="number" class="form-control" placeholder="0.00">
                        </div>
                        <div class="col-md-4">
                            <label class="mb-1">Stock Quantity</label>
                            <input type="number" class="form-control" placeholder="0">
                        </div>
                        <div class="col-md-4">
                            <label class="mb-1">Weight (kg)</label>
                            <input type="number" class="form-control" placeholder="0" step="0.1">
                        </div>
                        <div class="col-12">
                            <label class="mb-1">Description</label>
                            <textarea class="form-control" rows="3" placeholder="Product description..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="mb-1">Product Image</label>
                            <input type="file" class="form-control" accept="image/*">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="prodActive" checked>
                                <label class="form-check-label" for="prodActive">Active (visible to customers)</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="border-top:none;padding:0 24px 24px;">
                <button class="btn" data-bs-dismiss="modal" style="border-radius:10px;">Cancel</button>
                <button class="btn" style="background:var(--gasgo-orange);color:#fff;border-radius:10px;font-weight:600;padding:10px 28px;">Save Product</button>
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
                <form>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="mb-1">Freebie Name</label>
                            <input type="text" class="form-control" placeholder="e.g. Free Lighter">
                        </div>
                        <div class="col-md-6">
                            <label class="mb-1">Category</label>
                            <select class="form-select">
                                <option>Promotional Gifts</option>
                                <option>Accessories</option>
                                <option>Safety Items</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="mb-1">Stock Quantity</label>
                            <input type="number" class="form-control" placeholder="0">
                        </div>
                        <div class="col-md-6">
                            <label class="mb-1">Reward Points Required</label>
                            <input type="number" class="form-control" placeholder="e.g. 50 points">
                        </div>
                        <div class="col-12">
                            <label class="mb-1">Description</label>
                            <textarea class="form-control" rows="3" placeholder="Freebie description..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="mb-1">Freebie Image</label>
                            <input type="file" class="form-control" accept="image/*">
                        </div>
                        <div class="col-12">
                            <label class="mb-1">Redemption Type</label>
                            <select class="form-select">
                                <option>Loyalty Points</option>
                                <option>Auto-included with Order</option>
                                <option>Promotional</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="freebieActive" checked>
                                <label class="form-check-label" for="freebieActive">Active (available for redemption)</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="border-top:none;padding:0 24px 24px;background:#fffbf0;">
                <button class="btn" data-bs-dismiss="modal" style="border-radius:10px;">Cancel</button>
                <button class="btn" style="background:#ffc107;color:#333;border-radius:10px;font-weight:600;padding:10px 28px;">Save Freebie</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Section switching
    function switchSection(section) {
        // Hide all sections
        document.getElementById('productsSection').style.display = 'none';
        document.getElementById('freebiesSection').style.display = 'none';
        
        // Show selected section
        document.getElementById(section + 'Section').style.display = 'block';
        
        // Update tab active state
        document.querySelectorAll('.section-tab').forEach(tab => tab.classList.remove('active'));
        event.target.classList.add('active');
    }

    // Product functions
    function openAddProduct() {
        document.getElementById('productModalTitle').textContent = 'Add New Product';
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
    }

    function openEditFreebie(button) {
        document.getElementById('freebieModalTitle').innerHTML = '<i class="fas fa-gift me-2"></i>Edit Freebie';
    }

    function filterFreebies() {
        const q = document.getElementById('searchFreebies').value.toLowerCase();
        document.querySelectorAll('.freebie-item').forEach(item => {
            const matchesText = item.textContent.toLowerCase().includes(q);
            item.style.display = matchesText ? '' : 'none';
        });
    }
</script>
@endsection
