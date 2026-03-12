@extends('layouts.customer')

@section('title', 'GasGo - Home')
@section('nav-home', 'active')

@section('styles')
<style>
    /* ===== HERO ===== */
    .hero-section {
        background: linear-gradient(135deg, var(--gasgo-blue) 0%, #2196f3 60%, var(--gasgo-orange) 100%);
        color: white;
        padding: 100px 0 80px;
        position: relative;
    }
    .hero-section::after {
        content: '';
        position: absolute;
        bottom: -2px; left: 0; right: 0;
        height: 80px;
        background: #f8f9fa;
        clip-path: ellipse(55% 100% at 50% 100%);
    }
    .hero-title { font-size: 2.8rem; font-weight: 800; line-height: 1.2; }
    .hero-title span { color: var(--gasgo-orange); }
    .hero-subtitle { font-size: 1.1rem; opacity: .9; margin: 20px 0 30px; }
    .hero-badges { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 20px; }
    .hero-badge {
        background: rgba(255,255,255,.15);
        backdrop-filter: blur(6px);
        padding: 8px 18px;
        border-radius: 25px;
        font-size: .85rem;
        font-weight: 500;
        display: flex; align-items: center; gap: 6px;
    }
    .hero-badge i { color: var(--gasgo-orange); }
    .hero-img { max-width: 100%; }

    /* ===== PROMO BANNER ===== */
    .promo-banner {
        background: linear-gradient(135deg, var(--gasgo-orange) 0%, #ff6b35 100%);
        border-radius: 20px;
        padding: 40px;
        color: white;
        margin-top: -40px;
        position: relative;
        z-index: 2;
    }
    .promo-banner h3 { font-weight: 700; }

    /* ===== FEATURED PRODUCTS ===== */
    .product-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(0,0,0,.08);
        transition: transform .35s, box-shadow .35s;
    }
    .product-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 16px 40px rgba(0,0,0,.14);
    }
    .product-card .product-img {
        height: 200px;
        background: var(--gasgo-blue-light);
        display: flex; align-items: center; justify-content: center;
    }
    .product-card .product-img img { max-height: 160px; object-fit: contain; }
    .product-card .product-body { padding: 20px; }
    .product-card .product-body h5 { font-weight: 700; color: var(--gasgo-blue); margin-bottom: 4px; }
    .product-price { font-size: 1.25rem; font-weight: 700; color: var(--gasgo-orange); }
    .product-stock { font-size: .8rem; color: #27ae60; font-weight: 500; }
    .product-stock.out { color: #e74c3c; }

    /* ===== HOW IT WORKS ===== */
    .how-it-works { background: white; }
    .step-card {
        text-align: center;
        padding: 30px 20px;
        border-radius: 20px;
        transition: transform .3s;
    }
    .step-card:hover { transform: translateY(-6px); }
    .step-icon {
        width: 80px; height: 80px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.8rem; color: white; margin: 0 auto 18px;
    }
    .step-icon.blue { background: linear-gradient(135deg, #1a6db0, #2196f3); }
    .step-icon.orange { background: linear-gradient(135deg, #f7941d, #ff6b35); }
    .step-icon.green { background: linear-gradient(135deg, #27ae60, #2ecc71); }
    .step-icon.purple { background: linear-gradient(135deg, #8e44ad, #9b59b6); }

    /* ===== WHY GASGO ===== */
    .why-card {
        background: white;
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 4px 20px rgba(0,0,0,.06);
        height: 100%;
        border-top: 4px solid transparent;
        transition: all .3s;
    }
    .why-card:hover { border-top-color: var(--gasgo-orange); transform: translateY(-4px); }
    .why-card i { font-size: 2rem; margin-bottom: 14px; }

    @media (max-width: 768px) {
        .hero-title { font-size: 2rem; }
        .hero-section { padding: 60px 0 60px; }
    }
</style>
@endsection

@section('content')
<!-- Hero -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <h1 class="hero-title">Track Your <span>LPG Delivery</span> in Real-Time!</h1>
                <p class="hero-subtitle">Fast, reliable LPG delivery right to your door. Earn loyalty rewards with every order.</p>
                <a href="{{ url('/customer/product') }}" class="btn btn-gasgo btn-lg">
                    <i class="fas fa-fire me-2"></i>Browse Products
                </a>
                <div class="hero-badges">
                    <span class="hero-badge"><i class="fas fa-star"></i> Loyalty Rewards</span>
                    <span class="hero-badge"><i class="fas fa-bolt"></i> Fast Delivery</span>
                    <span class="hero-badge"><i class="fas fa-map-marker-alt"></i> Live Tracking</span>
                </div>
            </div>
            <div class="col-lg-6 text-center" data-aos="fade-left">
                <img src="{{ asset('images/11kg.jpg') }}" alt="LPG Tank" class="hero-img" style="max-height:380px;border-radius:20px;">
            </div>
        </div>
    </div>
</section>

<!-- Promo Banner -->
@guest
<section class="container" style="position:relative;z-index:2;" data-aos="fade-up">
    <div class="promo-banner">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h3><i class="fas fa-gift me-2"></i>New User? Get FREE Delivery on Your First Order!</h3>
                <p class="mb-0" style="opacity:.9;">Register now and start earning loyalty points with every purchase.</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="{{ url('/customer/loginRegistration?tab=register') }}" class="btn btn-light btn-lg" style="border-radius:25px;font-weight:600;color:var(--gasgo-orange);">
                    Register Now <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>
</section>
@endguest

<!-- Featured Products -->
<section class="section-padding">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="section-title">Our Products</h2>
            <p class="section-subtitle">Choose from our range of LPG tanks and accessories</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="product-card">
                    <div class="product-img">
                        <img src="{{ asset('images/11kg.jpg') }}" alt="11kg LPG">
                    </div>
                    <div class="product-body">
                        <h5>LPG Tank 11kg</h5>
                        <p class="product-stock"><i class="fas fa-check-circle me-1"></i>In Stock</p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="product-price">₱850.00</span>
                            <button class="btn btn-gasgo btn-sm add-to-cart-btn" data-id="1" data-name="LPG Tank 11kg" data-price="850" data-image="{{ asset('images/11kg.jpg') }}">
                                <i class="fas fa-cart-plus me-1"></i>Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="product-card">
                    <div class="product-img">
                        <img src="{{ asset('images/22kg.jpg') }}" alt="22kg LPG">
                    </div>
                    <div class="product-body">
                        <h5>LPG Tank 22kg</h5>
                        <p class="product-stock"><i class="fas fa-check-circle me-1"></i>In Stock</p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="product-price">₱1,600.00</span>
                            <button class="btn btn-gasgo btn-sm add-to-cart-btn" data-id="2" data-name="LPG Tank 22kg" data-price="1600" data-image="{{ asset('images/22kg.jpg') }}">
                                <i class="fas fa-cart-plus me-1"></i>Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="product-card">
                    <div class="product-img">
                        <img src="{{ asset('images/2kg.jpg') }}" alt="2kg LPG">
                    </div>
                    <div class="product-body">
                        <h5>LPG Tank 2kg</h5>
                        <p class="product-stock"><i class="fas fa-check-circle me-1"></i>In Stock</p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="product-price">₱350.00</span>
                            <button class="btn btn-gasgo btn-sm add-to-cart-btn" data-id="3" data-name="LPG Tank 2kg" data-price="350" data-image="{{ asset('images/2kg.jpg') }}">
                                <i class="fas fa-cart-plus me-1"></i>Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-center mt-5" data-aos="fade-up">
            <a href="{{ url('/customer/product') }}" class="btn btn-gasgo-outline btn-lg">
                View All Products <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="section-padding how-it-works">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="section-title">How It Works</h2>
            <p class="section-subtitle">Order your LPG in 4 easy steps</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="step-card">
                    <div class="step-icon blue"><i class="fas fa-search"></i></div>
                    <h5 class="fw-bold">1. Browse</h5>
                    <p class="text-muted">Explore our LPG products and accessories</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="step-card">
                    <div class="step-icon orange"><i class="fas fa-cart-plus"></i></div>
                    <h5 class="fw-bold">2. Order</h5>
                    <p class="text-muted">Add to cart and place your order</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="step-card">
                    <div class="step-icon green"><i class="fas fa-map-marked-alt"></i></div>
                    <h5 class="fw-bold">3. Track</h5>
                    <p class="text-muted">Track your delivery in real-time on the map</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="400">
                <div class="step-card">
                    <div class="step-icon purple"><i class="fas fa-hand-holding-heart"></i></div>
                    <h5 class="fw-bold">4. Receive</h5>
                    <p class="text-muted">Get your LPG delivered and earn rewards</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why GasGo -->
<section class="section-padding">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="section-title">Why Choose GasGo?</h2>
            <p class="section-subtitle">We make LPG delivery convenient, safe, and rewarding</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="why-card text-center">
                    <i class="fas fa-shipping-fast" style="color:var(--gasgo-blue);"></i>
                    <h5 class="fw-bold mt-2">Fast Delivery</h5>
                    <p class="text-muted small">Get your LPG delivered within the hour</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="why-card text-center">
                    <i class="fas fa-map-marker-alt" style="color:var(--gasgo-orange);"></i>
                    <h5 class="fw-bold mt-2">Live Tracking</h5>
                    <p class="text-muted small">Track your rider in real-time on the map</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="why-card text-center">
                    <i class="fas fa-award" style="color:#27ae60;"></i>
                    <h5 class="fw-bold mt-2">Rewards Program</h5>
                    <p class="text-muted small">Earn loyalty points and redeem rewards</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="400">
                <div class="why-card text-center">
                    <i class="fas fa-shield-alt" style="color:#8e44ad;"></i>
                    <h5 class="fw-bold mt-2">Safe & Secure</h5>
                    <p class="text-muted small">Certified LPG products with guaranteed quality</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<div id="dashboardData" data-authenticated="{{ Auth::check() ? '1' : '0' }}" style="display:none;"></div>
<script>
// Check if user is authenticated
const isAuthenticated = document.getElementById('dashboardData').dataset.authenticated === '1';

function addToCart(id, name, price, image) {
    // Check if user is authenticated
    if (!isAuthenticated) {
        window.location.href = "{{ route('customer.login') }}";
        return;
    }
    
    let cart = JSON.parse(localStorage.getItem('gasgo_cart')) || [];
    const existing = cart.find(item => item.id === id);
    if (existing) {
        existing.quantity++;
    } else {
        cart.push({ id, name, price, image, quantity: 1 });
    }
    localStorage.setItem('gasgo_cart', JSON.stringify(cart));
    updateCartCount();

    const toast = document.createElement('div');
    toast.className = 'position-fixed bottom-0 end-0 p-3';
    toast.style.zIndex = '9999';
    toast.innerHTML = '<div class="toast show align-items-center text-white border-0" style="background:var(--gasgo-blue);border-radius:12px;"><div class="d-flex"><div class="toast-body"><i class="fas fa-check-circle me-2"></i>' + name + ' added to cart!</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>';
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 2500);
}

document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        addToCart(
            parseInt(this.dataset.id),
            this.dataset.name,
            parseFloat(this.dataset.price),
            this.dataset.image
        );
    });
});
</script>
@endsection
