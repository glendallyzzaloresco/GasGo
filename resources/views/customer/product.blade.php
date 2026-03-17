@extends('layouts.customer')

@section('title', 'GasGo - Products')
@section('nav-products', 'active')

@section('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, var(--gasgo-blue) 0%, #2196f3 100%);
        color: white; padding: 50px 0 60px; margin-bottom: -30px; position: relative;
    }
    .page-header::after {
        content: ''; position: absolute; bottom: -2px; left: 0; right: 0; height: 60px;
        background: #f8f9fa; clip-path: ellipse(55% 100% at 50% 100%);
    }

    .filter-bar {
        background: white; border-radius: 16px; padding: 20px 24px;
        box-shadow: 0 4px 20px rgba(0,0,0,.06); margin-bottom: 30px;
    }
    .filter-btn {
        padding: 8px 20px; border-radius: 25px; font-weight: 600; font-size: .85rem;
        border: 2px solid #eee; background: white; color: #555; transition: all .25s; cursor: pointer;
    }
    .filter-btn:hover, .filter-btn.active {
        background: var(--gasgo-blue); color: white; border-color: var(--gasgo-blue);
    }

    .product-card {
        background: white; border-radius: 20px; overflow: hidden;
        box-shadow: 0 8px 30px rgba(0,0,0,.08); transition: transform .35s, box-shadow .35s; height: 100%;
    }
    .product-card:hover {
        transform: translateY(-8px); box-shadow: 0 16px 40px rgba(0,0,0,.14);
    }
    .product-card .product-img {
        height: 220px; background: var(--gasgo-blue-light);
        display: flex; align-items: center; justify-content: center; position: relative;
    }
    .product-card .product-img img { max-height: 180px; object-fit: contain; }
    .product-badge {
        position: absolute; top: 14px; left: 14px;
        background: var(--gasgo-orange); color: white;
        padding: 4px 14px; border-radius: 20px; font-size: .75rem; font-weight: 600;
    }
    .product-body { padding: 20px; }
    .product-body h5 { font-weight: 700; color: var(--gasgo-blue); }
    .product-price { font-size: 1.25rem; font-weight: 700; color: var(--gasgo-orange); }
    .product-weight { font-size: .82rem; color: #888; }
    .product-stock { font-size: .8rem; font-weight: 500; }
    .product-stock.in { color: #27ae60; }
    .product-stock.out { color: #e74c3c; }
</style>
@endsection

@section('content')
<section class="page-header">
    <div class="container text-center">
        <h1 class="fw-bold" data-aos="fade-up">Product Catalog</h1>
        <p class="mb-0" style="opacity:.9;" data-aos="fade-up" data-aos-delay="100">Browse our LPG tanks and accessories</p>
    </div>
</section>

<section class="container section-padding" style="position:relative;z-index:2;">
    <!-- Filters -->
    <div class="filter-bar" data-aos="fade-up">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="fw-bold text-muted me-2"><i class="fas fa-filter me-1"></i>Filter:</span>
            <button class="filter-btn active" data-filter="all">All</button>
            <button class="filter-btn" data-filter="lpg">LPG Tanks</button>
            <button class="filter-btn" data-filter="accessories">Accessories</button>
            <div class="ms-auto">
                <input type="text" class="form-control form-control-gasgo" placeholder="Search products..." id="searchProduct" style="padding:10px 18px;font-size:.9rem;">
            </div>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="row g-4" id="productGrid">
        <!-- 11kg -->
        <div class="col-lg-3 col-md-6 product-item" data-category="lpg" data-name="LPG Tank 11kg" data-aos="fade-up" data-aos-delay="100">
            <div class="product-card">
                <div class="product-img">
                    <span class="product-badge"><i class="fas fa-bolt me-1"></i>Popular</span>
                    <img src="{{ asset('images/11kg.jpg') }}" alt="11kg LPG">
                </div>
                <div class="product-body">
                    <h5>LPG Tank 11kg</h5>
                    <p class="product-weight"><i class="fas fa-weight-hanging me-1"></i>11 Kilograms</p>
                    <p class="product-stock in"><i class="fas fa-check-circle me-1"></i>In Stock</p>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="product-price">₱850.00</span>
                        <button class="btn btn-gasgo btn-sm add-to-cart-btn" data-id="1" data-name="LPG Tank 11kg" data-price="850" data-image="{{ asset('images/11kg.jpg') }}">
                            <i class="fas fa-cart-plus me-1"></i>Add
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 22kg -->
        <div class="col-lg-3 col-md-6 product-item" data-category="lpg" data-name="LPG Tank 22kg" data-aos="fade-up" data-aos-delay="200">
            <div class="product-card">
                <div class="product-img">
                    <img src="{{ asset('images/22kg.jpg') }}" alt="22kg LPG">
                </div>
                <div class="product-body">
                    <h5>LPG Tank 22kg</h5>
                    <p class="product-weight"><i class="fas fa-weight-hanging me-1"></i>22 Kilograms</p>
                    <p class="product-stock in"><i class="fas fa-check-circle me-1"></i>In Stock</p>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="product-price">₱1,600.00</span>
                        <button class="btn btn-gasgo btn-sm add-to-cart-btn" data-id="2" data-name="LPG Tank 22kg" data-price="1600" data-image="{{ asset('images/22kg.jpg') }}">
                            <i class="fas fa-cart-plus me-1"></i>Add
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2kg -->
        <div class="col-lg-3 col-md-6 product-item" data-category="lpg" data-name="LPG Tank 2kg" data-aos="fade-up" data-aos-delay="300">
            <div class="product-card">
                <div class="product-img">
                    <img src="{{ asset('images/2kg.jpg') }}" alt="2kg LPG">
                </div>
                <div class="product-body">
                    <h5>LPG Tank 2kg</h5>
                    <p class="product-weight"><i class="fas fa-weight-hanging me-1"></i>2 Kilograms</p>
                    <p class="product-stock in"><i class="fas fa-check-circle me-1"></i>In Stock</p>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="product-price">₱350.00</span>
                        <button class="btn btn-gasgo btn-sm add-to-cart-btn" data-id="3" data-name="LPG Tank 2kg" data-price="350" data-image="{{ asset('images/2kg.jpg') }}">
                            <i class="fas fa-cart-plus me-1"></i>Add
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Regulator Accessory -->
        <div class="col-lg-3 col-md-6 product-item" data-category="accessories" data-name="LPG Regulator" data-aos="fade-up" data-aos-delay="400">
            <div class="product-card">
                <div class="product-img">
                    <span class="product-badge" style="background:var(--gasgo-blue);">Accessory</span>
                    <i class="fas fa-cogs" style="font-size:4rem;color:var(--gasgo-blue);"></i>
                </div>
                <div class="product-body">
                    <h5>LPG Regulator</h5>
                    <p class="product-weight"><i class="fas fa-tag me-1"></i>Accessory</p>
                    <p class="product-stock in"><i class="fas fa-check-circle me-1"></i>In Stock</p>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="product-price">₱250.00</span>
                        <button class="btn btn-gasgo btn-sm add-to-cart-btn" data-id="4" data-name="LPG Regulator" data-price="250" data-image="">
                            <i class="fas fa-cart-plus me-1"></i>Add
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hose Accessory -->
        <div class="col-lg-3 col-md-6 product-item" data-category="accessories" data-name="LPG Hose" data-aos="fade-up" data-aos-delay="500">
            <div class="product-card">
                <div class="product-img">
                    <span class="product-badge" style="background:var(--gasgo-blue);">Accessory</span>
                    <i class="fas fa-link" style="font-size:4rem;color:var(--gasgo-blue);"></i>
                </div>
                <div class="product-body">
                    <h5>LPG Hose</h5>
                    <p class="product-weight"><i class="fas fa-tag me-1"></i>Accessory</p>
                    <p class="product-stock in"><i class="fas fa-check-circle me-1"></i>In Stock</p>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="product-price">₱150.00</span>
                        <button class="btn btn-gasgo btn-sm add-to-cart-btn" data-id="5" data-name="LPG Hose" data-price="150" data-image="">
                            <i class="fas fa-cart-plus me-1"></i>Add
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
function addToCart(id, name, price, image) {
    addToCartAjax(id, 1).catch(error => {
        console.error('Add to cart error:', error);
    });
}

document.querySelectorAll('.add-to-cart-btn').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        addToCart(parseInt(this.dataset.id), this.dataset.name, parseFloat(this.dataset.price), this.dataset.image);
    });
});

// filter buttons
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        const filter = this.dataset.filter;
        document.querySelectorAll('.product-item').forEach(item => {
            item.style.display = (filter === 'all' || item.dataset.category === filter) ? '' : 'none';
        });
    });
});

// search
document.getElementById('searchProduct').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.product-item').forEach(item => {
        item.style.display = item.dataset.name.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
@endsection
