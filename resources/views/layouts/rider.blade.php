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
            --color-primary: #1a6db0;
            --color-accent: #f7941d;
            --color-background: #f4f7fb;
            --sidebar-bg: #111b35;
            --gasgo-blue: var(--color-primary);
            --gasgo-blue-dark: var(--color-primary);
            --gasgo-blue-light: var(--color-background);
            --gasgo-orange: var(--color-accent);
            --gasgo-orange-dark: var(--color-accent);
            --gasgo-orange-light: var(--color-background);
            --sidebar-width: 260px;
            --admin-bg: var(--color-background);
            --admin-border: #e8eef5;
        }
        * { font-family: 'Poppins', sans-serif; }
        body {
            background: radial-gradient(circle at top right, #ffffff 0%, var(--color-background) 48%, #edf2f8 100%);
        }

        /* ===== SIDEBAR ===== */
        .rider-sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0; left: 0;
            background: linear-gradient(180deg, #111b35 0%, #17254a 55%, #1a2f63 100%);
            color: #fff;
            z-index: 1040;
            transition: transform .3s;
            overflow-y: auto;
            border-right: 1px solid rgba(255,255,255,.08);
        }
        .rider-sidebar::before,
        .rider-sidebar::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
        }
        .rider-sidebar::before {
            width: 170px;
            height: 170px;
            top: -40px;
            right: -55px;
            background: radial-gradient(circle at center, rgba(247,148,29,.25), rgba(247,148,29,0));
        }
        .rider-sidebar::after {
            width: 180px;
            height: 180px;
            bottom: 60px;
            left: -65px;
            background: radial-gradient(circle at center, rgba(33,150,243,.22), rgba(33,150,243,0));
        }
        .sidebar-brand {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255,255,255,.1);
            display: flex; align-items: center; gap: 12px;
            position: sticky;
            top: 0;
            z-index: 2;
            background: rgba(17,27,53,.88);
            backdrop-filter: blur(6px);
        }
        .sidebar-brand img { height: 42px; }
        .sidebar-brand h4 { margin: 0; font-weight: 700; font-size: 1.15rem; }
        .sidebar-brand h4 span { color: var(--gasgo-orange); }
        .sidebar-menu { list-style: none; padding: 16px 0; margin: 0; position: relative; z-index: 1; }
        .sidebar-menu li a {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 24px; color: rgba(255,255,255,.7);
            text-decoration: none; font-size: .9rem; font-weight: 500;
            border-left: 3px solid transparent;
            transition: all .25s;
            border-radius: 0 16px 16px 0;
            margin-right: 10px;
        }
        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            background: rgba(255,255,255,.08);
            color: #fff;
            border-left-color: var(--gasgo-orange);
            transform: translateX(4px);
        }
        .sidebar-menu li a i {
            width: 20px;
            text-align: center;
            color: var(--gasgo-orange);
            transition: transform .2s ease;
        }
        .sidebar-menu li a:hover i,
        .sidebar-menu li a.active i {
            transform: scale(1.08);
        }
        .sidebar-section { padding: 10px 24px 4px; font-size: .7rem; text-transform: uppercase; letter-spacing: 1px; color: rgba(255,255,255,.35); }

        /* ===== TOP BAR ===== */
        .rider-topbar {
            margin-left: var(--sidebar-width);
            height: 64px;
            background: rgba(255,255,255,.86);
            border-bottom: 1px solid var(--admin-border);
            backdrop-filter: blur(10px);
            box-shadow: 0 6px 22px rgba(15,23,42,.05);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 28px;
            position: sticky; top: 0; z-index: 1030;
        }
        .rider-topbar .page-title { font-weight: 700; font-size: 1.1rem; color: var(--gasgo-blue); }
        .rider-topbar .topbar-right { display: flex; align-items: center; gap: 18px; }
        .rider-topbar .topbar-right .notif-btn {
            background: none; border: none; font-size: 1.2rem; color: #666; position: relative; cursor: pointer;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            transition: background .2s ease, color .2s ease;
        }
        .rider-topbar .topbar-right .notif-btn:hover {
            background: #eef5ff;
            color: var(--gasgo-blue);
        }
        .rider-topbar .topbar-right .notif-btn .badge-dot {
            position: absolute; top: 0; right: 0; width: 8px; height: 8px;
            background: var(--gasgo-orange); border-radius: 50%;
        }
        .rider-avatar {
            width: 36px; height: 36px; border-radius: 50%; background: var(--gasgo-blue);
            color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: .85rem;
            border: none; cursor: pointer; transition: transform .3s;
            box-shadow: 0 8px 18px rgba(26,109,176,.3);
        }
        .rider-avatar:hover { transform: scale(1.08) translateY(-1px); }
        .dropdown-menu .dropdown-item {
            font-size: .9rem; padding: 10px 16px; border-radius: 0;
        }
        .dropdown-menu .dropdown-item:hover, .dropdown-menu .dropdown-item:focus {
            background: var(--gasgo-blue-light); color: var(--gasgo-blue);
        }
        .dropdown-menu .dropdown-item i { width: 18px; }
        .dropdown-divider { margin: 6px 0; border-color: #e0e0e0; }

        /* ===== MAIN ===== */
        .rider-main {
            margin-left: var(--sidebar-width);
            padding: 28px;
            min-height: calc(100vh - 64px);
        }

        /* ===== CARD STYLES ===== */
        .rider-card {
            background: linear-gradient(180deg, #ffffff 0%, #f9fcff 100%);
            border: 1px solid var(--admin-border);
            border-radius: 18px;
            padding: 22px;
            box-shadow: 0 6px 22px rgba(0,0,0,.05);
            transition: transform .3s, box-shadow .3s;
            position: relative;
            overflow: hidden;
        }
        .rider-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, var(--gasgo-blue), var(--gasgo-orange));
        }
        .rider-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 16px 34px rgba(15,23,42,.1);
        }
        .rider-card .card-icon {
            width: 48px; height: 48px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem; color: #fff;
        }
        .rider-card .card-icon.blue { background: linear-gradient(135deg, #1a6db0, #2196f3); }
        .rider-card .card-icon.orange { background: linear-gradient(135deg, #f7941d, #ff6b35); }
        .rider-card .card-icon.green { background: linear-gradient(135deg, #27ae60, #2ecc71); }
        .rider-card .card-icon.red { background: linear-gradient(135deg, #e74c3c, #ff6b6b); }
        .rider-card h3 { font-size: 1.6rem; font-weight: 700; margin: 8px 0 2px; }
        .rider-card p { font-size: .82rem; color: #888; margin: 0; }

        /* ===== TABLE ===== */
        .rider-table {
            background: #fff;
            border: 1px solid var(--admin-border);
            border-radius: 18px;
            box-shadow: 0 6px 22px rgba(0,0,0,.05);
            overflow: hidden;
        }
        .rider-table .table { margin: 0; }
        .rider-table thead th {
            background: linear-gradient(180deg, #f5fbff 0%, #edf6ff 100%);
            color: var(--gasgo-blue);
            font-weight: 600;
            font-size: .85rem;
            border: none;
            position: sticky;
            top: 0;
            z-index: 2;
        }
        .rider-table tbody td {
            vertical-align: middle;
            font-size: .88rem;
            color: #475569;
            border-color: #eef2f7;
        }
        .rider-table tbody tr:hover {
            background: #f8fbff;
        }

        /* ===== STATUS BADGES ===== */
        .badge-status { padding: 5px 14px; border-radius: 20px; font-size: .75rem; font-weight: 600; }
        .badge-assigned { background: #e8f4fc; color: #1a6db0; }
        .badge-picked_up { background: #fff5e6; color: #e07d0a; }
        .badge-out_for_delivery { background: #ffd9d9; color: #cc0000; }
        .badge-delivered { background: #d4edda; color: #155724; }
        .badge-failed { background: #f8d7da; color: #721c24; }

        /* ===== BUTTONS ===== */
        .btn-action {
            padding: 6px 12px;
            border-radius: 10px;
            font-size: .78rem;
            font-weight: 600;
            box-shadow: 0 6px 14px rgba(15,23,42,.1);
        }

        /* ===== HAMBURGER (MOBILE) ===== */
        .sidebar-toggle {
            display: none; background: none; border: none; font-size: 1.5rem; color: var(--gasgo-blue); cursor: pointer;
        }
        .sidebar-overlay {
            display: none; position: fixed; inset: 0; background: rgba(0,0,0,.4); z-index: 1035;
        }

        /* ===== TOAST ===== */
        .rider-toast-host {
            position: fixed;
            right: 14px;
            bottom: 14px;
            z-index: 1100;
            display: flex;
            flex-direction: column;
            gap: 8px;
            pointer-events: none;
        }
        .rider-toast-item {
            min-width: 240px;
            max-width: 340px;
            border-radius: 12px;
            color: #fff;
            box-shadow: 0 10px 26px rgba(15,23,42,.18);
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 12px;
            pointer-events: auto;
            animation: riderToastIn .22s ease;
        }
        .rider-toast-item.success { background: var(--gasgo-blue); }
        .rider-toast-item.error { background: #dc3545; }
        .rider-toast-item button {
            border: none;
            background: transparent;
            color: #fff;
            margin-left: auto;
            font-size: .9rem;
            opacity: .86;
            cursor: pointer;
        }
        @keyframes riderToastIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 991px) {
            .rider-sidebar { transform: translateX(-100%); }
            .rider-sidebar.open { transform: translateX(0); }
            .sidebar-overlay.open { display: block; }
            .rider-topbar, .rider-main { margin-left: 0; }
            .sidebar-toggle { display: inline-block; }
        }

        /* ===== AVAILABLE ORDERS ===== */
        .available-order-card {
            border: 2px solid var(--gasgo-orange-light);
            background: linear-gradient(135deg, #ffffff 0%, #fffbf5 100%);
            transition: all 0.3s ease;
        }

        .available-order-card:hover {
            border-color: var(--gasgo-orange);
            box-shadow: 0 8px 24px rgba(247, 148, 29, 0.15);
            transform: translateY(-2px);
        }

        .accept-order-btn {
            transition: all 0.3s ease;
        }

        .accept-order-btn:hover {
            background: #229954 !important;
            transform: scale(1.02);
        }

        .accept-order-btn:disabled {
            background: #95a5a6 !important;
            cursor: not-allowed;
        }
    </style>
    @yield('rider-styles')
</head>
<body>
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <aside class="rider-sidebar" id="riderSidebar">
        <div class="sidebar-brand">
            <img data-theme-logo src="{{ asset('images/gasgo_logo-removebg-preview.png') }}" alt="GasGo">
            <h4>Gas<span>Go</span> Rider</h4>
        </div>
        <div class="sidebar-section">Menu</div>
        <ul class="sidebar-menu">
            <li><a href="{{ url('/rider/dashboard') }}" class="@yield('nav-dashboard')"><i class="fas fa-home"></i>Dashboard</a></li>
            <li><a href="{{ url('/rider/route/live-map') }}" class="@yield('nav-route-map')"><i class="fas fa-satellite-dish"></i>Live Route Map</a></li>
            <li><a href="{{ url('/rider/history') }}" class="@yield('nav-history')"><i class="fas fa-history"></i>Delivery History</a></li>
            <li><a href="{{ url('/rider/profile') }}" class="@yield('nav-profile')"><i class="fas fa-user-circle"></i>Profile</a></li>
        </ul>
        <div class="sidebar-section">Account</div>
        <ul class="sidebar-menu">
            <li>
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt"></i>Logout
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
            </li>
        </ul>
    </aside>

    <!-- Top Bar -->
    <header class="rider-topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
            <span class="page-title">@yield('page-title', 'Dashboard')</span>
        </div>
        <div class="topbar-right">
            <button class="notif-btn"><i class="fas fa-bell"></i><span class="badge-dot"></span></button>
            <div class="dropdown">
                <button class="rider-avatar" type="button" id="riderDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="cursor:pointer;">
                    {{ strtoupper(substr(Auth::user()->name ?? 'R', 0, 1)) }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="riderDropdown" style="border-radius:10px;box-shadow:0 4px 15px rgba(0,0,0,.1);">
                    <li><a class="dropdown-item" href="#"><i class="fas fa-user-circle me-2"></i>{{ Auth::user()->name ?? 'Rider' }}</a></li>
                    <li><a class="dropdown-item" href="{{ url('/rider/profile') }}"><i class="fas fa-cog me-2"></i>Edit Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form-topbar').submit();">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                        <form id="logout-form-topbar" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="rider-main">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius:12px;border-left:4px solid var(--gasgo-orange);">
                <i class="fas fa-check-circle me-2" style="color:var(--gasgo-orange);"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius:12px;border-left:4px solid #dc3545;">
                <i class="fas fa-times-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @yield('content')
    </main>

    <script src="{{ asset('bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/theme-loader.js') }}"></script>
    <script>
        const sidebar = document.getElementById('riderSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        document.getElementById('sidebarToggle').addEventListener('click', () => {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('open');
        });
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('open');
        });

        window.showAlert = function(type, message) {
            const hostId = 'riderToastHost';
            let host = document.getElementById(hostId);
            if (!host) {
                host = document.createElement('div');
                host.id = hostId;
                host.className = 'rider-toast-host';
                document.body.appendChild(host);
            }

            const item = document.createElement('div');
            item.className = `rider-toast-item ${type}`;
            item.innerHTML = `
                <i class="fas ${type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'}"></i>
                <span>${message}</span>
                <button type="button" aria-label="Dismiss"><i class="fas fa-times"></i></button>
            `;
            host.appendChild(item);

            const closeBtn = item.querySelector('button');
            closeBtn.addEventListener('click', () => item.remove());
            setTimeout(() => item.remove(), 4000);
        };

        // Handle active nav link
        document.querySelectorAll('.sidebar-menu a').forEach(link => {
            if (link.href === window.location.href) {
                // Remove active class from all
                document.querySelectorAll('.sidebar-menu a').forEach(l => l.classList.remove('active'));
                // Add to current
                link.classList.add('active');
            }
        });
    </script>
    @yield('scripts')
</body>
</html>
