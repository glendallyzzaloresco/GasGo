<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', trim(($homepageSettings->brand_name_primary ?? 'Gas') . ' ' . ($homepageSettings->brand_name_accent ?? 'Go')) . ' - ' . ($homepageSettings->hero_title_highlight ?? 'Delivery'))</title>
    
    <!-- Bootstrap CSS -->
    <link href="{{ asset('bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('fontawesome/css/all.min.css') }}">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        :root {
            --color-primary: {{ $homepageSettings->primary_color ?? '#1a6db0' }};
            --color-accent: {{ $homepageSettings->accent_color ?? '#f7941d' }};
            --color-background: {{ $homepageSettings->background_color ?? '#f8f9fa' }};
            --sidebar-bg: {{ $homepageSettings->sidebar_bg_color ?? '#111b35' }};
            --gasgo-blue: var(--color-primary);
            --gasgo-blue-dark: var(--color-primary);
            --gasgo-blue-light: var(--color-background);
            --gasgo-orange: var(--color-accent);
            --gasgo-orange-dark: var(--color-accent);
            --gasgo-orange-light: var(--color-background);
            --gasgo-gradient: linear-gradient(135deg, var(--color-primary) 0%, #2196f3 100%);
            --gasgo-gradient-orange: linear-gradient(135deg, var(--color-accent) 0%, #ff6b35 100%);
        }
        
        * {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background-color: var(--color-background, #f8f9fa);
            overflow-x: hidden;
            position: relative;
        }
        .hero-section,
        .promo-banner,
        .how-it-works {
        overflow: hidden;
}
        
        /* ==================== NAVBAR ===c================= */
        .navbar-gasgo {
            background: white;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            min-height: 80px;
            display: flex;
            align-items: center;
            padding: 10px 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            transition: background 0.3s ease, box-shadow 0.3s ease;
        }

        .navbar-gasgo .container {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .navbar-gasgo.scrolled {
            background: rgba(255,255,255,0.98);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }
        
        .navbar-brand img {
            height: 50px;
            transition: transform 0.3s ease;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            text-decoration: none;
        }

        .brand-text {
            font-weight: 700;
            color: var(--gasgo-blue);
            font-size: 1.15rem;
            line-height: 1;
            display: inline-block;
        }

        .brand-text .go {
            color: var(--gasgo-orange);
        }

        @media (max-width: 576px) {
            .brand-text { display: inline-block; font-size: 1.05rem; }
            .navbar-brand img { height: 42px; }
        }
        
        .navbar-brand:hover img {
            transform: scale(1.05);
        }
        
        .nav-link-gasgo {
            color: #333 !important;
            font-weight: 500;
            padding: 10px 20px !important;
            margin: 0 5px;
            border-radius: 25px;
            transition: color 0.25s ease, background 0.25s ease, transform 0.25s ease;
            position: relative;
        }
        
        .nav-link-gasgo::after {
            content: '';
            position: absolute;
            bottom: 5px;
            left: 50%;
            width: 0;
            height: 3px;
            background: var(--gasgo-orange);
            transition: width 0.25s ease;
            transform: translateX(-50%);
            border-radius: 2px;
        }
        
        .nav-link-gasgo:hover::after,
        .nav-link-gasgo.active::after {
            width: 30px;
        }

        /* Navbar list defaults */
        .navbar-nav {
            gap: 0.5rem;
        }

        .navbar-nav .nav-item {
            flex: 0 0 auto;
        }

        .nav-link-gasgo {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            min-width: 56px;
            justify-content: center;
        }
        
        .nav-link-gasgo:hover,
        .nav-link-gasgo.active {
            color: var(--gasgo-blue) !important;
        }
        
        .nav-link-gasgo i {
            margin-right: 8px;
            color: var(--gasgo-orange);
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: nowrap;
        }
        
        .btn-nav-cart {
            background: var(--gasgo-gradient-orange);
            color: white !important;
            padding: 10px 25px !important;
            border-radius: 25px;
            font-weight: 600;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .btn-nav-cart:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(247, 148, 29, 0.4);
        }
        
        .cart-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--gasgo-blue);
            color: white;
            font-size: 0.7rem;
            padding: 3px 8px;
            border-radius: 20px;
            animation: pulse 2s infinite;
        }
        
        .btn-nav-login {
            border: 2px solid var(--gasgo-blue);
            color: var(--gasgo-blue) !important;
            padding: 8px 25px !important;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-left: 10px;
        }
        
        .btn-nav-login:hover {
            background: var(--gasgo-blue);
            color: white !important;
        }

        .account-dropdown {
            position: relative;
        }

        .btn-nav-account {
            border: 2px solid var(--gasgo-blue);
            color: var(--gasgo-blue) !important;
            background: white;
            padding: 8px 18px !important;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-nav-account:hover,
        .btn-nav-account.show {
            background: var(--gasgo-blue);
            color: white !important;
        }

        .account-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--gasgo-gradient);
            color: white;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            font-weight: 700;
            box-shadow: 0 6px 16px rgba(26, 109, 176, 0.22);
        }

        .account-dropdown-menu {
            border: none;
            border-radius: 18px;
            box-shadow: 0 18px 40px rgba(0,0,0,0.12);
            padding: 10px;
            min-width: 260px;
            margin-top: 12px;
        }

        .account-summary {
            padding: 12px 14px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--gasgo-blue-light) 0%, #ffffff 100%);
            margin-bottom: 8px;
        }

        .account-summary .name {
            font-weight: 700;
            color: var(--gasgo-blue);
            margin-bottom: 2px;
        }

        .account-summary .meta {
            font-size: 0.82rem;
            color: #6c757d;
            line-height: 1.4;
        }

        .account-dropdown-menu .dropdown-item {
            border-radius: 12px;
            padding: 10px 14px;
            font-weight: 500;
            color: #334155;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .account-dropdown-menu .dropdown-item:hover {
            background: var(--gasgo-orange-light);
            color: var(--gasgo-orange-dark);
        }

        .account-dropdown-menu .dropdown-item i {
            color: var(--gasgo-orange);
            width: 18px;
        }

        .account-dropdown-menu .dropdown-divider {
            margin: 8px 0;
        }

        .account-logout-btn {
            border: none;
            background: transparent;
            width: 100%;
            text-align: left;
        }
        
        /* Mobile Navigation */
        .navbar-toggler {
            border: none;
            padding: 10px;
        }
        
        .navbar-toggler:focus {
            box-shadow: none;
        }
        
        .navbar-toggler-icon {
            background-image: none;
            position: relative;
            width: 30px;
            height: 20px;
        }
        
        .navbar-toggler-icon::before,
        .navbar-toggler-icon::after,
        .navbar-toggler-icon span {
            content: '';
            position: absolute;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--gasgo-blue);
            border-radius: 2px;
            transition: all 0.3s ease;
        }
        
        .navbar-toggler-icon::before { top: 0; }
        .navbar-toggler-icon span { top: 50%; transform: translateY(-50%); }
        .navbar-toggler-icon::after { bottom: 0; }
        
        /* ==================== MAIN CONTENT ==================== */
        .main-content {
            margin-top: 100px; /* match navbar footprint */
            min-height: calc(100vh - 100px - 300px);
        }
        
        body:has(> nav) .main-content {
            margin-top: 100px;
        }
        
        body:not(:has(> nav)) .main-content {
            margin-top: 0;
            min-height: calc(100vh - 300px);
        }

        /* Let Bootstrap control collapsed visibility; only style when expanded */
        .navbar-collapse.show,
        .navbar-collapse.collapsing {
            gap: 1rem;
        }
        
        /* ==================== BUTTONS ==================== */
        .btn-gasgo {
            background: var(--gasgo-gradient-orange);
            border: none;
            color: white;
            padding: 15px 40px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-gasgo::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s ease;
        }
        
        .btn-gasgo:hover::before {
            left: 100%;
        }
        
        .btn-gasgo:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(247, 148, 29, 0.4);
            color: white;
        }
        
        .btn-gasgo-outline {
            border: 2px solid var(--gasgo-blue);
            color: var(--gasgo-blue);
            background: transparent;
            padding: 13px 38px;
            border-radius: 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-gasgo-outline:hover {
            background: var(--gasgo-blue);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(26, 109, 176, 0.3);
        }
        
        .btn-gasgo-blue {
            background: var(--gasgo-gradient);
            border: none;
            color: white;
            padding: 15px 40px;
            border-radius: 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-gasgo-blue:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(26, 109, 176, 0.4);
            color: white;
        }
        
        /* ==================== CARDS ==================== */
        .gasgo-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            overflow: hidden;
            transition: all 0.4s ease;
        }
        
        .gasgo-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
        }
        
        /* ==================== SECTION STYLING ==================== */
        .section-padding {
            padding: 80px 0;
        }
        
        .section-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--gasgo-blue);
            margin-bottom: 15px;
        }
        
        .section-subtitle {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 50px;
        }
        
        /* ==================== FOOTER ==================== */
        .footer-gasgo {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            padding: 80px 0 30px;
            text-align: center;
        }
        
        .footer-gasgo .row {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            flex-wrap: wrap;
            width: 100%;
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .footer-gasgo .row > div {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: flex-start;
            text-align: left;
        }
        
        .footer-logo {
            height: 60px;
            margin-bottom: 20px;
            display: block;
            margin-left: 0;
            margin-right: 0;
        }
        
        .footer-desc {
            color: rgba(255,255,255,0.7);
            margin-bottom: 25px;
            text-align: left;
        }
        
        .footer-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 25px;
            color: var(--gasgo-orange);
            text-align: left;
        }
        
        .footer-links {
            list-style: none;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
            text-align: left;
        }
        
        .footer-links li {
            margin-bottom: 0;
        }
        
        .footer-links a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
        }
        
        .footer-links a i {
            margin-right: 10px;
            color: var(--gasgo-orange);
            width: 20px;
        }
        
        .footer-links a:hover {
            color: white;
            padding-left: 10px;
        }
        
        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 25px;
            justify-content: flex-start;
        }
        
        .social-links a {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }
        
        .social-links a:hover {
            background: var(--gasgo-orange);
            transform: translateY(-5px);
        }
        
        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.1);
            margin-top: 50px;
            padding-top: 30px;
            text-align: center;
            color: rgba(255,255,255,0.5);
        }
        
        /* ==================== ANIMATIONS ==================== */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }
        
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }
        
        .animate-pulse {
            animation: pulse 2s ease-in-out infinite;
        }
        
        /* ==================== SCROLL TO TOP ==================== */
        .scroll-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: var(--gasgo-gradient-orange);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 999;
            box-shadow: 0 5px 20px rgba(247, 148, 29, 0.4);
        }
        
        .scroll-to-top.visible {
            opacity: 1;
            visibility: visible;
        }
        
        .scroll-to-top:hover {
            transform: translateY(-5px);
        }
        
        /* ==================== RESPONSIVE ==================== */
        @media (max-width: 991px) {
            .navbar-collapse {
                background: white;
                padding: 20px;
                border-radius: 15px;
                margin-top: 15px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.1);
                width: 100%;
                position: static;
            }

            .navbar-nav {
                display: flex;
                flex-direction: column;
                align-items: stretch;
                width: 100%;
                gap: 0.25rem;
            }

            .navbar-nav .nav-item {
                width: 100%;
            }
            
            .nav-link-gasgo {
                padding: 15px 20px !important;
                border-radius: 10px;
                margin: 0;
                justify-content: flex-start;
                width: 100%;
            }
            
            .nav-link-gasgo::after {
                display: none;
            }
            
            .nav-link-gasgo:hover {
                background: var(--gasgo-blue-light);
            }
            
            .btn-nav-cart,
            .btn-nav-login,
            .btn-nav-account {
                width: 100%;
                text-align: center;
                margin: 10px 0;
            }

            .nav-actions {
                width: 100%;
                flex-direction: column;
                align-items: stretch;
            }

            .account-dropdown-menu {
                min-width: 100%;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .section-padding {
                padding: 50px 0;
            }
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-top: 90px;
            }
            
            .section-title {
                font-size: 1.75rem;
            }
            
            .btn-gasgo,
            .btn-gasgo-outline {
                padding: 12px 30px;
                font-size: 0.95rem;
            }
        }

        @media (min-width: 992px) {
            .navbar-collapse {
                display: flex !important;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
            }

            .navbar-nav {
                display: flex;
                align-items: center;
                flex-wrap: nowrap;
                justify-content: center;
            }
        }
        
        @media (max-width: 576px) {
            .section-padding {
                padding: 40px 0;
            }
            
            .footer-gasgo {
                padding: 50px 0 20px;
                position: relative;
            }
        }
        
        /* ==================== CUSTOM SCROLLBAR ==================== */
        ::-webkit-scrollbar {
            width: 10px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--gasgo-blue);
            border-radius: 5px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--gasgo-blue-dark);
        }
        
        /* ==================== FORM STYLING ==================== */
        .form-control-gasgo {
            border: 2px solid #eee;
            border-radius: 12px;
            padding: 15px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control-gasgo:focus {
            border-color: var(--gasgo-orange);
            box-shadow: 0 0 0 4px rgba(247, 148, 29, 0.15);
        }
        
        /* ==================== BADGE ==================== */
        .badge-gasgo {
            background: var(--gasgo-orange);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .badge-blue {
            background: var(--gasgo-blue);
        }
        
        /* ==================== ALERT ==================== */
        .alert-gasgo {
            background: var(--gasgo-orange-light);
            border: none;
            border-left: 4px solid var(--gasgo-orange);
            color: var(--gasgo-orange-dark);
            border-radius: 0 12px 12px 0;
            padding: 15px 20px;
        }
    </style>
    
    @yield('styles')
</head>
<body>
    @php
        $navCartCount = Auth::check()
            ? \App\Models\Cart::where('user_id', Auth::id())->sum('quantity')
            : collect(session('cart', []))->sum(fn ($qty) => (int) $qty);
        $industryNoun = $homepageSettings->industry_noun ?? 'LPG Tanks';
        $isWater = str_contains(strtolower($industryNoun), 'water');
        $isFood = str_contains(strtolower($industryNoun), 'food') || str_contains(strtolower($industryNoun), 'meal');
        $isAppliance = str_contains(strtolower($industryNoun), 'appliance');
        $nicheIcon = $isWater ? 'fas fa-tint' : ($isFood ? 'fas fa-utensils' : ($isAppliance ? 'fas fa-blender' : 'fas fa-fire'));
        $nicheColor = $isWater ? 'var(--color-accent, #00b4d8)' : ($isFood ? 'var(--color-accent, #ff922b)' : ($isAppliance ? 'var(--color-accent, #15aabf)' : 'var(--gasgo-orange, #f7941d)'));
    @endphp

    <!-- Navbar -->
    @if (Route::currentRouteName() !== 'customer.login')
    <nav class="navbar navbar-expand-lg navbar-gasgo">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                @if(!empty($homepageSettings->navbar_logo_path))
                    <img data-theme-logo src="{{ $homepageSettings->navbar_logo_url }}" alt="{{ trim(($homepageSettings->brand_name_primary ?? 'Gas') . ' ' . ($homepageSettings->brand_name_accent ?? 'Go')) }} Icon" onerror="this.style.display='none';this.nextElementSibling.style.display='inline-flex';">
                    <span class="brand-avatar-badge" style="display:none;width:34px;height:34px;border-radius:8px;background:{{ $nicheColor }};color:#fff;align-items:center;justify-content:center;font-size:1rem;margin-right:8px;">
                        <i class="{{ $nicheIcon ?? 'fas fa-fire' }}"></i>
                    </span>
                @else
                    <span class="brand-avatar-badge" style="display:inline-flex;width:34px;height:34px;border-radius:8px;background:{{ $nicheColor }};color:#fff;align-items:center;justify-content:center;font-size:1rem;margin-right:8px;">
                        <i class="{{ $nicheIcon ?? 'fas fa-fire' }}"></i>
                    </span>
                @endif
                <span class="brand-text">{{ $homepageSettings->brand_name_primary ?? 'Gas' }}<span class="go">{{ $homepageSettings->brand_name_accent ?? 'Go' }}</span></span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"><span></span></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link nav-link-gasgo @yield('nav-home')" href="{{ url('/') }}">
                            <i class="fas fa-home"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-gasgo @yield('nav-products')" href="{{ url('/customer/product') }}">
                            <i class="fas fa-fire"></i>Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-gasgo @yield('nav-loyalty')" href="{{ url('/customer/loyaltyRewards') }}">
                            <i class="fas fa-gift"></i>Loyalty & Promos
                        </a>
                    </li>
                    <li class="nav-item">
                        @auth
                            <a class="nav-link nav-link-gasgo @yield('nav-orders')" href="{{ url('/customer/orderHistory') }}">
                                <i class="fas fa-receipt"></i>My Orders
                            </a>
                        @else
                            <a class="nav-link nav-link-gasgo" href="{{ url('/customer/loginRegistration') }}" title="Login required" style="opacity: 0.6; cursor: help;">
                                <i class="fas fa-lock me-1"></i>My Orders
                            </a>
                        @endauth
                    </li>
                </ul>
                
                <div class="nav-actions">
                    <a href="{{ url('/customer/productCart') }}" class="nav-link btn-nav-cart">
                        <i class="fas fa-shopping-cart me-2"></i>Cart
                        <span class="cart-badge cart-count">{{ $navCartCount }}</span>
                    </a>
                    @auth
                        <div class="dropdown account-dropdown">
                            <button class="btn btn-nav-account dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="account-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                                <span>My Account</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end account-dropdown-menu">
                                <div class="account-summary">
                                    <div class="name">{{ Auth::user()->name }}</div>
                                </div>
                                <a class="dropdown-item" href="{{ route('customer.profile') }}">
                                    <i class="fas fa-user-circle"></i>Profile Settings
                                </a>
                                <div class="dropdown-divider"></div>
                                <form action="{{ route('customer.logout') }}" method="POST" style="margin: 0;" data-confirm="Are you sure you want to log out of your account?">
                                    @csrf
                                    <button type="submit" class="dropdown-item account-logout-btn" style="border:none;background:none;cursor:pointer;width:100%;text-align:left;">
                                        <i class="fas fa-sign-out-alt"></i>Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ url('/customer/loginRegistration') }}" class="nav-link btn-nav-login">
                            <i class="fas fa-user me-2"></i>Login
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
    @endif
    
    <!-- Main Content -->
    <main class="main-content">
        @yield('content')
    </main>
    
    <!-- Footer -->
    @if (Route::currentRouteName() !== 'customer.login')
    <footer class="footer-gasgo">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-3">
                    <img data-theme-logo src="{{ $homepageSettings->footer_logo_url }}" alt="{{ trim(($homepageSettings->brand_name_primary ?? 'Gas') . ' ' . ($homepageSettings->brand_name_accent ?? 'Go')) }}" class="footer-logo">
                    <p class="footer-desc">
                        {{ $homepageSettings->footer_description ?? 'Your trusted partner for fast, reliable LPG delivery. Track your orders in real-time and earn loyalty & promos with every purchase.' }}
                    </p>
                    <div class="social-links">
                        @if(!empty($homepageSettings->facebook_url))
                            <a href="{{ $homepageSettings->facebook_url }}" target="_blank" rel="noopener noreferrer" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                        @else
                            <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                        @endif

                        @if(!empty($homepageSettings->twitter_url))
                            <a href="{{ $homepageSettings->twitter_url }}" target="_blank" rel="noopener noreferrer" title="Twitter / X"><i class="fab fa-twitter"></i></a>
                        @else
                            <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                        @endif

                        @if(!empty($homepageSettings->instagram_url))
                            <a href="{{ $homepageSettings->instagram_url }}" target="_blank" rel="noopener noreferrer" title="Instagram"><i class="fab fa-instagram"></i></a>
                        @else
                            <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                        @endif

                        @if(!empty($homepageSettings->youtube_url))
                            <a href="{{ $homepageSettings->youtube_url }}" target="_blank" rel="noopener noreferrer" title="YouTube"><i class="fab fa-youtube"></i></a>
                        @else
                            <a href="#" title="YouTube"><i class="fab fa-youtube"></i></a>
                        @endif

                        @if(!empty($homepageSettings->tiktok_url))
                            <a href="{{ $homepageSettings->tiktok_url }}" target="_blank" rel="noopener noreferrer" title="TikTok"><i class="fab fa-tiktok"></i></a>
                        @endif
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-3">
                    <h5 class="footer-title">Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="{{ url('/') }}"><i class="fas fa-chevron-right"></i>Home</a></li>
                        <li><a href="{{ url('/customer/product') }}"><i class="fas fa-chevron-right"></i>Products</a></li>
                        <li><a href="{{ url('/customer/loyaltyRewards') }}"><i class="fas fa-chevron-right"></i>Loyalty & Promos</a></li>
                        @auth
                            <li><a href="{{ url('/customer/orderHistory') }}"><i class="fas fa-chevron-right"></i>My Orders</a></li>
                        @else
                            <li><a href="{{ url('/customer/loginRegistration') }}" style="opacity: 0.7;" title="Login required"><i class="fas fa-lock"></i> My Orders</a></li>
                        @endauth
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-3">
                    <h5 class="footer-title">Contact Us</h5>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fas fa-map-marker-alt"></i>{{ $homepageSettings->contact_address ?? 'PNR Site Estacion San Miguel Calasiao Pangasinan' }}</a></li>
                        <li><a href="tel:{{ preg_replace('/\s+/', '', $homepageSettings->contact_phone ?? '+63 912 345 6789') }}"><i class="fas fa-phone"></i>{{ $homepageSettings->contact_phone ?? '+63 912 345 6789' }}</a></li>
                        <li><a href="mailto:{{ $homepageSettings->contact_email ?? 'info@gasgo.com' }}"><i class="fas fa-envelope"></i>{{ $homepageSettings->contact_email ?? 'info@gasgo.com' }}</a></li>
                        <li><a href="#"><i class="fas fa-clock"></i>{{ $homepageSettings->contact_hours ?? 'Mon-Sun: 6AM - 10PM' }}</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} {{ trim(($homepageSettings->brand_name_primary ?? 'Gas') . ' ' . ($homepageSettings->brand_name_accent ?? 'Go')) }}. All rights reserved. | <a href="{{ route('privacy.policy') }}" class="text-decoration-none" style="color: var(--gasgo-orange);">Privacy Policy</a> | <a href="{{ route('terms.service') }}" class="text-decoration-none" style="color: var(--gasgo-orange);">Terms of Service</a></p>
            </div>
        </div>
    </footer>
    @endif
    
    <!-- Scroll to Top Button -->
    <div class="scroll-to-top" id="scrollToTop">
        <i class="fas fa-arrow-up"></i>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="{{ asset('bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- AOS Animation JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-out-cubic',
            once: true,
            offset: 50
        });
        
        // Navbar scroll effect
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
        
        // Scroll to top functionality
        document.getElementById('scrollToTop').addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
        
        const initialCartCount = Number('{{ (int) $navCartCount }}');

        // Update visible cart badges from server-calculated count.
        function updateCartCount(count = initialCartCount) {
            document.querySelectorAll('.cart-count').forEach(el => {
                el.textContent = count;
            });
        }
        
        updateCartCount();
    </script>
    
    @auth
    @else
    @endauth
    
    <!-- AJAX Route Configuration -->
    @include('components.ajax-routes')
    
    <!-- AJAX Utilities -->
    <script src="{{ asset('js/ajax-utils.js') }}"></script>
    <script src="{{ asset('js/theme-loader.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>
    <script>
        window.gasgoConfirm = function(options = {}) {
            if (typeof options === 'string') {
                options = { text: options };
            }
            const isDelete = Boolean(
                options.isDelete ||
                options.isDanger ||
                (typeof options.text === 'string' && (options.text.toLowerCase().includes('delete') || options.text.toLowerCase().includes('remove') || options.text.toLowerCase().includes('cancel'))) ||
                (typeof options.title === 'string' && (options.title.toLowerCase().includes('delete') || options.title.toLowerCase().includes('remove') || options.title.toLowerCase().includes('cancel')))
            );

            const lottieFile = options.lottie || (
                isDelete 
                    ? '{{ asset("lottie/Delete Icon.lottie") }}' 
                    : (options.icon === 'success' ? '{{ asset("lottie/success.lottie") }}' : '{{ asset("lottie/Warning animation.lottie") }}')
            );

            const htmlContent = `
                <div style="display:flex; justify-content:center; align-items:center; margin-bottom: 10px;">
                    <dotlottie-player src="${lottieFile}" background="transparent" speed="1" style="width: 120px; height: 120px;" loop autoplay></dotlottie-player>
                </div>
                ${options.text ? `<p style="font-size: 0.95rem; color: #64748b; margin-top: 4px; margin-bottom: 0;">${options.text}</p>` : ''}
                ${options.html || ''}
            `;

            return Swal.fire({
                title: options.title || (isDelete ? 'Confirm Action' : 'Are you sure?'),
                html: htmlContent,
                showCancelButton: true,
                confirmButtonColor: options.confirmButtonColor || (isDelete ? '#dc3545' : '#f7941d'),
                cancelButtonColor: options.cancelButtonColor || '#64748b',
                confirmButtonText: options.confirmButtonText || (isDelete ? '<i class="fas fa-trash-alt me-1"></i>Yes, Proceed' : '<i class="fas fa-check me-1"></i>Yes, Confirm'),
                cancelButtonText: options.cancelButtonText || 'Cancel',
                reverseButtons: true,
                focusCancel: isDelete,
                customClass: {
                    popup: 'rounded-4 shadow-lg border-0 p-4',
                    title: 'fw-bold fs-4 text-dark mb-0',
                    confirmButton: 'rounded-pill px-4 py-2 fw-semibold',
                    cancelButton: 'rounded-pill px-4 py-2 fw-semibold me-2'
                }
            }).then(result => result.isConfirmed);
        };

        // Form submission modal confirmation listener
        document.addEventListener('submit', function(e) {
            const form = e.target;
            const confirmMsg = form.getAttribute('data-confirm') || form.dataset.confirm;
            if (confirmMsg && !form.dataset.confirmed) {
                e.preventDefault();
                const isDelete = form.querySelector('[name="_method"][value="DELETE"]') || form.action.includes('delete') || form.action.includes('cancel') || confirmMsg.toLowerCase().includes('cancel');
                window.gasgoConfirm({
                    title: isDelete ? 'Confirm Cancellation' : 'Are you sure?',
                    text: confirmMsg,
                    isDelete: Boolean(isDelete),
                    confirmButtonText: isDelete ? '<i class="fas fa-ban me-1"></i>Yes, Cancel Order' : 'Yes, Proceed'
                }).then(confirmed => {
                    if (confirmed) {
                        form.dataset.confirmed = 'true';
                        form.submit();
                    }
                });
            }
        });
    </script>
    
    @stack('scripts')
    @yield('scripts')
</body>
</html>
