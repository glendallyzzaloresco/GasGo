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
        overflow: hidden;
        isolation: isolate;
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
    .hero-content { position: relative; z-index: 2; }
    .hero-glow {
        position: absolute;
        border-radius: 50%;
        filter: blur(2px);
        opacity: .26;
        pointer-events: none;
        z-index: 1;
    }
    .hero-glow.one {
        width: 220px;
        height: 220px;
        top: 12%;
        left: -70px;
        background: radial-gradient(circle at 30% 30%, #ffffff 0%, rgba(255,255,255,0) 70%);
        animation: driftY 8s ease-in-out infinite;
    }
    .hero-glow.two {
        width: 280px;
        height: 280px;
        right: -80px;
        top: 28%;
        background: radial-gradient(circle at 40% 40%, rgba(247,148,29,.9) 0%, rgba(247,148,29,0) 70%);
        animation: driftY 10s ease-in-out infinite reverse;
    }
    .hero-glow.three {
        width: 180px;
        height: 180px;
        right: 30%;
        bottom: -40px;
        background: radial-gradient(circle at 30% 30%, rgba(255,255,255,.75) 0%, rgba(255,255,255,0) 70%);
        animation: driftY 7s ease-in-out infinite;
    }
    .hero-badges { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 20px; }
    .hero-badge {
        background: rgba(255,255,255,.15);
        backdrop-filter: blur(6px);
        padding: 8px 18px;
        border-radius: 25px;
        font-size: .85rem;
        font-weight: 500;
        display: flex; align-items: center; gap: 6px;
        position: relative;
        overflow: hidden;
    }
    .hero-badge::before {
        content: '';
        position: absolute;
        top: 0;
        left: -140%;
        width: 70%;
        height: 100%;
        background: linear-gradient(120deg, transparent, rgba(255,255,255,.45), transparent);
        animation: badgeShine 4.5s ease-in-out infinite;
    }
    .hero-badge i { color: var(--gasgo-orange); }
    .hero-img {
        max-width: 100%;
        animation: floatCard 5s ease-in-out infinite;
        box-shadow: 0 18px 35px rgba(6, 28, 50, .25);
    }

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

    /* ===== PROMO BANNER ===== */
    .promo-banner {
        background: linear-gradient(135deg, var(--gasgo-orange) 0%, #ff6b35 100%);
        border-radius: 20px;
        padding: 40px;
        color: white;
        margin-top: -40px;
        position: relative;
        z-index: 2;
        box-shadow: 0 15px 50px rgba(247,148,29,.3);
        animation: fadeInUp .8s ease-out .3s backwards;
        border: 1px solid rgba(255,255,255,.1);
        overflow: hidden;
    }
    .promo-banner::before {
        content: '';
        position: absolute;
        top: -50%; right: -50%;
        width: 200%; height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,.1) 0%, transparent 70%);
        animation: float 6s ease-in-out infinite;
    }
    .promo-banner h3 { font-weight: 700; position: relative; z-index: 1; }
    .promo-banner p { position: relative; z-index: 1; }
    .btn-light:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,.15);
    }

    /* ===== PRODUCT CARD ===== */
    .product-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(0,0,0,.08);
        transition: all .4s cubic-bezier(.34,.1,.64,.1);
        height: 100%;
        position: relative;
        border: 1px solid rgba(0,0,0,.05);
    }
    .product-card:hover {
        transform: translateY(-12px);
        box-shadow: 0 20px 60px rgba(0,0,0,.15);
        border-color: rgba(247,148,29,.2);
    }
    .product-card .product-img {
        height: 200px;
        background: linear-gradient(135deg, #f0f4ff 0%, #fff 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }
    .product-card .product-img::after {
        content: '';
        position: absolute;
        top: 0; left: -100%;
        width: 100%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,.4), transparent);
        animation: shimmer 3s infinite;
    }
    .product-card .product-img img {
        max-height: 160px;
        object-fit: contain;
        transition: transform .4s ease-out;
    }
    .product-card:hover .product-img img { transform: scale(1.1); }
    .product-card .product-body { padding: 20px; position: relative; z-index: 1; }
    .product-card .product-body h5 {
        font-weight: 700;
        color: var(--gasgo-blue);
        margin-bottom: 4px;
        transition: color .3s ease-out;
    }
    .product-card:hover .product-body h5 { color: var(--gasgo-orange); }
    .product-price {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--gasgo-orange);
        transition: all .3s ease-out;
    }
    .product-card:hover .product-price { font-size: 1.4rem; }
    .product-stock {
        font-size: .8rem;
        color: #27ae60;
        font-weight: 500;
        transition: all .3s ease-out;
    }
    .product-stock.out { color: #e74c3c; }

    .add-to-cart-btn {
        background: linear-gradient(135deg, var(--gasgo-blue) 0%, #2196f3 100%);
        border: none;
        color: white;
        font-weight: 600;
        transition: all .3s cubic-bezier(.34,.1,.64,.1);
    }
    .add-to-cart-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(26,109,176,.3);
        background: linear-gradient(135deg, #1555a0 0%, #1976d2 100%);
    }

    /* ===== HOW IT WORKS ===== */
    .how-it-works { background: #f8f9fa; }
    .step-card {
        text-align: center;
        padding: 30px 20px;
        border-radius: 20px;
        transition: all .4s cubic-bezier(.34,.1,.64,.1);
        background: white;
        border: 1px solid rgba(0,0,0,.05);
    }
    .step-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(0,0,0,.1);
        border-color: rgba(247,148,29,.2);
    }
    .step-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        color: white;
        margin: 0 auto 18px;
        transition: all .4s cubic-bezier(.34,.1,.64,.1);
        box-shadow: 0 8px 25px rgba(0,0,0,.15);
        position: relative;
    }
    .step-card:hover .step-icon {
        transform: scale(1.15) rotate(5deg);
        box-shadow: 0 12px 35px rgba(0,0,0,.2);
    }
    .step-icon.blue { background: linear-gradient(135deg, #1a6db0, #2196f3); }
    .step-icon.orange { background: linear-gradient(135deg, #f7941d, #ff6b35); }
    .step-icon.green { background: linear-gradient(135deg, #27ae60, #2ecc71); }
    .step-icon.purple { background: linear-gradient(135deg, #8e44ad, #9b59b6); }

    /* ===== WHY CARD ===== */
    .why-card {
        background: white;
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 4px 20px rgba(0,0,0,.06);
        height: 100%;
        border-top: 4px solid transparent;
        transition: all .4s cubic-bezier(.34,.1,.64,.1);
        position: relative;
        overflow: hidden;
    }
    .why-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: linear-gradient(135deg, rgba(247,148,29,.05) 0%, transparent 100%);
        opacity: 0;
        transition: opacity .4s ease-out;
    }
    .why-card:hover {
        border-top-color: var(--gasgo-orange);
        transform: translateY(-8px);
        box-shadow: 0 15px 40px rgba(0,0,0,.12);
    }
    .why-card:hover::before { opacity: 1; }
    .why-card i {
        font-size: 2rem;
        margin-bottom: 14px;
        display: block;
        transition: all .4s cubic-bezier(.34,.1,.64,.1);
    }
    .why-card:hover i {
        transform: scale(1.2) translateY(-5px);
    }
    .why-card h5 {
        position: relative;
        z-index: 1;
        transition: color .3s ease-out;
    }
    .why-card p {
        position: relative;
        z-index: 1;
    }

    /* ===== SECTION TITLES ===== */
    .section-title {
        font-size: 2.2rem;
        font-weight: 800;
        color: var(--gasgo-blue);
        position: relative;
        display: inline-block;
    }
    .section-subtitle {
        color: #666;
        font-size: 1.05rem;
        margin-top: 10px;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .hero-title { font-size: 2rem; }
        .hero-section { padding: 60px 0 60px; }
        .section-title { font-size: 1.8rem; }
        .product-card:hover { transform: translateY(-8px); }
        .step-icon { width: 70px; height: 70px; font-size: 1.5rem; }
    }

    /* ===== ACCESSIBILITY ===== */
    @media (prefers-reduced-motion: reduce) {
        * {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }
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
                <img src="{{ asset('images/gasgo_logo-removebg-preview.png') }}" alt="GasGo Logo" style="max-height:380px;border-radius:20px;">
            </div>
        </div>
    </div>
</section>

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
                            <span class="product-price">&#8369;850.00</span>
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
                            <span class="product-price">&#8369;1,600.00</span>
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
                            <span class="product-price">&#8369;350.00</span>
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
                    <h5 class="fw-bold mt-2">Safe &amp; Secure</h5>
                    <p class="text-muted small">Certified LPG products with guaranteed quality</p>
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

document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
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