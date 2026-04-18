<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'GasGo Admin')</title>
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
            --sidebar-width: 260px;
            --admin-bg: #f4f7fb;
            --admin-border: #e8eef5;
        }
        * { font-family: 'Poppins', sans-serif; }
        body {
            background: radial-gradient(circle at top right, #ffffff 0%, var(--admin-bg) 48%, #edf2f8 100%);
        }

        /* ===== SIDEBAR ===== */
        .admin-sidebar {
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
        .admin-sidebar::before,
        .admin-sidebar::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
        }
        .admin-sidebar::before {
            width: 170px;
            height: 170px;
            top: -40px;
            right: -55px;
            background: radial-gradient(circle at center, rgba(247,148,29,.25), rgba(247,148,29,0));
        }
        .admin-sidebar::after {
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

        /* ===== SUBMENU ===== */
        .sidebar-submenu {
            list-style: none;
            padding: 0;
            margin: 0;
            overflow: hidden;
            max-height: 0;
            transition: max-height .3s ease;
        }
        .sidebar-submenu.open { max-height: 200px; }
        .sidebar-submenu li a {
            padding: 9px 24px 9px 52px;
            font-size: .85rem;
            border-left: 3px solid transparent;
        }
        .sidebar-submenu li a::before {
            content: '•';
            margin-right: 8px;
            color: var(--gasgo-orange);
            font-size: .7rem;
        }
        .sidebar-submenu li a i { display: none; }
        .submenu-toggle .submenu-arrow {
            margin-left: auto;
            font-size: .7rem;
            transition: transform .3s;
            color: rgba(255,255,255,.5);
        }
        .submenu-toggle.open .submenu-arrow { transform: rotate(90deg); }

        /* ===== TOP BAR ===== */
        .admin-topbar {
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
        .admin-topbar .page-title { font-weight: 700; font-size: 1.1rem; color: var(--gasgo-blue); }
        .admin-topbar .topbar-right { display: flex; align-items: center; gap: 18px; }
        .admin-topbar .topbar-right .notif-wrap {
            position: relative;
        }
        .admin-topbar .topbar-right .notif-btn {
            background: none; border: none; font-size: 1.2rem; color: #666; position: relative; cursor: pointer;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            transition: background .2s ease, color .2s ease;
        }
        .admin-topbar .topbar-right .notif-btn:hover,
        .admin-topbar .topbar-right .notif-btn[aria-expanded='true'] {
            background: #eef5ff;
            color: var(--gasgo-blue);
        }
        .admin-topbar .topbar-right .notif-btn .badge-dot {
            position: absolute; top: 0; right: 0; width: 8px; height: 8px;
            background: var(--gasgo-orange); border-radius: 50%;
        }
        .notif-panel {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            width: min(360px, 88vw);
            background: #fff;
            border: 1px solid var(--admin-border);
            border-radius: 14px;
            box-shadow: 0 20px 36px rgba(15,23,42,.14);
            overflow: hidden;
            z-index: 1050;
            opacity: 0;
            transform: translateY(6px);
            pointer-events: none;
            transition: opacity .2s ease, transform .2s ease;
        }
        .notif-panel.show {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }
        .notif-panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 12px;
            border-bottom: 1px solid #eef2f7;
            background: #f8fbff;
        }
        .notif-panel-header h6 {
            margin: 0;
            font-weight: 700;
            font-size: .88rem;
            color: var(--gasgo-blue);
        }
        .notif-panel-header button {
            border: none;
            background: transparent;
            color: #64748b;
            font-size: .8rem;
            font-weight: 600;
            padding: 4px 6px;
            border-radius: 8px;
        }
        .notif-panel-header button:hover {
            background: #eaf2ff;
            color: var(--gasgo-blue);
        }
        .notif-list {
            max-height: 340px;
            overflow-y: auto;
        }
        .notif-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 11px 12px;
            text-decoration: none;
            color: inherit;
            border-bottom: 1px solid #f1f5f9;
        }
        .notif-item:last-child {
            border-bottom: none;
        }
        .notif-item:hover {
            background: #f8fbff;
        }
        .notif-icon {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: .82rem;
        }
        .notif-warning .notif-icon { background: #fff4db; color: #b7791f; }
        .notif-info .notif-icon { background: #e8f4fc; color: #1a6db0; }
        .notif-success .notif-icon { background: #e7f8ef; color: #1f8a52; }
        .notif-danger .notif-icon { background: #fdecec; color: #b4232f; }
        .notif-content .title {
            font-size: .84rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 2px;
        }
        .notif-content .msg {
            font-size: .79rem;
            color: #64748b;
            line-height: 1.35;
        }
        .notif-content .meta {
            font-size: .74rem;
            color: #94a3b8;
            margin-top: 5px;
        }
        .notif-empty,
        .notif-loading {
            padding: 18px 12px;
            text-align: center;
            color: #64748b;
            font-size: .82rem;
        }
        .notif-dot-hidden {
            display: none;
        }
        .admin-avatar {
            width: 36px; height: 36px; border-radius: 50%; background: var(--gasgo-blue);
            color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: .85rem;
            border: none; cursor: pointer; transition: transform .3s;
            box-shadow: 0 8px 18px rgba(26,109,176,.3);
        }
        .admin-avatar:hover { transform: scale(1.08) translateY(-1px); }
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

        .reveal-ready {
            opacity: 0;
            transform: translateY(14px);
            transition: opacity .45s ease, transform .45s ease;
        }
        .reveal-in {
            opacity: 1;
            transform: translateY(0);
        }
        .btn-loading {
            position: relative;
            pointer-events: none;
            opacity: .8;
        }
        .btn-loading .btn-label {
            visibility: hidden;
        }
        .btn-loading .btn-spinner {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: .78rem;
            color: inherit;
        }
        .admin-toast-host {
            position: fixed;
            right: 14px;
            bottom: 14px;
            z-index: 1100;
            display: flex;
            flex-direction: column;
            gap: 8px;
            pointer-events: none;
        }
        .admin-toast-item {
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
            animation: adminToastIn .22s ease;
        }
        .admin-toast-item.success { background: var(--gasgo-blue); }
        .admin-toast-item.error { background: #dc3545; }
        .admin-toast-item button {
            border: none;
            background: transparent;
            color: #fff;
            margin-left: auto;
            font-size: .9rem;
            opacity: .86;
            cursor: pointer;
        }
        @keyframes adminToastIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ===== STAT CARDS ===== */
        .stat-card {
            background: linear-gradient(180deg, #ffffff 0%, #f9fcff 100%);
            border: 1px solid var(--admin-border);
            border-radius: 18px;
            padding: 22px;
            box-shadow: 0 6px 22px rgba(0,0,0,.05);
            transition: transform .3s, box-shadow .3s;
            position: relative;
            overflow: hidden;
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, var(--gasgo-blue), var(--gasgo-orange));
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 16px 34px rgba(15,23,42,.1);
        }
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
        .gasgo-table {
            background: #fff;
            border: 1px solid var(--admin-border);
            border-radius: 18px;
            box-shadow: 0 6px 22px rgba(0,0,0,.05);
            overflow: hidden;
        }
        .gasgo-table .table { margin: 0; }
        .gasgo-table thead th {
            background: linear-gradient(180deg, #f5fbff 0%, #edf6ff 100%);
            color: var(--gasgo-blue);
            font-weight: 600;
            font-size: .85rem;
            border: none;
            position: sticky;
            top: 0;
            z-index: 2;
        }
        .gasgo-table tbody td {
            vertical-align: middle;
            font-size: .88rem;
            color: #475569;
            border-color: #eef2f7;
        }
        .gasgo-table tbody tr:hover {
            background: #f8fbff;
        }
        .badge-status { padding: 5px 14px; border-radius: 20px; font-size: .75rem; font-weight: 600; }
        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-approved { background: #d1ecf1; color: #0c5460; }
        .badge-assigned { background: #e8f4fc; color: #1a6db0; }
        .badge-delivered { background: #d4edda; color: #155724; }
        .badge-cancelled { background: #f8d7da; color: #721c24; }
        .badge-out_for_delivery { background: #fff5e6; color: #e07d0a; }

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
            <li><a href="{{ route('admin.inventory.index') }}" class="@yield('nav-inventory')"><i class="fas fa-warehouse"></i>Inventory</a></li>
        </ul>
        <div class="sidebar-section">Delivery</div>
        <ul class="sidebar-menu">
            <li><a href="{{ url('/admin/riders') }}" class="@yield('nav-riders')"><i class="fas fa-motorcycle"></i>Riders</a></li>
            <li><a href="{{ url('/admin/deliveries') }}" class="@yield('nav-deliveries')"><i class="fas fa-truck"></i>Deliveries</a></li>
        </ul>
        <div class="sidebar-section">Marketing</div>
        <ul class="sidebar-menu">
            <li><a href="{{ url('/admin/rewards') }}" class="@yield('nav-rewards')"><i class="fas fa-gift"></i>Rewards</a></li>
        </ul>
        <div class="sidebar-section">Reports</div>
        <ul class="sidebar-menu">
            <li><a href="{{ url('/admin/reports') }}" class="@yield('nav-reports')"><i class="fas fa-chart-bar"></i>Sales Reports</a></li>
            <li><a href="{{ url('/admin/users') }}" class="@yield('nav-users')"><i class="fas fa-users-cog"></i>User Management</a></li>
        </ul>
        <div class="sidebar-section">Maintenance</div>
        <ul class="sidebar-menu">
            <li><a href="{{ url('/admin/settings') }}" class="@yield('nav-settings')"><i class="fas fa-cog"></i>Settings</a></li>
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
            <div class="notif-wrap" id="adminNotifWrap" data-endpoint="{{ route('admin.notifications') }}">
                <button class="notif-btn" id="adminNotifBtn" aria-expanded="false" aria-label="Notifications">
                    <i class="fas fa-bell"></i>
                    <span class="badge-dot notif-dot-hidden" id="adminNotifDot"></span>
                </button>
                <div class="notif-panel" id="adminNotifPanel" role="dialog" aria-label="Admin Notifications">
                    <div class="notif-panel-header">
                        <h6>Notifications</h6>
                        <button type="button" id="adminNotifRefresh"><i class="fas fa-rotate me-1"></i>Refresh</button>
                    </div>
                    <div class="notif-list" id="adminNotifList">
                        <div class="notif-loading"><i class="fas fa-circle-notch fa-spin me-2"></i>Loading notifications...</div>
                    </div>
                </div>
            </div>
            <div class="dropdown">
                <button class="admin-avatar" type="button" id="adminDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="cursor:pointer;">
                    {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminDropdown" style="border-radius:10px;box-shadow:0 4px 15px rgba(0,0,0,.1);">
                    <li><a class="dropdown-item" href="{{ route('admin.profile') }}"><i class="fas fa-user-circle me-2"></i>{{ Auth::user()->name ?? 'Admin' }}</a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.settings') }}"><i class="fas fa-cog me-2"></i>Settings</a></li>
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

    <script src="{{ asset('bootstrap/js/bootstrap.bundle.min.js') }}"></script>
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

        // Products submenu toggle
        const productsToggle = document.getElementById('productsToggle');
        const productsSubmenu = document.getElementById('productsSubmenu');
        if (productsToggle && productsSubmenu) {
            productsToggle.addEventListener('click', function (e) {
                e.preventDefault();
                this.classList.toggle('open');
                productsSubmenu.classList.toggle('open');
            });
        }

        window.showAdminToast = function(message, isError = false) {
            const hostId = 'adminToastHost';
            let host = document.getElementById(hostId);
            if (!host) {
                host = document.createElement('div');
                host.id = hostId;
                host.className = 'admin-toast-host';
                document.body.appendChild(host);
            }

            const item = document.createElement('div');
            item.className = `admin-toast-item ${isError ? 'error' : 'success'}`;
            item.innerHTML = `
                <i class="fas ${isError ? 'fa-exclamation-circle' : 'fa-check-circle'}"></i>
                <span>${message}</span>
                <button type="button" aria-label="Dismiss"><i class="fas fa-times"></i></button>
            `;

            const dismiss = () => item.remove();
            item.querySelector('button')?.addEventListener('click', dismiss);
            host.appendChild(item);
            setTimeout(dismiss, 2600);
        };

        const notifWrap = document.getElementById('adminNotifWrap');
        const notifBtn = document.getElementById('adminNotifBtn');
        const notifPanel = document.getElementById('adminNotifPanel');
        const notifList = document.getElementById('adminNotifList');
        const notifDot = document.getElementById('adminNotifDot');
        const notifRefresh = document.getElementById('adminNotifRefresh');

        function setNotifOpen(isOpen) {
            if (!notifBtn || !notifPanel) return;
            notifBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            notifPanel.classList.toggle('show', isOpen);
        }

        function renderNotifItems(items) {
            if (!notifList) return;
            if (!items || !items.length) {
                notifList.innerHTML = '<div class="notif-empty"><i class="fas fa-check-circle me-2 text-success"></i>No new notifications right now.</div>';
                return;
            }

            notifList.innerHTML = items.map((item) => {
                const level = item.level || 'info';
                const icon = item.icon || 'fa-bell';
                const title = item.title || 'Notification';
                const message = item.message || '';
                const timeHuman = item.time_human || '';
                const url = item.url || '#';

                return `<a href="${url}" class="notif-item notif-${level}">
                            <div class="notif-icon"><i class="fas ${icon}"></i></div>
                            <div class="notif-content">
                                <div class="title">${title}</div>
                                <div class="msg">${message}</div>
                                <div class="meta">${timeHuman}</div>
                            </div>
                        </a>`;
            }).join('');
        }

        async function loadAdminNotifications(showToastOnError = false) {
            if (!notifWrap || !notifList) return;
            const endpoint = notifWrap.dataset.endpoint;
            if (!endpoint) return;

            if (!notifPanel?.classList.contains('show')) {
                notifList.innerHTML = '<div class="notif-loading"><i class="fas fa-circle-notch fa-spin me-2"></i>Loading notifications...</div>';
            }

            try {
                const response = await fetch(endpoint, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) {
                    throw new Error('Failed to fetch notifications');
                }

                const payload = await response.json();
                const count = Number(payload.count || 0);

                renderNotifItems(payload.items || []);
                if (notifDot) {
                    notifDot.classList.toggle('notif-dot-hidden', count <= 0);
                }
            } catch (error) {
                notifList.innerHTML = '<div class="notif-empty"><i class="fas fa-triangle-exclamation me-2 text-danger"></i>Unable to load notifications.</div>';
                if (showToastOnError && typeof window.showAdminToast === 'function') {
                    window.showAdminToast('Could not load notifications right now.', true);
                }
            }
        }

        if (notifBtn && notifPanel) {
            notifBtn.addEventListener('click', function () {
                const isOpen = notifPanel.classList.contains('show');
                setNotifOpen(!isOpen);
                if (!isOpen) {
                    loadAdminNotifications();
                }
            });

            document.addEventListener('click', function (event) {
                if (!notifWrap?.contains(event.target)) {
                    setNotifOpen(false);
                }
            });
        }

        notifRefresh?.addEventListener('click', function () {
            loadAdminNotifications(true);
        });

        loadAdminNotifications();
        setInterval(() => loadAdminNotifications(), 60000);

        // Lightweight reveal animation for key admin blocks.
        const revealTargets = document.querySelectorAll('.admin-main .stat-card, .admin-main .report-card, .admin-main .gasgo-table, .admin-main .card, .admin-main .analytics-card');
        if (revealTargets.length) {
            if ('IntersectionObserver' in window) {
                const revealObserver = new IntersectionObserver((entries, obs) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('reveal-in');
                            obs.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.12, rootMargin: '0px 0px -6% 0px' });

                revealTargets.forEach((el, index) => {
                    el.classList.add('reveal-ready');
                    el.style.transitionDelay = `${Math.min(index * 40, 320)}ms`;
                    revealObserver.observe(el);
                });
            } else {
                revealTargets.forEach((el) => el.classList.add('reveal-in'));
            }
        }
    </script>
    @yield('scripts')
</body>
</html>
