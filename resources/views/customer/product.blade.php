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
        cursor: pointer;
    }
    .product-card:hover {
        transform: translateY(-8px); box-shadow: 0 16px 40px rgba(0,0,0,.14);
    }
    .product-card .product-img {
        height: 220px; background: white;
        display: flex; align-items: center; justify-content: center; position: relative;
    }
    .product-card .product-img img { max-height: 180px; object-fit: contain; }
    .product-badge {
        position: absolute; top: 14px; left: 14px;
        background: var(--gasgo-orange); color: white;
        padding: 4px 14px; border-radius: 20px; font-size: .75rem; font-weight: 600;
    }
    .product-badge.accessory { background: var(--gasgo-blue); }
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
        @forelse($products as $index => $product)
            @php
                $label = strtolower($product->name ?? '');
                $category = (str_contains($label, 'lpg') || str_contains($label, 'kg')) ? 'lpg' : 'accessories';
                $inStock = (int) ($product->quantity_on_hand ?? 0) > 0;
                $img = $product->resolved_image;
            @endphp
            <div class="col-lg-3 col-md-6 product-item" data-category="{{ $category }}" data-name="{{ $product->name }}" data-aos="fade-up" data-aos-delay="{{ (($index % 6) + 1) * 100 }}">
                <a href="{{ route('customer.product.show', $product->id) }}" style="text-decoration: none; color: inherit;">
                    <div class="product-card">
                        <div class="product-img">
                            <span class="product-badge {{ $category === 'lpg' ? '' : 'accessory' }}">{{ $category === 'lpg' ? 'LPG' : 'Accessory' }}</span>
                            @if($img)
                                <img src="{{ $img }}" alt="{{ $product->name }}" class="img-fluid">
                            @else
                                <span class="text-muted small">No image available</span>
                            @endif
                        </div>
                        <div class="product-body">
                            <h5>{{ $product->name }}</h5>
                            <p class="product-weight"><i class="fas fa-weight-hanging me-1"></i>{{ $product->weight ?: ucfirst($category) }}</p>
                            <p class="product-stock {{ $inStock ? 'in' : 'out' }}"><i class="fas {{ $inStock ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>{{ $inStock ? 'In Stock' : 'Out of Stock' }}</p>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="product-price">₱{{ number_format($product->price, 2) }}</span>
                                <button class="btn btn-gasgo btn-sm add-to-cart-btn" data-id="{{ $product->id }}" data-name="{{ $product->name }}" data-price="{{ $product->price }}" data-image="{{ $img }}" {{ $inStock ? '' : 'disabled' }} onclick="event.stopPropagation();">
                                    <i class="fas fa-cart-plus me-1"></i>Add
                                </button>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12 text-center text-muted py-5">No products available yet.</div>
        @endforelse
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
