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

    /* ===== FEATURED PRODUCTS ===== */
    .product-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(0,0,0,.08);
        transition: transform .35s, box-shadow .35s;
        transform-style: preserve-3d;
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
        transition: transform .3s, box-shadow .3s;
    }
    .step-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 10px 30px rgba(0,0,0,.09);
    }
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

    [data-reveal="slide-up"] {
        animation: revealUp .65s ease both;
    }

    @keyframes floatCard {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }
    @keyframes driftY {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-18px); }
    }
    @keyframes badgeShine {
        0%, 60%, 100% { left: -140%; }
        25% { left: 145%; }
    }
    @keyframes revealUp {
        from {
            opacity: 0;
            transform: translateY(14px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 768px) {
        .hero-title { font-size: 2rem; }
        .hero-section { padding: 60px 0 60px; }
        .hero-glow { opacity: .2; }
    }

    /* ===== LIVE TRACKING WIDGET ===== */
    .live-tracking-widget {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 8px 30px rgba(0,0,0,.08);
        margin-top: -40px;
        position: relative;
        z-index: 2;
    }

    .tracking-mini-map {
        height: 250px;
        border-radius: 16px;
        overflow: hidden;
        background: var(--gasgo-blue-light);
        position: relative;
    }

    .map-overlay {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        color: var(--gasgo-blue);
        z-index: 10;
    }

    .map-overlay i { font-size: 2rem; margin-bottom: 10px; }
    .map-overlay.hidden { display: none; }

    .rider-info-mini {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px;
        background: var(--gasgo-blue-light);
        border-radius: 12px;
        margin-top: 16px;
    }

    .rider-avatar-mini {
        width: 48px; height: 48px;
        border-radius: 50%;
        background: var(--gasgo-blue);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        font-weight: 700;
    }

    .live-dot-dash {
        width: 8px;
        height: 8px;
        background: #27ae60;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
        animation: blink 1.5s infinite;
    }

    @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: .3; }
    }

    .status-badge-live {
        padding: 6px 16px;
        border-radius: 20px;
        font-size: .8rem;
        font-weight: 700;
        color: white;
        background: var(--gasgo-orange);
    }

    .distance-indicator {
        color: var(--gasgo-orange);
        font-weight: 600;
        font-size: .9rem;
    }
</style>
@endsection

@section('content')
<!-- Hero -->
<section class="hero-section">
    <span class="hero-glow one"></span>
    <span class="hero-glow two"></span>
    <span class="hero-glow three"></span>
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 hero-content" data-aos="fade-right" data-reveal="slide-up">
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
                <img src="{{ asset('images/gasgo_logo-removebg-preview.png') }}" alt="GasGo Logo"  style="max-height:380px;border-radius:20px;">
            </div>
        </div>
    </div>
</section>

