<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>GasGo - Fast & Reliable LPG Delivery</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
        <style>
            :root {
                --gasgo-blue: #1a6db0;
                --gasgo-blue-dark: #145a8f;
                --gasgo-blue-light: #e8f4fc;
                --gasgo-orange: #f7941d;
                --gasgo-orange-dark: #e07d0a;
                --gasgo-orange-light: #fff5e6;
                --gasgo-gradient: linear-gradient(135deg, #1a6db0 0%, #2196f3 100%);
                --gasgo-gradient-orange: linear-gradient(135deg, #f7941d 0%, #ff6b35 100%);
            }
            * { font-family: 'Poppins', sans-serif; box-sizing: border-box; margin: 0; padding: 0; }
            body { background: #f8f9fa; overflow-x: hidden; }

            /* ===== NAVBAR ===== */
            .navbar-gasgo {
                background: white;
                box-shadow: 0 2px 20px rgba(0,0,0,0.1);
                min-height: 80px;
                position: fixed;
                top: 0; left: 0; right: 0;
                z-index: 1000;
                transition: background 0.3s ease, box-shadow 0.3s ease;
            }
            .navbar-gasgo.scrolled {
                background: rgba(255,255,255,0.98);
                backdrop-filter: blur(10px);
            }
            .navbar-brand {
                display: flex; align-items: center; gap: 0.6rem; text-decoration: none;
            }
            .navbar-brand img { height: 50px; transition: transform 0.3s ease; }
            .navbar-brand:hover img { transform: scale(1.05); }
            .brand-text { font-weight: 700; color: var(--gasgo-blue); font-size: 1.15rem; }
            .brand-text .go { color: var(--gasgo-orange); }
            .nav-link-gasgo {
                color: #333 !important; font-weight: 500; padding: 10px 20px !important;
                border-radius: 25px; transition: color 0.25s ease, background 0.25s ease;
            }
            .nav-link-gasgo:hover { color: var(--gasgo-blue) !important; }
            .nav-link-gasgo i { margin-right: 8px; color: var(--gasgo-orange); }
            .btn-nav-login {
                border: 2px solid var(--gasgo-blue); color: var(--gasgo-blue) !important;
                padding: 8px 25px !important; border-radius: 25px; font-weight: 600;
                transition: all 0.3s ease; background: none;
            }
            .btn-nav-login:hover { background: var(--gasgo-blue); color: white !important; }
            .btn-nav-register {
                background: var(--gasgo-gradient-orange); color: white !important;
                padding: 10px 25px !important; border-radius: 25px; font-weight: 600;
                border: none; transition: all 0.3s ease;
            }
            .btn-nav-register:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 20px rgba(247,148,29,0.4);
                color: white !important;
            }

            /* ===== HERO ===== */
            .hero-section {
                background: linear-gradient(135deg, #1a6db0 0%, #0d4a7a 60%, #1a1a2e 100%);
                min-height: 100vh;
                display: flex; align-items: center;
                color: white; padding-top: 100px;
                position: relative; overflow: hidden;
            }
            .hero-section::before {
                content: '';
                position: absolute; top: -50%; right: -10%;
                width: 600px; height: 600px;
                background: radial-gradient(circle, rgba(247,148,29,0.15) 0%, transparent 70%);
                border-radius: 50%;
            }
            .hero-section::after {
                content: '';
                position: absolute; bottom: -20%; left: -5%;
                width: 400px; height: 400px;
                background: radial-gradient(circle, rgba(33,150,243,0.2) 0%, transparent 70%);
                border-radius: 50%;
            }
            .hero-badge {
                display: inline-block;
                background: rgba(247,148,29,0.2);
                border: 1px solid rgba(247,148,29,0.4);
                color: var(--gasgo-orange);
                padding: 8px 20px; border-radius: 30px;
                font-size: 0.9rem; font-weight: 600;
                margin-bottom: 25px;
            }
            .hero-title {
                font-size: 3.5rem; font-weight: 800; line-height: 1.2;
                margin-bottom: 20px;
                text-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .hero-title span { color: var(--gasgo-orange); }
            .hero-subtitle {
                font-size: 1.2rem; opacity: 0.9; margin-bottom: 40px; line-height: 1.8;
            }
            .hero-btns { display: flex; gap: 15px; flex-wrap: wrap; }

            /* ===== BUTTONS ===== */
            .btn-gasgo {
                background: var(--gasgo-gradient-orange);
                border: none; color: white;
                padding: 15px 40px; border-radius: 30px;
                font-weight: 600; font-size: 1rem;
                transition: all 0.3s ease;
                display: inline-flex; align-items: center; gap: 8px;
            }
            .btn-gasgo:hover {
                transform: translateY(-3px);
                box-shadow: 0 10px 30px rgba(247,148,29,0.4);
                color: white;
            }
            .btn-gasgo-outline {
                border: 2px solid white; color: white;
                background: transparent;
                padding: 13px 38px; border-radius: 30px;
                font-weight: 600; transition: all 0.3s ease;
                display: inline-flex; align-items: center; gap: 8px;
            }
            .btn-gasgo-outline:hover {
                background: white; color: var(--gasgo-blue);
                transform: translateY(-3px);
            }

            /* ===== STATS ===== */
            .stats-section {
                background: white;
                padding: 50px 0;
                box-shadow: 0 5px 30px rgba(0,0,0,0.08);
            }
            .stat-card {
                text-align: center; padding: 30px 20px;
                border-radius: 15px;
                transition: all 0.3s ease;
            }
            .stat-card:hover { transform: translateY(-5px); }
            .stat-icon {
                width: 60px; height: 60px;
                background: var(--gasgo-blue-light);
                border-radius: 50%;
                display: flex; align-items: center; justify-content: center;
                margin: 0 auto 15px;
                font-size: 1.5rem; color: var(--gasgo-blue);
            }
            .stat-number {
                font-size: 2.5rem; font-weight: 800;
                color: var(--gasgo-blue); margin-bottom: 5px;
            }
            .stat-label { color: #666; font-size: 0.95rem; }

            /* ===== PRODUCTS ===== */
            .products-section { padding: 100px 0; background: #f8f9fa; }
            .section-badge {
                display: inline-block;
                background: var(--gasgo-orange-light);
                color: var(--gasgo-orange);
                padding: 6px 18px; border-radius: 20px;
                font-size: 0.85rem; font-weight: 600;
                margin-bottom: 15px;
            }
            .section-title {
                font-size: 2.5rem; font-weight: 800;
                color: var(--gasgo-blue); margin-bottom: 10px;
            }
            .section-subtitle { color: #666; font-size: 1.05rem; margin-bottom: 50px; }
            .product-card {
                background: white; border-radius: 20px;
                padding: 35px 25px; text-align: center;
                box-shadow: 0 5px 20px rgba(0,0,0,0.06);
                transition: all 0.4s ease; border: 2px solid transparent;
                height: 100%;
            }
            .product-card:hover {
                transform: translateY(-10px);
                box-shadow: 0 20px 50px rgba(26,109,176,0.15);
                border-color: var(--gasgo-blue);
            }
            .product-icon-wrap {
                width: 90px; height: 90px;
                background: var(--gasgo-gradient);
                border-radius: 50%;
                display: flex; align-items: center; justify-content: center;
                margin: 0 auto 20px;
                font-size: 2.2rem; color: white;
                box-shadow: 0 8px 25px rgba(26,109,176,0.3);
            }
            .product-name { font-size: 1.2rem; font-weight: 700; color: var(--gasgo-blue); margin-bottom: 8px; }
            .product-desc { color: #888; font-size: 0.9rem; margin-bottom: 15px; }
            .product-price { font-size: 2rem; font-weight: 800; color: var(--gasgo-orange); margin-bottom: 10px; }
            .product-stock-badge {
                display: inline-block; padding: 5px 15px; border-radius: 20px;
                font-size: 0.8rem; font-weight: 600; margin-bottom: 20px;
            }
            .in-stock { background: #d4edda; color: #155724; }
            .out-stock { background: #f8d7da; color: #721c24; }

            /* ===== HOW IT WORKS ===== */
            .how-section { padding: 100px 0; background: white; }
            .step-card {
                text-align: center; padding: 30px 20px;
            }
            .step-number {
                width: 70px; height: 70px;
                background: var(--gasgo-gradient-orange);
                border-radius: 50%;
                display: flex; align-items: center; justify-content: center;
                margin: 0 auto 20px;
                font-size: 1.8rem; font-weight: 800; color: white;
                box-shadow: 0 8px 25px rgba(247,148,29,0.3);
            }
            .step-title { font-size: 1.1rem; font-weight: 700; color: var(--gasgo-blue); margin-bottom: 10px; }
            .step-desc { color: #666; font-size: 0.9rem; }

            /* ===== CTA ===== */
            .cta-section {
                background: var(--gasgo-gradient);
                padding: 100px 0; text-align: center; color: white;
                position: relative; overflow: hidden;
            }
            .cta-section::before {
                content: '';
                position: absolute; top: -50%; right: -5%;
                width: 500px; height: 500px;
                background: radial-gradient(circle, rgba(247,148,29,0.2) 0%, transparent 70%);
                border-radius: 50%;
            }
            .cta-title { font-size: 2.8rem; font-weight: 800; margin-bottom: 20px; }
            .cta-subtitle { font-size: 1.1rem; opacity: 0.9; margin-bottom: 40px; }

            /* ===== FOOTER ===== */
            .footer-gasgo {
                background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
                color: white; padding: 80px 0 30px;
            }
            .footer-title { font-size: 1.2rem; font-weight: 700; margin-bottom: 25px; color: var(--gasgo-orange); }
            .footer-desc { color: rgba(255,255,255,0.7); margin-bottom: 25px; }
            .footer-links { list-style: none; padding: 0; }
            .footer-links li { margin-bottom: 12px; }
            .footer-links a {
                color: rgba(255,255,255,0.7); text-decoration: none;
                transition: all 0.3s ease; display: inline-flex; align-items: center;
            }
            .footer-links a i { margin-right: 10px; color: var(--gasgo-orange); width: 20px; }
            .footer-links a:hover { color: white; padding-left: 5px; }
            .social-links { display: flex; gap: 15px; margin-top: 25px; }
            .social-links a {
                width: 45px; height: 45px; border-radius: 50%;
                background: rgba(255,255,255,0.1);
                display: flex; align-items: center; justify-content: center;
                color: white; font-size: 1.1rem; transition: all 0.3s ease; text-decoration: none;
            }
            .social-links a:hover { background: var(--gasgo-orange); transform: translateY(-5px); }
            .footer-bottom {
                border-top: 1px solid rgba(255,255,255,0.1);
                margin-top: 50px; padding-top: 30px;
                text-align: center; color: rgba(255,255,255,0.5);
            }

            /* ===== SCROLL TO TOP ===== */
            .scroll-to-top {
                position: fixed; bottom: 30px; right: 30px;
                width: 50px; height: 50px;
                background: var(--gasgo-gradient-orange);
                color: white; border-radius: 50%;
                display: flex; align-items: center; justify-content: center;
                font-size: 1.2rem; cursor: pointer;
                opacity: 0; visibility: hidden;
                transition: all 0.3s ease; z-index: 999;
                box-shadow: 0 5px 20px rgba(247,148,29,0.4);
            }
            .scroll-to-top.visible { opacity: 1; visibility: visible; }
            .scroll-to-top:hover { transform: translateY(-5px); }

            /* ===== RESPONSIVE ===== */
            @media (max-width: 768px) {
                .hero-title { font-size: 2.2rem; }
                .section-title { font-size: 1.8rem; }
                .cta-title { font-size: 2rem; }
            }
            @media (max-width: 576px) {
                .brand-text { display: none; }
            }
        </style>
    </head>
    <body>
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-gasgo">
            <div class="container">
                <a class="navbar-brand" href="#">
                    <img src="{{ asset('images/gasgo_logo-removebg-preview.png') }}" alt="GasGo" onerror="this.style.display='none'">
                    <span class="brand-text">Gas<span class="go">Go</span></span>
                </a>
                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav mx-auto">
                        <li class="nav-item">
                            <a class="nav-link nav-link-gasgo" href="#products">
                                <i class="fas fa-fire"></i>Products
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-gasgo" href="#how-it-works">
                                <i class="fas fa-info-circle"></i>How It Works
                            </a>
                        </li>
                    </ul>
                    <div class="d-flex align-items-center gap-2">
                        @auth
                            <a href="{{ url('/customer/customerDashboard') }}" class="btn-nav-register text-decoration-none px-4 py-2 rounded-pill">
                                <i class="fas fa-home me-2"></i>Dashboard
                            </a>
                            <form action="{{ route('customer.logout') }}" method="POST" style="margin:0;">
                                @csrf
                                <button type="submit" class="btn-nav-login">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </button>
                            </form>
                        @else
                            <a href="{{ route('customer.login') }}" class="btn-nav-login text-decoration-none">
                                <i class="fas fa-user me-1"></i> Login
                            </a>
                            @if (Route::has('customer.register'))
                                <a href="{{ route('customer.register') }}" class="btn-nav-register text-decoration-none">
                                    Register
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="hero-section">
            <div class="container position-relative" style="z-index:1;">
                <div class="row align-items-center">
                    <div class="col-lg-7" data-aos="fade-right">
                        <div class="hero-badge">
                            <i class="fas fa-bolt me-2"></i>Fast LPG Delivery
                        </div>
                        <h1 class="hero-title">
                            Your <span>LPG Gas</span><br>Delivered Fast &<br>Reliably
                        </h1>
                        <p class="hero-subtitle">
                            Order your LPG gas cylinders and get them delivered to your doorstep.
                            Track in real-time and earn rewards with every purchase.
                        </p>
                        <div class="hero-btns">
                            @auth
                                <a href="{{ route('customer.orders') }}" class="btn-gasgo">
                                    <i class="fas fa-shopping-cart"></i> Order Now
                                </a>
                                <a href="{{ url('/customer/customerDashboard') }}" class="btn-gasgo-outline">
                                    <i class="fas fa-home"></i> Dashboard
                                </a>
                            @else
                                <a href="{{ route('customer.login') }}" class="btn-gasgo">
                                    <i class="fas fa-bolt"></i> Get Started
                                </a>
                                @if (Route::has('customer.register'))
                                    <a href="{{ route('customer.register') }}" class="btn-gasgo-outline">
                                        <i class="fas fa-user-plus"></i> Register Free
                                    </a>
                                @endif
                            @endauth
                        </div>
                    </div>
                    <div class="col-lg-5 text-center mt-5 mt-lg-0" data-aos="fade-left" data-aos-delay="200">
                        <div style="font-size: 12rem; opacity: 0.15; animation: float 3s ease-in-out infinite;">
                            <i class="fas fa-fire"></i>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="stats-section">
            <div class="container">
                <div class="row">
                    <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="0">
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-receipt"></i></div>
                            <div class="stat-number">{{ $totalOrders ?? 0 }}</div>
                            <div class="stat-label">Total Orders</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="100">
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-users"></i></div>
                            <div class="stat-number">{{ $totalCustomers ?? 0 }}</div>
                            <div class="stat-label">Active Customers</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="200">
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-motorcycle"></i></div>
                            <div class="stat-number">{{ $activeRiders ?? 0 }}</div>
                            <div class="stat-label">Active Riders</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="300">
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-clock"></i></div>
                            <div class="stat-number">{{ $pendingOrders ?? 0 }}</div>
                            <div class="stat-label">Pending Orders</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Products Section -->
        <section class="products-section" id="products">
            <div class="container">
                <div class="text-center" data-aos="fade-up">
                    <div class="section-badge"><i class="fas fa-fire me-2"></i>Our Products</div>
                    <h2 class="section-title">LPG Products We Offer</h2>
                    <p class="section-subtitle">Choose from our wide range of LPG cylinders for your home or business needs</p>
                </div>
                <div class="row g-4">
                    @forelse($products ?? [] as $product)
                        <div class="col-md-4" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                            <div class="product-card">
                                <div class="product-icon-wrap">
                                    <i class="fas fa-fire"></i>
                                </div>
                                <h3 class="product-name">{{ $product->name }}</h3>
                                <p class="product-desc">{{ Str::limit($product->description ?? '', 60) }}</p>
                                <div class="product-price">&#8369;{{ number_format($product->price, 2) }}</div>
                                <div class="mb-3">
                                    @if($product->stock > 0)
                                        <span class="product-stock-badge in-stock"><i class="fas fa-check-circle me-1"></i>In Stock ({{ $product->stock }})</span>
                                    @else
                                        <span class="product-stock-badge out-stock"><i class="fas fa-times-circle me-1"></i>Out of Stock</span>
                                    @endif
                                </div>
                                @if($product->stock > 0)
                                    @auth
                                        <a href="{{ route('customer.products') }}" class="btn-gasgo" style="font-size:0.9rem; padding:10px 25px;">
                                            <i class="fas fa-shopping-cart"></i> Order Now
                                        </a>
                                    @else
                                        <a href="{{ route('customer.login') }}" class="btn-gasgo" style="font-size:0.9rem; padding:10px 25px;">
                                            <i class="fas fa-sign-in-alt"></i> Login to Order
                                        </a>
                                    @endauth
                                @else
                                    <button class="btn btn-secondary btn-sm" disabled>Out of Stock</button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center text-muted py-5">
                            <i class="fas fa-box-open fa-3x mb-3 text-muted"></i>
                            <p>No products available at the moment.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <!-- How It Works -->
        <section class="how-section" id="how-it-works">
            <div class="container">
                <div class="text-center mb-5" data-aos="fade-up">
                    <div class="section-badge"><i class="fas fa-list-ol me-2"></i>Simple Steps</div>
                    <h2 class="section-title">How It Works</h2>
                    <p class="section-subtitle">Get your LPG delivered in 3 easy steps</p>
                </div>
                <div class="row g-4">
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="0">
                        <div class="step-card">
                            <div class="step-number">1</div>
                            <h4 class="step-title">Choose Your Product</h4>
                            <p class="step-desc">Browse our selection of LPG cylinders and select the one that fits your needs.</p>
                        </div>
                    </div>
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="150">
                        <div class="step-card">
                            <div class="step-number">2</div>
                            <h4 class="step-title">Place Your Order</h4>
                            <p class="step-desc">Enter your delivery address, choose a payment method, and confirm your order.</p>
                        </div>
                    </div>
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                        <div class="step-card">
                            <div class="step-number">3</div>
                            <h4 class="step-title">Fast Delivery</h4>
                            <p class="step-desc">Our rider picks up your order and delivers it to your doorstep. Track in real-time!</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
            <div class="container position-relative" style="z-index:1;" data-aos="fade-up">
                <h2 class="cta-title">Ready to Order Your LPG?</h2>
                <p class="cta-subtitle">Join thousands of satisfied customers and get your gas delivered today</p>
                @auth
                    <a href="{{ route('customer.orders') }}" class="btn-gasgo">
                        <i class="fas fa-shopping-cart"></i> Place Your Order
                    </a>
                @else
                    <a href="{{ route('customer.login') }}" class="btn-gasgo me-3">
                        <i class="fas fa-bolt"></i> Get Started
                    </a>
                    @if (Route::has('customer.register'))
                        <a href="{{ route('customer.register') }}" class="btn-gasgo-outline">
                            <i class="fas fa-user-plus"></i> Create Account
                        </a>
                    @endif
                @endauth
            </div>
        </section>

        <!-- Footer -->
        <footer class="footer-gasgo">
            <div class="container">
                <div class="row g-4">
                    <div class="col-lg-4 col-md-6">
                        <h5 class="footer-title">GasGo</h5>
                        <p class="footer-desc">
                            Your trusted partner for fast, reliable LPG delivery. Track your orders in real-time and earn rewards with every purchase.
                        </p>
                        <div class="social-links">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <h5 class="footer-title">Quick Links</h5>
                        <ul class="footer-links">
                            <li><a href="{{ route('customer.login') }}"><i class="fas fa-chevron-right"></i>Login</a></li>
                            @if (Route::has('customer.register'))
                                <li><a href="{{ route('customer.register') }}"><i class="fas fa-chevron-right"></i>Register</a></li>
                            @endif
                            <li><a href="#products"><i class="fas fa-chevron-right"></i>Products</a></li>
                            <li><a href="#how-it-works"><i class="fas fa-chevron-right"></i>How It Works</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <h5 class="footer-title">Contact Us</h5>
                        <ul class="footer-links">
                            <li><a href="#"><i class="fas fa-map-marker-alt"></i>123 Gas Street, Metro City</a></li>
                            <li><a href="tel:+639123456789"><i class="fas fa-phone"></i>+63 912 345 6789</a></li>
                            <li><a href="mailto:info@gasgo.com"><i class="fas fa-envelope"></i>info@gasgo.com</a></li>
                            <li><a href="#"><i class="fas fa-clock"></i>Mon-Sun: 6AM - 10PM</a></li>
                        </ul>
                    </div>
                </div>
                <div class="footer-bottom">
                    <p>&copy; 2026 GasGo. All rights reserved. |
                        <a href="#" class="text-decoration-none" style="color: var(--gasgo-orange);">Privacy Policy</a> |
                        <a href="#" class="text-decoration-none" style="color: var(--gasgo-orange);">Terms of Service</a>
                    </p>
                </div>
            </div>
        </footer>

        <!-- Scroll to Top -->
        <div class="scroll-to-top" id="scrollToTop">
            <i class="fas fa-arrow-up"></i>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
        <script>
            AOS.init({ duration: 800, easing: 'ease-out-cubic', once: true, offset: 50 });

            window.addEventListener('scroll', function() {
                const navbar = document.querySelector('.navbar-gasgo');
                const scrollToTop = document.getElementById('scrollToTop');
                if (window.scrollY > 50) {
                    navbar.classList.add('scrolled');
                    scrollToTop.classList.add('visible');
                } else {
                    navbar.classList.remove('scrolled');
                    scrollToTop.classList.remove('visible');
                }
            });

            document.getElementById('scrollToTop').addEventListener('click', function() {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        </script>
    </body>
</html>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            :root {
                --gasgo-blue: #1a6db0;
                --gasgo-orange: #f7941d;
            }
            body {
                font-family: 'Poppins', sans-serif;
                background: linear-gradient(135deg, var(--gasgo-blue) 0%, #0d4a7a 100%);
                color: #333;
                min-height: 100vh;
            }
            .navbar {
                background: rgba(255,255,255,0.95) !important;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }
            .navbar-brand {
                font-size: 1.5rem;
                font-weight: 700;
                color: var(--gasgo-blue) !important;
            }
            .hero {
                color: white;
                padding: 80px 0;
                text-align: center;
            }
            .hero h1 {
                font-size: 3rem;
                font-weight: 700;
                margin-bottom: 20px;
                text-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .hero p {
                font-size: 1.2rem;
                margin-bottom: 40px;
                opacity: 0.95;
            }
            .btn-gasgo {
                background: var(--gasgo-orange);
                color: white;
                padding: 12px 40px;
                border-radius: 8px;
                border: none;
                font-weight: 600;
                transition: all 0.3s ease;
            }
            .btn-gasgo:hover {
                background: #e07d0f;
                transform: translateY(-2px);
                box-shadow: 0 8px 16px rgba(247,148,29,0.3);
                color: white;
                text-decoration: none;
            }
            .products-section {
                background: white;
                padding: 80px 0;
            }
            .products-section h2 {
                text-align: center;
                font-size: 2.5rem;
                font-weight: 700;
                margin-bottom: 60px;
                color: var(--gasgo-blue);
            }
            .product-card {
                background: white;
                border-radius: 12px;
                padding: 30px;
                text-align: center;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                transition: all 0.3s ease;
                border: 2px solid #f0f0f0;
            }
            .product-card:hover {
                transform: translateY(-8px);
                box-shadow: 0 8px 24px rgba(26,109,176,0.2);
                border-color: var(--gasgo-blue);
            }
            .product-icon {
                width: 80px;
                height: 80px;
                background: linear-gradient(135deg, var(--gasgo-blue), var(--gasgo-orange));
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 20px;
                font-size: 2rem;
                color: white;
            }
            .product-card h3 {
                color: var(--gasgo-blue);
                font-weight: 700;
                margin-bottom: 10px;
            }
            .product-price {
                font-size: 1.8rem;
                font-weight: 700;
                color: var(--gasgo-orange);
                margin: 15px 0;
            }
            .product-stock {
                font-size: 0.9rem;
                color: #666;
            }
            .stats-section {
                background: rgba(255,255,255,0.1);
                color: white;
                padding: 60px 0;
            }
            .stat-item {
                text-align: center;
                margin: 20px 0;
            }
            .stat-number {
                font-size: 2.5rem;
                font-weight: 700;
                margin-bottom: 10px;
            }
            .stat-label {
                font-size: 1rem;
                opacity: 0.9;
            }
            .cta-section {
                background: white;
                padding: 60px 0;
                text-align: center;
            }
            .cta-section h2 {
                color: var(--gasgo-blue);
                font-size: 2rem;
                font-weight: 700;
                margin-bottom: 30px;
            }
            footer {
                background: #1a1a1a;
                color: white;
                padding: 30px 0;
                text-align: center;
            }
        </style>
    </head>
    <body>
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-light sticky-top">
            <div class="container">
                <a class="navbar-brand" href="#">
                    <i class="fas fa-gas-pump" style="color: var(--gasgo-orange);"></i> GasGo
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <div class="ms-auto">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="btn btn-gasgo me-2">
                                Dashboard
                            </a>
                            <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary">Logout</button>
                            </form>
                        @else
                            <a href="{{ route('customer.login') }}" class="btn btn-outline-secondary me-2">
                                Login
                            </a>
                            @if (Route::has('customer.register'))
                                <a href="{{ route('customer.register') }}" class="btn btn-gasgo">
                                    Register
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="hero">
            <div class="container">
                <h1>Fast & Reliable LPG Delivery</h1>
                <p>Order your LPG gas cylinders and get them delivered to your doorstep in minutes</p>
                @auth
                    <a href="{{ route('customer.orders') }}" class="btn btn-gasgo">
                        Order Now
                    </a>
                @else
                    <a href="{{ route('customer.login') }}" class="btn btn-gasgo">
                        Get Started
                    </a>
                @endauth
            </div>
        </section>

        <!-- Stats Section -->
        <section class="stats-section">
            <div class="container">
                <div class="row">
                    <div class="col-md-3">
                        <div class="stat-item">
                            <div class="stat-number">{{ $totalOrders ?? 0 }}</div>
                            <div class="stat-label">Total Orders</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-item">
                            <div class="stat-number">{{ $totalCustomers ?? 0 }}</div>
                            <div class="stat-label">Active Customers</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-item">
                            <div class="stat-number">{{ $activeRiders ?? 0 }}</div>
                            <div class="stat-label">Active Riders</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-item">
                            <div class="stat-number">{{ $pendingOrders ?? 0 }}</div>
                            <div class="stat-label">Pending Orders</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Products Section -->
        <section class="products-section">
            <div class="container">
                <h2>Our Products</h2>
                <div class="row">
                    @forelse($products ?? [] as $product)
                        <div class="col-md-4 mb-4">
                            <div class="product-card">
                                <div class="product-icon">
                                    <i class="fas fa-cube"></i>
                                </div>
                                <h3>{{ $product->name }}</h3>
                                <p class="text-muted">{{ substr($product->description ?? '', 0, 60) }}...</p>
                                <div class="product-price">â‚±{{ number_format($product->price, 2) }}</div>
                                <div class="product-stock">
                                    <span class="badge @if($product->stock > 0) bg-success @else bg-danger @endif">
                                        Stock: {{ $product->stock }}
                                    </span>
                                </div>
                                @if($product->stock > 0)
                                    @auth
                                        <a href="{{ route('customer.orders') }}" class="btn btn-gasgo btn-sm mt-3">Order Now</a>
                                    @else
                                        <a href="{{ route('customer.login') }}" class="btn btn-gasgo btn-sm mt-3">Order Now</a>
                                    @endauth
                                @else
                                    <button class="btn btn-secondary btn-sm mt-3" disabled>Out of Stock</button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center text-muted">
                            <p>No products available at the moment.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
            <div class="container">
                <h2>Ready to Order?</h2>
                <p class="text-muted mb-4">Join thousands of satisfied customers getting their LPG delivered fast</p>
                @auth
                    <a href="{{ route('customer.orders') }}" class="btn btn-gasgo">
                        Place Your Order
                    </a>
                @else
                    <a href="{{ route('customer.login') }}" class="btn btn-gasgo me-2">
                        Login
                    </a>
                    @if (Route::has('customer.register'))
                        <a href="{{ route('customer.register') }}" class="btn btn-outline-primary">
                            Create Account
                        </a>
                    @endif
                @endauth
            </div>
        </section>

        <!-- Footer -->
        <footer>
            <div class="container">
                <p>&copy; 2026 GasGo. Fast LPG Delivery Service. All rights reserved.</p>
            </div>
        </footer>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
