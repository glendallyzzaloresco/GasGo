<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'GasGo Admin')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --gasgo-blue: #1a6db0;
            --gasgo-blue-dark: #145a8f;
            --gasgo-blue-light: #e8f4fc;
            --gasgo-orange: #f7941d;
            --gasgo-orange-dark: #e07d0a;
            --gasgo-orange-light: #fff5e6;
            --sidebar-width: 260px;
        }
        * { font-family: 'Poppins', sans-serif; }
        body { background: #f0f2f5; }

        /* ===== SIDEBAR ===== */
        .admin-sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0; left: 0;
            background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
            color: #fff;
            z-index: 1040;
            transition: transform .3s;
            overflow-y: auto;
        }
        .sidebar-brand {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255,255,255,.1);
            display: flex; align-items: center; gap: 12px;
        }
        .sidebar-brand img { height: 42px; }
        .sidebar-brand h4 { margin: 0; font-weight: 700; font-size: 1.15rem; }
        .sidebar-brand h4 span { color: var(--gasgo-orange); }
        .sidebar-menu { list-style: none; padding: 16px 0; margin: 0; }
        .sidebar-menu li a {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 24px; color: rgba(255,255,255,.7);
            text-decoration: none; font-size: .9rem; font-weight: 500;
            border-left: 3px solid transparent;
            transition: all .25s;
        }
        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            background: rgba(255,255,255,.08);
            color: #fff;
            border-left-color: var(--gasgo-orange);
        }
        .sidebar-menu li a i { width: 20px; text-align: center; color: var(--gasgo-orange); }
        .sidebar-section { padding: 10px 24px 4px; font-size: .7rem; text-transform: uppercase; letter-spacing: 1px; color: rgba(255,255,255,.35); }

        /* ===== TOP BAR ===== */
        .admin-topbar {
            margin-left: var(--sidebar-width);
            height: 64px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 28px;
            position: sticky; top: 0; z-index: 1030;
        }
        .admin-topbar .page-title { font-weight: 700; font-size: 1.1rem; color: var(--gasgo-blue); }
        .admin-topbar .topbar-right { display: flex; align-items: center; gap: 18px; }
        .admin-topbar .topbar-right .notif-btn {
            background: none; border: none; font-size: 1.2rem; color: #666; position: relative; cursor: pointer;
        }
        .admin-topbar .topbar-right .notif-btn .badge-dot {
            position: absolute; top: 0; right: 0; width: 8px; height: 8px;
            background: var(--gasgo-orange); border-radius: 50%;
        }
        .admin-avatar {
            width: 36px; height: 36px; border-radius: 50%; background: var(--gasgo-blue);
            color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: .85rem;
            border: none; cursor: pointer; transition: transform .3s;
        }
        .admin-avatar:hover { transform: scale(1.1); }
        .dropdown-menu .dropdown-item {
            font-size: .9rem; padding: 10px 16px; border-radius: 0;
        }
        .dropdown-menu .dropdown-item:hover, .dropdown-menu .dropdown-item:focus {
            background: var(--gasgo-blue-light); color: var(--gasgo-blue);
        }
        .dropdown-menu .dropdown-item i { width: 18px; }
        .dropdown-divider { margin: 6px 0; border-color: #e0e0e0; }

        /* ===== MAIN ===== */
        .admin-main {
            margin-left: var(--sidebar-width);
            padding: 28px;
            min-height: calc(100vh - 64px);
        }

        /* ===== STAT CARDS ===== */
        .stat-card {
            background: #fff; border-radius: 16px; padding: 22px;
            box-shadow: 0 4px 15px rgba(0,0,0,.06); transition: transform .3s;
        }
        .stat-card:hover { transform: translateY(-4px); }
        .stat-card .stat-icon {
            width: 48px; height: 48px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem; color: #fff;
        }
        .stat-card .stat-icon.blue { background: linear-gradient(135deg, #1a6db0, #2196f3); }
        .stat-card .stat-icon.orange { background: linear-gradient(135deg, #f7941d, #ff6b35); }
        .stat-card .stat-icon.green { background: linear-gradient(135deg, #27ae60, #2ecc71); }
        .stat-card .stat-icon.red { background: linear-gradient(135deg, #e74c3c, #ff6b6b); }
        .stat-card h3 { font-size: 1.6rem; font-weight: 700; margin: 8px 0 2px; }
        .stat-card p { font-size: .82rem; color: #888; margin: 0; }

        /* ===== TABLE ===== */
        .gasgo-table { background: #fff; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,.06); overflow: hidden; }
        .gasgo-table .table { margin: 0; }
        .gasgo-table thead th { background: var(--gasgo-blue-light); color: var(--gasgo-blue); font-weight: 600; font-size: .85rem; border: none; }
        .gasgo-table tbody td { vertical-align: middle; font-size: .88rem; color: #555; }
        .badge-status { padding: 5px 14px; border-radius: 20px; font-size: .75rem; font-weight: 600; }
        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-approved { background: #d1ecf1; color: #0c5460; }
        .badge-assigned { background: #e8f4fc; color: #1a6db0; }
        .badge-delivered { background: #d4edda; color: #155724; }
        .badge-cancelled { background: #f8d7da; color: #721c24; }
        .badge-out_for_delivery { background: #fff5e6; color: #e07d0a; }

        .btn-action { padding: 6px 12px; border-radius: 8px; font-size: .78rem; font-weight: 600; }

        /* ===== HAMBURGER (MOBILE) ===== */
        .sidebar-toggle {
            display: none; background: none; border: none; font-size: 1.5rem; color: var(--gasgo-blue); cursor: pointer;
        }
        .sidebar-overlay {
            display: none; position: fixed; inset: 0; background: rgba(0,0,0,.4); z-index: 1035;
        }

        @media (max-width: 991px) {
            .admin-sidebar { transform: translateX(-100%); }
            .admin-sidebar.open { transform: translateX(0); }
            .sidebar-overlay.open { display: block; }
            .admin-topbar, .admin-main { margin-left: 0; }
            .sidebar-toggle { display: inline-block; }
        }
    </style>
    @yield('admin-styles')
</head>
<body>
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-brand">
            <img src="{{ asset('images/gasgo_logo-removebg-preview.png') }}" alt="GasGo">
            <h4>Gas<span>Go</span> Admin</h4>
        </div>
        <div class="sidebar-section">Main</div>
        <ul class="sidebar-menu">
            <li><a href="{{ url('/admin/dashboard') }}" class="@yield('nav-dashboard')"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
            <li><a href="{{ url('/admin/orders') }}" class="@yield('nav-orders')"><i class="fas fa-shopping-bag"></i>Orders</a></li>
            <li><a href="{{ url('/admin/products') }}" class="@yield('nav-products')"><i class="fas fa-fire"></i>Products</a></li>
            <li><a href="{{ url('/admin/categories') }}" class="@yield('nav-categories')"><i class="fas fa-th-large"></i>Categories</a></li>
        </ul>
        <div class="sidebar-section">Delivery</div>
        <ul class="sidebar-menu">
            <li><a href="{{ url('/admin/riders') }}" class="@yield('nav-riders')"><i class="fas fa-motorcycle"></i>Riders</a></li>
            <li><a href="{{ url('/admin/deliveries') }}" class="@yield('nav-deliveries')"><i class="fas fa-truck"></i>Deliveries</a></li>
        </ul>
        <div class="sidebar-section">Marketing</div>
        <ul class="sidebar-menu">
            <li><a href="{{ url('/admin/promotions') }}" class="@yield('nav-promotions')"><i class="fas fa-tags"></i>Promotions</a></li>
            <li><a href="{{ url('/admin/rewards') }}" class="@yield('nav-rewards')"><i class="fas fa-gift"></i>Rewards</a></li>
        </ul>
        <div class="sidebar-section">Reports</div>
        <ul class="sidebar-menu">
            <li><a href="{{ url('/admin/reports') }}" class="@yield('nav-reports')"><i class="fas fa-chart-bar"></i>Sales Reports</a></li>
            <li><a href="{{ url('/admin/customers') }}" class="@yield('nav-customers')"><i class="fas fa-users"></i>Customers</a></li>
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
    <header class="admin-topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
            <span class="page-title">@yield('page-title', 'Dashboard')</span>
        </div>
        <div class="topbar-right">
            <button class="notif-btn"><i class="fas fa-bell"></i><span class="badge-dot"></span></button>
            <div class="dropdown">
                <button class="admin-avatar" type="button" id="adminDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="cursor:pointer;">
                    {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminDropdown" style="border-radius:10px;box-shadow:0 4px 15px rgba(0,0,0,.1);">
                    <li><a class="dropdown-item" href="#"><i class="fas fa-user-circle me-2"></i>{{ Auth::user()->name ?? 'Admin' }}</a></li>
                    <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Settings</a></li>
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
    <main class="admin-main">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius:12px;">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        document.getElementById('sidebarToggle').addEventListener('click', () => {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('open');
        });
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('open');
        });
    </script>
    @yield('scripts')
</body>
</html>