<!-- Live Tracking Widget (for authenticated users with active orders) -->
@auth
@if($activeOrders && count($activeOrders) > 0)
<section class="container" style="position:relative;z-index:2;" data-aos="fade-up">
    <div class="live-tracking-widget">
        @foreach($activeOrders as $order)
        <div class="tracking-order-widget mb-4"
             data-order-id="{{ $order->id }}"
             data-order-status="{{ $order->status }}"
             data-order-lat="{{ $order->latitude ?? '' }}"
             data-order-lng="{{ $order->longitude ?? '' }}"
             data-delivery-lat="{{ ($order->delivery && $order->delivery->latitude) ? $order->delivery->latitude : '' }}"
             data-delivery-lng="{{ ($order->delivery && $order->delivery->longitude) ? $order->delivery->longitude : '' }}"
             data-status-url="{{ route('customer.tracking.status', $order) }}">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0" style="color:var(--gasgo-blue);">
                    <i class="fas fa-map-marked-alt me-2" style="color:var(--gasgo-orange);"></i>
                    Order #{{ $order->order_number }} - Live Tracking
                </h5>
                <div class="d-flex align-items-center gap-3">
                    <span class="distance-indicator" id="distance-{{ $order->id }}" style="display:none;">
                        <i class="fas fa-route me-1"></i><span class="distance-value">--</span> km away
                    </span>
                    <span class="live-dot-dash"></span>
                    <span style="font-size:.85rem;color:#27ae60;font-weight:600;">Live</span>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="tracking-mini-map" id="miniMap-{{ $order->id }}">
                        <div class="map-overlay" id="mapOverlay-{{ $order->id }}">
                            <i class="fas fa-map-marked-alt"></i>
                            <h6 class="fw-bold">Real-Time Tracking</h6>
                            <p class="text-muted mb-0" id="mapMessage-{{ $order->id }}">
                                @if($order->status === 'assigned')
                                    Rider is preparing your order...
                                @elseif($order->status === 'out_for_delivery')
                                    Your rider is on the way!
                                @else
                                    Loading map...
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="rider-info-mini">
                        <div class="rider-avatar-mini" id="riderAvatar-{{ $order->id }}">
                            @if($order->delivery && $order->delivery->rider)
                                {{ strtoupper(substr($order->delivery->rider->name, 0, 1)) }}
                            @else
                                <i class="fas fa-motorcycle"></i>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="fw-bold mb-1" id="riderName-{{ $order->id }}">
                                @if($order->delivery && $order->delivery->rider)
                                    {{ $order->delivery->rider->name }}
                                @else
                                    Assigning Rider...
                                @endif
                            </h6>
                            <p class="text-muted mb-1" style="font-size:.85rem;" id="riderPhone-{{ $order->id }}">
                                @if($order->delivery && $order->delivery->rider)
                                    <i class="fas fa-phone me-1"></i>{{ $order->delivery->rider->phone ?? 'No contact' }}
                                @else
                                    Finding nearest rider
                                @endif
                            </p>
                            <div class="status-badge-live" id="statusBadge-{{ $order->id }}">
                                {{ $order->status === 'out_for_delivery' ? 'On the way' : 'Preparing' }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted">Total Amount</small>
                            <strong style="color:var(--gasgo-orange);">₱{{ number_format($order->total_amount, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted">Items</small>
                            <small>{{ $order->orderItems->sum('quantity') }} {{ Str::plural('item', $order->orderItems->sum('quantity')) }}</small>
                        </div>
                        <div class="d-flex gap-2 mt-3">
                            <a href="{{ route('customer.tracking', $order->id) }}" class="btn btn-gasgo btn-sm flex-grow-1">
                                <i class="fas fa-eye me-1"></i>Full Tracking
                            </a>
                            @if($order->delivery && $order->delivery->rider && $order->delivery->rider->phone)
                            <a href="tel:{{ $order->delivery->rider->phone }}" class="btn btn-gasgo-outline btn-sm">
                                <i class="fas fa-phone"></i>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if(!$loop->last)
        <hr class="my-4">
        @endif
        @endforeach
    </div>
</section>
@endif
@endauth

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
<!-- Leaflet CSS and JS for live tracking -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="{{ asset('css/leaflet-custom.css') }}" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="{{ asset('js/leaflet-utils.js') }}"></script>

<script>
const cartStoreUrl = '{{ route("customer.cart.store") }}';
const csrfToken = '{{ csrf_token() }}';

// Live tracking functionality
let trackingMaps = {};
let riderMarkers = {};
let destMarkers = {};

@auth
@if($activeOrders && count($activeOrders) > 0)
// Helper function to create rider marker
function createRiderMarker(lat, lng, size = 40) {
    const fontSize = size === 50 ? 22 : 18;
    const border = size === 50 ? 4 : 3;
    const pulseSize = size === 50 ? 20 : 15;

    return L.marker([lat, lng], {
        icon: L.divIcon({
            className: 'rider-marker-icon',
            html: `
                <div class="rider-icon-container" style="
                    width: ${size}px;
                    height: ${size}px;
                    background: #f7941d;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: white;
                    font-size: ${fontSize}px;
                    border: ${border}px solid white;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
                    animation: riderPulse 2s infinite;
                ">
                    <i class="fas fa-motorcycle"></i>
                </div>
                <style>
                @keyframes riderPulse {
                    0%, 100% {
                        box-shadow: 0 2px 8px rgba(0,0,0,0.3), 0 0 0 0 rgba(247, 148, 29, 0.7);
                    }
                    50% {
                        box-shadow: 0 2px 8px rgba(0,0,0,0.3), 0 0 0 ${pulseSize}px rgba(247, 148, 29, 0);
                    }
                }
                </style>
            `,
            iconSize: [size, size],
            iconAnchor: [size/2, size/2]
        })
    });
}

// Initialize tracking for each active order
document.addEventListener('DOMContentLoaded', function() {
    @foreach($activeOrders as $order)
    initOrderTracking({{ $order->id }});
    @endforeach
});

function initOrderTracking(orderId) {
    const widget = document.querySelector(`[data-order-id="${orderId}"]`);
    if (!widget) return;

    const orderStatus = widget.dataset.orderStatus;
    const deliveryLat = widget.dataset.deliveryLat ? parseFloat(widget.dataset.deliveryLat) : null;
    const deliveryLng = widget.dataset.deliveryLng ? parseFloat(widget.dataset.deliveryLng) : null;
    const orderLat = widget.dataset.orderLat ? parseFloat(widget.dataset.orderLat) : null;
    const orderLng = widget.dataset.orderLng ? parseFloat(widget.dataset.orderLng) : null;
    const statusUrl = widget.dataset.statusUrl;

    // Initialize map if rider has location
    if (deliveryLat && deliveryLng) {
        initMiniMap(orderId, deliveryLat, deliveryLng, orderLat, orderLng);
    }

    // Start polling for updates
    if (orderStatus !== 'delivered' && orderStatus !== 'cancelled') {
        setTimeout(() => pollOrderStatus(orderId, statusUrl), 5000);
    }
}

function initMiniMap(orderId, riderLat, riderLng, destLat, destLng) {
    const mapEl = document.getElementById(`miniMap-${orderId}`);
    const overlayEl = document.getElementById(`mapOverlay-${orderId}`);

    if (!mapEl) return;

    // Hide overlay
    overlayEl.classList.add('hidden');

    // Initialize map
    const map = initLeafletMap(`miniMap-${orderId}`, riderLat, riderLng, 14);
    trackingMaps[orderId] = map;

    // Add rider marker (orange with motorcycle icon)
    const riderMarker = createRiderMarker(riderLat, riderLng, 40);
    riderMarker.addTo(map);
    riderMarker.bindPopup('<div style="padding:8px;"><b>🏍️ Your Rider</b><br><small>Delivering your order</small></div>');
    riderMarkers[orderId] = riderMarker;

    // Add destination marker (blue) if coordinates exist
    if (destLat && destLng) {
        const destMarker = createCustomMarker(destLat, destLng, {
            color: '#1a6db0',
            iconType: 'circle',
            size: 20
        });
        destMarker.addTo(map);
        destMarker.bindPopup('<div style="padding:8px;"><b>Your Location</b></div>');
        destMarkers[orderId] = destMarker;

        // Fit bounds to show both markers
        const bounds = L.latLngBounds([
            [riderLat, riderLng],
            [destLat, destLng]
        ]);
        map.fitBounds(bounds, { padding: [30, 30] });

        // Calculate and show distance
        updateDistance(orderId, riderLat, riderLng, destLat, destLng);
    } else {
        map.setView([riderLat, riderLng], 15);
    }
}

function updateRiderPosition(orderId, newLat, newLng) {
    const map = trackingMaps[orderId];
    const riderMarker = riderMarkers[orderId];
    const destMarker = destMarkers[orderId];

    if (!map) {
        // Initialize map if it doesn't exist
        const widget = document.querySelector(`[data-order-id="${orderId}"]`);
        const orderLat = widget.dataset.orderLat ? parseFloat(widget.dataset.orderLat) : null;
        const orderLng = widget.dataset.orderLng ? parseFloat(widget.dataset.orderLng) : null;
        initMiniMap(orderId, newLat, newLng, orderLat, orderLng);
        return;
    }

    if (riderMarker) {
        // Smooth move animation
        smoothMoveMarker(riderMarker, newLat, newLng, 2000);

        // Update distance if destination exists
        if (destMarker) {
            const destLatLng = destMarker.getLatLng();
            updateDistance(orderId, newLat, newLng, destLatLng.lat, destLatLng.lng);

            // Fit bounds to include both markers
            const bounds = L.latLngBounds([
                [newLat, newLng],
                [destLatLng.lat, destLatLng.lng]
            ]);
            map.fitBounds(bounds, { padding: [30, 30] });
        } else {
            map.panTo([newLat, newLng]);
        }
    }
}

function updateDistance(orderId, lat1, lng1, lat2, lng2) {
    const R = 6371; // Earth's radius in kilometers
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLng = (lng2 - lng1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLng/2) * Math.sin(dLng/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    const distance = R * c;

    const distanceEl = document.getElementById(`distance-${orderId}`);
    if (distanceEl) {
        const distanceVal = distanceEl.querySelector('.distance-value');
        if (distanceVal) {
            distanceVal.textContent = distance.toFixed(1);
            distanceEl.style.display = 'inline';
        }
    }
}

function pollOrderStatus(orderId, statusUrl) {
    fetch(statusUrl, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (!data.error) {
            updateOrderUI(orderId, data);
        }

        // Continue polling if order is still active
        if (data.status !== 'delivered' && data.status !== 'cancelled') {
            setTimeout(() => pollOrderStatus(orderId, statusUrl), 5000);
        }
    })
    .catch(error => {
        console.error('Polling error:', error);
        // Retry polling after longer delay on error
        setTimeout(() => pollOrderStatus(orderId, statusUrl), 15000);
    });
}

function updateOrderUI(orderId, data) {
    // Update rider info
    if (data.rider_name) {
        document.getElementById(`riderName-${orderId}`).textContent = data.rider_name;
        const avatar = document.getElementById(`riderAvatar-${orderId}`);
        avatar.textContent = data.rider_name.charAt(0).toUpperCase();

        const phone = document.getElementById(`riderPhone-${orderId}`);
        if (data.rider_phone) {
            phone.innerHTML = `<i class="fas fa-phone me-1"></i>${data.rider_phone}`;
        }
    }

    // Update status badge
    const statusBadge = document.getElementById(`statusBadge-${orderId}`);
    if (data.status === 'out_for_delivery') {
        statusBadge.textContent = 'On the way';
        statusBadge.style.background = 'var(--gasgo-orange)';
    } else if (data.status === 'delivered') {
        statusBadge.textContent = 'Delivered';
        statusBadge.style.background = '#27ae60';
    }

    // Update rider position
    if (data.rider_lat && data.rider_lng) {
        updateRiderPosition(orderId, parseFloat(data.rider_lat), parseFloat(data.rider_lng));
    }

    // Update map overlay message
    const overlay = document.getElementById(`mapOverlay-${orderId}`);
    const message = document.getElementById(`mapMessage-${orderId}`);
    if (data.status === 'delivered') {
        overlay.classList.remove('hidden');
        message.textContent = 'Order delivered successfully!';
    } else if (data.status === 'out_for_delivery' && data.rider_lat && data.rider_lng) {
        overlay.classList.add('hidden');
    }
}
@endif
@endauth

// Give cards a light 3D tilt for a more interactive home screen.
function applyTiltEffect() {
    document.querySelectorAll('.product-card, .why-card').forEach(card => {
        card.addEventListener('mousemove', function(e) {
            if (window.innerWidth < 992) return;
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const rotateY = ((x / rect.width) - 0.5) * 8;
            const rotateX = (0.5 - (y / rect.height)) * 8;
            this.style.transform = `perspective(900px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-5px)`;
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
    });
}

function applyStaggerReveal() {
    const revealItems = document.querySelectorAll('[data-reveal="slide-up"] .hero-badge');
    revealItems.forEach((item, idx) => {
        item.style.animationDelay = (idx * 120) + 'ms';
        item.style.opacity = '0';
        item.style.transform = 'translateY(10px)';
        setTimeout(() => {
            item.style.transition = 'opacity .45s ease, transform .45s ease';
            item.style.opacity = '1';
            item.style.transform = 'translateY(0)';
        }, 250 + (idx * 120));
    });
}

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

document.addEventListener('DOMContentLoaded', function() {
    applyTiltEffect();
    applyStaggerReveal();
});
</script>
@endsection
