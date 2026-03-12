<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'GasGo Rider')</title>
    <link href="{{ asset('bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('fontawesome/css/all.min.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --gasgo-blue: #1a6db0;
            --gasgo-blue-dark: #145a8f;
            --gasgo-blue-light: #e8f4fc;
            --gasgo-orange: #f7941d;
            --gasgo-orange-dark: #e07d0a;
            --gasgo-orange-light: #fff5e6;
        }
        * { font-family: 'Poppins', sans-serif; }
        body { background: #f0f2f5; padding-bottom: 80px; }

        /* ===== TOP NAV ===== */
        .rider-topbar {
            background: linear-gradient(135deg, var(--gasgo-blue), var(--gasgo-blue-dark));
            color: #fff; padding: 16px 20px;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 1030;
        }
        .rider-topbar .brand {
            display: flex; align-items: center; gap: 10px;
        }
        .rider-topbar .brand img { height: 36px; }
        .rider-topbar .brand h5 { margin: 0; font-weight: 700; font-size: 1rem; }
        .rider-topbar .brand h5 span { color: var(--gasgo-orange); }
        .rider-topbar .topbar-right { display: flex; align-items: center; gap: 14px; }
        .rider-topbar .topbar-right .notif-btn {
            background: rgba(255,255,255,.15); border: none; color: #fff;
            width: 38px; height: 38px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem; position: relative; cursor: pointer;
        }
        .rider-topbar .topbar-right .notif-btn .badge-dot {
            position: absolute; top: 4px; right: 4px; width: 8px; height: 8px;
            background: var(--gasgo-orange); border-radius: 50%;
        }
        .rider-avatar {
            width: 36px; height: 36px; border-radius: 50%; background: var(--gasgo-orange);
            color: #fff; display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: .85rem;
        }

        /* ===== BOTTOM NAV (MOBILE) ===== */
        .bottom-nav {
            position: fixed; bottom: 0; left: 0; right: 0;
            background: #fff; border-top: 1px solid #e0e0e0;
            display: flex; justify-content: space-around; padding: 8px 0;
            z-index: 1030;
        }
        .bottom-nav a {
            display: flex; flex-direction: column; align-items: center;
            text-decoration: none; color: #888; font-size: .68rem; font-weight: 600;
            transition: color .3s;
        }
        .bottom-nav a i { font-size: 1.2rem; margin-bottom: 2px; }
        .bottom-nav a.active { color: var(--gasgo-orange); }
        .bottom-nav a:hover { color: var(--gasgo-blue); }

        /* ===== MAIN ===== */
        .rider-main { padding: 20px; max-width: 768px; margin: 0 auto; }

        /* ===== COMMON CARDS ===== */
        .r-card {
            background: #fff; border-radius: 16px; padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,.06); margin-bottom: 16px;
        }
        .r-card h6 { font-weight: 700; color: var(--gasgo-blue); }

        /* ===== STATUS TOGGLE ===== */
        .status-toggle {
            display: flex; gap: 8px; justify-content: center; margin: 16px 0;
        }
        .status-btn {
            padding: 10px 20px; border-radius: 25px; border: 2px solid #e0e0e0;
            background: #fff; font-size: .82rem; font-weight: 600; cursor: pointer;
            transition: all .3s;
        }
        .status-btn.active-available { border-color: #27ae60; background: #27ae60; color: #fff; }
        .status-btn.active-busy { border-color: var(--gasgo-orange); background: var(--gasgo-orange); color: #fff; }
        .status-btn.active-offline { border-color: #999; background: #999; color: #fff; }

        .badge-status { padding: 5px 14px; border-radius: 20px; font-size: .75rem; font-weight: 600; }
        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-assigned { background: #e8f4fc; color: #1a6db0; }
        .badge-out_for_delivery { background: #fff5e6; color: #e07d0a; }
        .badge-delivered { background: #d4edda; color: #155724; }

        @yield('rider-styles')
    </style>
</head>
<body>
    <!-- Top Bar -->
    <header class="rider-topbar">
        <div class="brand">
            <img src="{{ asset('images/gasgo_logo-removebg-preview.png') }}" alt="GasGo">
            <h5>Gas<span>Go</span> Rider</h5>
        </div>
        <div class="topbar-right">
            <button class="notif-btn"><i class="fas fa-bell"></i><span class="badge-dot"></span></button>
            <div class="rider-avatar">R</div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="rider-main">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius:12px;">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @yield('content')
    </main>

    <!-- Bottom Navigation -->
    <nav class="bottom-nav">
        <a href="{{ url('/rider/dashboard') }}" class="@yield('nav-dashboard')"><i class="fas fa-home"></i>Home</a>
        <a href="{{ url('/rider/delivery') }}" class="@yield('nav-delivery')"><i class="fas fa-shipping-fast"></i>Delivery</a>
        <a href="{{ url('/rider/history') }}" class="@yield('nav-history')"><i class="fas fa-history"></i>History</a>
        <a href="{{ url('/rider/profile') }}" class="@yield('nav-profile')"><i class="fas fa-user"></i>Profile</a>
    </nav>

    <script src="{{ asset('bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    @yield('scripts')
</body>
</html>
