@extends('layouts.customer')

@section('title', 'Home')
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
        background: #ffffff;
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
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    .product-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 16px 40px rgba(0,0,0,.14);
    }
    .product-card .product-img {
        height: 160px;
        background: white;
        display: flex; align-items: center; justify-content: center;
        position: relative;
        flex-shrink: 0;
    }
    .product-card .product-img img { max-height: 130px; object-fit: contain; }
    .product-badge {
        position: absolute; top: 14px; left: 14px;
        background: var(--gasgo-orange); color: white;
        padding: 4px 14px; border-radius: 20px; font-size: .75rem; font-weight: 600;
        text-transform: capitalize;
    }
    .product-badge.accessory { background: var(--gasgo-blue); }
    .product-card .product-body { 
        padding: 16px;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }
    .product-card .product-body h5 { font-weight: 700; color: #2f2f2f; margin-bottom: 4px; font-size: 1rem; }
    .product-variant {
        flex-grow: 1;
        font-size: .8rem;
    }
    .product-price { font-size: 1.25rem; font-weight: 700; color: var(--gasgo-orange); }
    .product-stock { font-size: .8rem; color: #27ae60; font-weight: 500; }
    .product-stock.out { color: #e74c3c; }

    /* Product Action Buttons */
    .product-actions {
        display: flex;
        gap: 10px;
        margin-top: 16px;
    }

    .product-actions .btn-add {
        flex: 1;
        background: var(--gasgo-orange);
        color: white;
        border: none;
        padding: 10px 16px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .product-actions .btn-add:hover:not(:disabled) {
        background: #f07708;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(247, 148, 29, 0.3);
    }

    .product-actions .btn-add:disabled {
        background: #ccc;
        cursor: not-allowed;
        opacity: 0.6;
    }

    .product-actions .btn-buy {
        flex: 1;
        background: transparent;
        color: var(--gasgo-blue);
        border: 2px solid var(--gasgo-blue);
        padding: 8px 16px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .product-actions .btn-buy:hover:not(:disabled) {
        background: var(--gasgo-blue);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(26, 109, 176, 0.3);
    }

    .product-actions .btn-buy:disabled {
        border-color: #ccc;
        color: #ccc;
        cursor: not-allowed;
        opacity: 0.6;
    }

    /* ===== HOW IT WORKS ===== */
    .how-it-works { background: #ffffff; }
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
        background: #ffffff;
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

</style>
@endsection

@section('content')
@php
    $resolveProductImage = function (?string $path): ?string {
        if (! $path) {
            return null;
        }

        $normalized = ltrim($path, '/');
        if (str_starts_with($normalized, 'http://') || str_starts_with($normalized, 'https://')) {
            return $path;
        }

        if (str_starts_with($normalized, 'storage/')) {
            return asset($normalized);
        }

        return asset('storage/' . $normalized);
    };

    $featuredProducts = collect($products ?? [])->take(4);
    $heroProductImage = $homepageSettings->home_hero_image_url ?: $featuredProducts
        ->map(fn ($product) => $resolveProductImage($product->image ?? null))
        ->first(fn ($image) => filled($image));

    $promoBannerImage = $homepageSettings->promo_banner_image_url ?? null;
@endphp

<!-- Hero -->
<section class="hero-section">
    <span class="hero-glow one"></span>
    <span class="hero-glow two"></span>
    <span class="hero-glow three"></span>
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 hero-content" data-aos="fade-right" data-reveal="slide-up">
                <h1 class="hero-title">{{ $homepageSettings->hero_title_prefix ?? 'Fast, Reliable' }} <span>{{ $homepageSettings->hero_title_highlight ?? 'LPG Delivery' }}</span> {{ $homepageSettings->hero_title_suffix ?? 'to Your Door' }}</h1>
                <p class="hero-subtitle">{{ $homepageSettings->hero_subtitle ?? 'Fast, reliable LPG delivery right to your door. Earn loyalty rewards with every order.' }}</p>
                <a href="{{ url('/customer/product') }}" class="btn btn-gasgo btn-lg">
                    <i class="fas fa-fire me-2"></i>{{ $homepageSettings->hero_primary_button_label ?? 'Browse Products' }}
                </a>
                <div class="hero-badges">
                    <span class="hero-badge"><i class="fas fa-star"></i> Loyalty Rewards</span>
                    <span class="hero-badge"><i class="fas fa-bolt"></i> Fast Delivery</span>
                    <span class="hero-badge"><i class="fas fa-receipt"></i> Order Updates</span>
                </div>
            </div>
            @if($heroProductImage)
                <div class="col-lg-6 text-center" data-aos="fade-left">
                    <img src="{{ $heroProductImage }}" alt="Featured Product" style="max-height:380px;border-radius:20px;">
                </div>
            @endif
        </div>
    </div>
</section>

<!-- Promo Banner -->
@guest
<section class="container" style="position:relative;z-index:2;" data-aos="fade-up">
    <div class="promo-banner" @if($promoBannerImage) style="background-image: linear-gradient(rgba(247,148,29,.82), rgba(255,107,53,.82)), url('{{ $promoBannerImage }}'); background-size: cover; background-position: center;" @endif>
        <div class="row align-items-center">
            <div class="col-md-8">
                <h3><i class="fas fa-gift me-2"></i>{{ $homepageSettings->promo_title ?? 'New User? Get FREE Delivery on Your First Order!' }}</h3>
                <p class="mb-0" style="opacity:.9;">{{ $homepageSettings->promo_subtitle ?? 'Register now and start earning loyalty points with every purchase.' }}</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="{{ url('/customer/loginRegistration?tab=register') }}" class="btn btn-light btn-lg" style="border-radius:25px;font-weight:600;color:var(--gasgo-orange);">
                    {{ $homepageSettings->promo_button_label ?? 'Register Now' }} <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>
</section>
@endguest

<!-- Featured Products -->
<section class="section-padding" style="background:#ffffff;">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="section-title">{{ $homepageSettings->products_section_title ?? 'Our Products' }}</h2>
            <p class="section-subtitle">{{ $homepageSettings->products_section_subtitle ?? 'Choose from our range of LPG tanks and accessories' }}</p>
        </div>
        <div class="row g-4">
            @forelse($featuredProducts as $index => $product)
                @php
                    $img = $resolveProductImage($product->image);
                    $inStock = (int) ($product->quantity_on_hand ?? 0) > 0;
                @endphp
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="{{ ($index + 1) * 100 }}">
                    <div class="product-card">
                        <div class="product-img">
                            @if($img)
                                <img src="{{ $img }}" alt="{{ $product->name }}" class="img-fluid">
                            @else
                                <span class="text-muted small">No image available</span>
                            @endif
                            @if($product->category)
                                <span class="product-badge {{ strtolower($product->category) === 'accessories' ? 'accessory' : '' }}">{{ $product->category }}</span>
                            @endif
                        </div>
                        <div class="product-body">
                            <h5>{{ $product->name }}</h5>
                            @if($product->description)
                                <p class="product-variant mb-2" style="font-size:.85rem; color:#555; margin-bottom:8px; font-weight:500;">{{ $product->description }}</p>
                            @endif
                            <p class="product-stock {{ $inStock ? '' : 'out' }}">
                                <i class="fas {{ $inStock ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>{{ $inStock ? 'In Stock' : 'Out of Stock' }}
                            </p>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center gap-2" style="margin-bottom: 12px;">
                                <span class="product-price">₱{{ number_format($product->price, 2) }}</span>
                            </div>
                            <div class="product-actions" style="flex-direction: column;">
                                <button class="btn-buy buy-now-btn" data-id="{{ $product->id }}" data-name="{{ $product->name }}" data-price="{{ $product->price }}" {{ $inStock ? '' : 'disabled' }} title="Buy Now">
                                    <i class="fas fa-bolt"></i>Buy Now
                                </button>
                                <button class="btn-add add-to-cart-btn" data-id="{{ $product->id }}" data-name="{{ $product->name }}" data-price="{{ $product->price }}" data-image="{{ $img }}" {{ $inStock ? '' : 'disabled' }} title="Add to Cart">
                                    <i class="fas fa-shopping-cart"></i>Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center text-muted py-4">No products available right now.</div>
            @endforelse
        </div>
        <div class="text-center mt-5" data-aos="fade-up">
            <a href="{{ url('/customer/product') }}" class="btn btn-gasgo-outline btn-lg">
                {{ $homepageSettings->products_view_all_label ?? 'View All Products' }} <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="section-padding how-it-works" style="background:#ffffff;">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="section-title">{{ $homepageSettings->how_it_works_title ?? 'How It Works' }}</h2>
            <p class="section-subtitle">{{ $homepageSettings->how_it_works_subtitle ?? 'Order in 4 easy steps' }}</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="step-card">
                    <div class="step-icon blue"><i class="fas fa-search"></i></div>
                    <h5 class="fw-bold">1. Browse</h5>
                    <p class="text-muted">Explore our {{ strtolower($homepageSettings->industry_noun ?? 'products') }} and catalog</p>
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
                    <p class="text-muted">Get your delivery right to your door & earn rewards</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="section-padding" style="background:#f8f9fa;">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="section-title">{{ $homepageSettings->why_choose_title ?? ('Why Choose ' . trim(($homepageSettings->brand_name_primary ?? 'Gas') . ' ' . ($homepageSettings->brand_name_accent ?? 'Go')) . '?') }}</h2>
            <p class="section-subtitle">{{ $homepageSettings->why_choose_subtitle ?? 'We make delivery convenient, safe, and rewarding' }}</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="why-card text-center">
                    <i class="fas fa-shipping-fast" style="color:var(--gasgo-blue);"></i>
                    <h5 class="fw-bold mt-2">Fast Delivery</h5>
                    <p class="text-muted small">Get your {{ strtolower($homepageSettings->industry_noun ?? 'order') }} delivered fast</p>
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
                    <p class="text-muted small">Certified {{ $homepageSettings->industry_noun ?? 'quality' }} products with guaranteed safety</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
const cartStoreUrl = '{{ route("customer.cart.store") }}';
const csrfToken = '{{ csrf_token() }}';

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
    addToCartAjax(id, 1)
        .then(() => {
            // Show success notification with View Cart button
            showNotificationWithAction(`✓ ${name} added to cart!`, 'success', 5000);
        })
        .catch(error => {
            console.error('Add to cart error:', error);
            showNotificationWithAction('Failed to add item to cart', 'error', 3000);
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

function snapshotActionButtonState() {
    document.querySelectorAll('.buy-now-btn, .add-to-cart-btn').forEach(btn => {
        if (!btn.dataset.defaultHtml) {
            btn.dataset.defaultHtml = btn.innerHTML;
            btn.dataset.initialDisabled = btn.disabled ? '1' : '0';
        }
    });
}

function restoreActionButtonState() {
    document.querySelectorAll('.buy-now-btn, .add-to-cart-btn').forEach(btn => {
        if (btn.dataset.defaultHtml) {
            btn.innerHTML = btn.dataset.defaultHtml;
            btn.disabled = btn.dataset.initialDisabled === '1';
        }
    });

    buyNowInProgress = false;
}

window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        restoreActionButtonState();
    }
});

document.querySelectorAll('.buy-now-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        buyNow(parseInt(this.dataset.id));
    });
});

let buyNowInProgress = false;

function buyNow(productId) {
    if (buyNowInProgress) return;

    const button = document.querySelector(`.buy-now-btn[data-id="${productId}"]`);
    if (!button) return;

    buyNowInProgress = true;
    const originalHtml = button.innerHTML;
    const timeoutMs = 10000;
    const timeoutPromise = new Promise((_, reject) => {
        setTimeout(() => reject(new Error('Request timeout')), timeoutMs);
    });

    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>Loading...';

    Promise.race([addToCartAjax(productId, 1), timeoutPromise])
        .then(() => {
            window.location.href = "{{ route('customer.checkout') }}" + '?selected_items=' + productId;
        })
        .catch(error => {
            console.error('Buy Now error:', error);
            button.disabled = false;
            button.innerHTML = originalHtml;
            alert('Unable to process Buy Now. Please try again.');
        })
        .finally(() => {
            buyNowInProgress = false;
        });
}

document.addEventListener('DOMContentLoaded', function() {
    applyTiltEffect();
    applyStaggerReveal();
    snapshotActionButtonState();
});

function showNotificationWithAction(message, type = 'success', duration = 3000) {
    const cartUrl = "{{ route('customer.cart') }}";
    const notification = document.createElement('div');
    
    notification.className = `gasgo-notification ${type === 'error' ? 'error' : ''}`;
    
    const iconHtml = type === 'success' 
        ? '<i class="fas fa-check"></i>' 
        : '<i class="fas fa-exclamation"></i>';
    
    const viewCartHtml = type === 'success' 
        ? `<a href="${cartUrl}" class="notification-link">View Cart</a>` 
        : '';
    
    notification.innerHTML = `
        <div class="notification-icon">${iconHtml}</div>
        <div class="notification-content">
            <div class="notification-text">${message}${viewCartHtml}</div>
        </div>
        <button class="notification-close" aria-label="Close notification">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    const closeBtn = notification.querySelector('.notification-close');
    closeBtn.addEventListener('click', () => {
        notification.classList.add('fade-out');
        setTimeout(() => notification.remove(), 350);
    });
    
    document.body.appendChild(notification);
    
    if (duration) {
        setTimeout(() => {
            if (notification.parentNode) {
                notification.classList.add('fade-out');
                setTimeout(() => notification.remove(), 350);
            }
        }, duration);
    }
}
</script>

<style>
/* ===== NOTIFICATION TOAST ===== */
.gasgo-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    background: #d4f1f0;
    border: 1px solid #a8dcd9;
    border-radius: 12px;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    max-width: 380px;
    width: 90%;
    animation: slideInNotification 0.35s ease-out;
    font-size: 0.95rem;
    color: #1a5a57;
}

.gasgo-notification.error {
    background: #ffe8e8;
    border-color: #ffb3b3;
    color: #8b0000;
}

.gasgo-notification.error .notification-icon {
    background: #ff4444;
}

.notification-icon {
    flex-shrink: 0;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: var(--gasgo-orange);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 1.3rem;
}

.notification-content {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 8px;
}

.notification-text {
    font-weight: 500;
    line-height: 1.4;
}

.notification-link {
    color: var(--gasgo-orange);
    text-decoration: none;
    font-weight: 600;
    margin-left: 8px;
    white-space: nowrap;
    transition: opacity 0.2s;
}

.notification-link:hover {
    opacity: 0.8;
    text-decoration: underline;
}

.notification-error .notification-link {
    color: #8b0000;
}

.notification-close {
    flex-shrink: 0;
    background: none;
    border: none;
    color: #999;
    font-size: 1.3rem;
    cursor: pointer;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.2s;
}

.notification-close:hover {
    color: #333;
}

.notification-error .notification-close {
    color: #b30000;
}

@keyframes slideInNotification {
    from {
        transform: translateX(420px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOutNotification {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(420px);
        opacity: 0;
    }
}

.gasgo-notification.fade-out {
    animation: slideOutNotification 0.35s ease-out forwards;
}

@media (max-width: 576px) {
    .gasgo-notification {
        top: 10px;
        right: 10px;
        left: 10px;
        width: auto;
        max-width: none;
    }
}
</style>
@endsection
