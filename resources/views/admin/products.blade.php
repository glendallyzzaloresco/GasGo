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
    .product-card img { width:100%; height:180px; object-fit:cover; }
    .product-card .card-body { padding:18px; }
    .product-card .card-body h6 { font-weight:700; color:var(--gasgo-blue); }
    .product-card .price { color:var(--gasgo-orange); font-weight:700; font-size:1.15rem; }
    .product-card .stock-badge { font-size:.72rem; }
    .modal-form label { font-weight:600; font-size:.88rem; color:#555; }
    .modal-form .form-control, .modal-form .form-select {
        border-radius:10px; border:2px solid #e0e0e0; padding:10px 16px;
    }
    .modal-form .form-control:focus, .modal-form .form-select:focus { border-color:var(--gasgo-blue); box-shadow:none; }
</style>
@endsection

@section('content')
<!-- Top Actions -->
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <div class="search-box" style="position:relative;">
            <i class="fas fa-search" style="position:absolute;left:16px;top:50%;transform:translateY(-50%);color:#aaa;"></i>
            <input type="text" id="searchProducts" placeholder="Search products..." style="border-radius:25px;padding:10px 20px 10px 42px;border:2px solid #e0e0e0;font-size:.88rem;width:280px;" onkeyup="filterProducts()">
        </div>
        <select id="categoryFilter" onchange="filterProducts()" style="border-radius:25px;padding:10px 18px;border:2px solid #e0e0e0;font-size:.88rem;background:#fff;cursor:pointer;">
            <option value="">All Categories</option>
            <option value="lpg-tank">LPG Tank</option>
            <option value="accessories">Accessories</option>
            <option value="regulator">Regulator</option>
        </select>
    </div>
    <button class="btn" style="background:var(--gasgo-orange);color:#fff;border-radius:12px;font-weight:600;padding:10px 22px;" data-bs-toggle="modal" data-bs-target="#productModal" onclick="openAddProduct()">
        <i class="fas fa-plus me-2"></i>Add Product
    </button>
</div>

<!-- Products Grid -->
<div class="row g-4" id="productsGrid">
    <!-- Product 1 -->
    <div class="col-lg-3 col-md-4 col-sm-6 product-item" data-category="lpg-tank">
        <div class="product-card">
            <img src="{{ asset('images/11kg.jpg') }}" alt="LPG 11kg">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="mb-0">LPG 11kg</h6>
                    <span class="badge bg-success stock-badge">In Stock</span>
                </div>
                <p class="text-muted mb-2" style="font-size:.82rem;">Standard 11kg LPG tank for household use</p>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="price">₱850</span>
                    <span class="text-muted" style="font-size:.82rem;">Stock: <strong>25</strong></span>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm flex-grow-1" style="background:var(--gasgo-blue);color:#fff;border-radius:8px;font-weight:600;" data-bs-toggle="modal" data-bs-target="#productModal"><i class="fas fa-edit me-1"></i>Edit</button>
                    <button class="btn btn-sm" style="background:#f8d7da;color:#dc3545;border-radius:8px;" title="Delete"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        </div>
    </div>
    <!-- Product 2 -->
    <div class="col-lg-3 col-md-4 col-sm-6 product-item" data-category="lpg-tank">
        <div class="product-card">
            <img src="{{ asset('images/22kg.jpg') }}" alt="LPG 22kg">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="mb-0">LPG 22kg</h6>
                    <span class="badge bg-success stock-badge">In Stock</span>
                </div>
                <p class="text-muted mb-2" style="font-size:.82rem;">Large 22kg LPG tank for commercial/family</p>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="price">₱1,600</span>
                    <span class="text-muted" style="font-size:.82rem;">Stock: <strong>15</strong></span>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm flex-grow-1" style="background:var(--gasgo-blue);color:#fff;border-radius:8px;font-weight:600;" data-bs-toggle="modal" data-bs-target="#productModal"><i class="fas fa-edit me-1"></i>Edit</button>
                    <button class="btn btn-sm" style="background:#f8d7da;color:#dc3545;border-radius:8px;" title="Delete"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        </div>
    </div>
    <!-- Product 3 -->
    <div class="col-lg-3 col-md-4 col-sm-6 product-item" data-category="lpg-tank">
        <div class="product-card">
            <img src="{{ asset('images/2kg.jpg') }}" alt="LPG 2kg">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="mb-0">LPG 2kg</h6>
                    <span class="badge bg-warning text-dark stock-badge">Low Stock</span>
                </div>
                <p class="text-muted mb-2" style="font-size:.82rem;">Compact 2kg LPG tank for portable use</p>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="price">₱375</span>
                    <span class="text-muted" style="font-size:.82rem;">Stock: <strong class="text-danger">3</strong></span>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm flex-grow-1" style="background:var(--gasgo-blue);color:#fff;border-radius:8px;font-weight:600;" data-bs-toggle="modal" data-bs-target="#productModal"><i class="fas fa-edit me-1"></i>Edit</button>
                    <button class="btn btn-sm" style="background:#f8d7da;color:#dc3545;border-radius:8px;" title="Delete"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        </div>
    </div>
    <!-- Product 4 -->
    <div class="col-lg-3 col-md-4 col-sm-6 product-item" data-category="regulator">
        <div class="product-card">
            <div style="width:100%;height:180px;background:linear-gradient(135deg,var(--gasgo-blue-light),#fff);display:flex;align-items:center;justify-content:center;">
                <i class="fas fa-tools" style="font-size:3rem;color:var(--gasgo-blue);opacity:.5;"></i>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="mb-0">LPG Regulator</h6>
                    <span class="badge bg-success stock-badge">In Stock</span>
                </div>
                <p class="text-muted mb-2" style="font-size:.82rem;">High-quality LPG regulator with safety valve</p>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="price">₱350</span>
                    <span class="text-muted" style="font-size:.82rem;">Stock: <strong>40</strong></span>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm flex-grow-1" style="background:var(--gasgo-blue);color:#fff;border-radius:8px;font-weight:600;" data-bs-toggle="modal" data-bs-target="#productModal"><i class="fas fa-edit me-1"></i>Edit</button>
                    <button class="btn btn-sm" style="background:#f8d7da;color:#dc3545;border-radius:8px;" title="Delete"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        </div>
    </div>
    <!-- Product 5 -->
    <div class="col-lg-3 col-md-4 col-sm-6 product-item">
        <div class="product-card">
            <div style="width:100%;height:180px;background:linear-gradient(135deg,var(--gasgo-orange-light),#fff);display:flex;align-items:center;justify-content:center;">
                <i class="fas fa-ring" style="font-size:3rem;color:var(--gasgo-orange);opacity:.5;"></i>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="mb-0">LPG Hose (1.5m)</h6>
                    <span class="badge bg-success stock-badge">In Stock</span>
                </div>
                <p class="text-muted mb-2" style="font-size:.82rem;">Flexible rubber LPG hose 1.5 meters</p>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="price">₱250</span>
                    <span class="text-muted" style="font-size:.82rem;">Stock: <strong>35</strong></span>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm flex-grow-1" style="background:var(--gasgo-blue);color:#fff;border-radius:8px;font-weight:600;" data-bs-toggle="modal" data-bs-target="#productModal"><i class="fas fa-edit me-1"></i>Edit</button>
                    <button class="btn btn-sm" style="background:#f8d7da;color:#dc3545;border-radius:8px;" title="Delete"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        </div>
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
@endsection

@section('scripts')
<script>
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
</script>
@endsection
