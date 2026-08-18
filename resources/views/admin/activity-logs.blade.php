@extends('layouts.admin')

@section('title', 'System Activity Logs - GasGo Admin')
@section('page-title', 'System Activity Logs')
@section('nav-activity-logs', 'active')

@section('admin-styles')
<style>
    .stat-card-custom {
        background: #fff;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
        border: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 16px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .stat-card-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.07);
    }
    .stat-icon-wrapper {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
    }
    .stat-icon-blue { background: #e0f2fe; color: #0284c7; }
    .stat-icon-green { background: #dcfce7; color: #16a34a; }
    .stat-icon-orange { background: #ffedd5; color: #ea580c; }
    .stat-icon-purple { background: #f3e8ff; color: #9333ea; }

    .stat-info h3 {
        font-size: 1.6rem;
        font-weight: 800;
        margin: 0;
        color: #1e293b;
    }
    .stat-info span {
        font-size: 0.82rem;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .filter-card {
        background: #fff;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
        border: 1px solid #f1f5f9;
        margin-bottom: 24px;
    }

    .logs-table-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
        border: 1px solid #f1f5f9;
        overflow: hidden;
    }

    .actor-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: linear-gradient(135deg, #1a6db0, #2196f3);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.85rem;
        flex-shrink: 0;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-0">

    {{-- Stats Row --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="stat-card-custom">
                <div class="stat-icon-wrapper stat-icon-blue">
                    <i class="fas fa-history"></i>
                </div>
                <div class="stat-info">
                    <h3>{{ number_format($stats['totalCount'] ?? 0) }}</h3>
                    <span>Total Logs</span>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="stat-card-custom">
                <div class="stat-icon-wrapper stat-icon-green">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="stat-info">
                    <h3>{{ number_format($stats['todayCount'] ?? 0) }}</h3>
                    <span>Today's Activity</span>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="stat-card-custom">
                <div class="stat-icon-wrapper stat-icon-orange">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-info">
                    <h3>{{ number_format($stats['ordersCount'] ?? 0) }}</h3>
                    <span>Order Events</span>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="stat-card-custom">
                <div class="stat-icon-wrapper stat-icon-purple">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="stat-info">
                    <h3>{{ number_format($stats['authCount'] ?? 0) }}</h3>
                    <span>Auth & Security</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('admin.activity-logs') }}" id="logFilterForm">
            <div class="row g-3 align-items-end">
                
                {{-- Search --}}
                <div class="col-lg-4 col-md-6">
                    <label class="form-label small fw-bold text-muted mb-1">Search Keywords</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search user, order #, product, action..." value="{{ request('search') }}">
                    </div>
                </div>

                {{-- Module Filter --}}
                <div class="col-lg-2 col-sm-6">
                    <label class="form-label small fw-bold text-muted mb-1">Module</label>
                    <select name="module" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="all" {{ request('module') == 'all' || !request('module') ? 'selected' : '' }}>All Modules</option>
                        <option value="orders" {{ request('module') == 'orders' ? 'selected' : '' }}>🛒 Orders</option>
                        <option value="products" {{ request('module') == 'products' ? 'selected' : '' }}>📦 Products</option>
                        <option value="deliveries" {{ request('module') == 'deliveries' ? 'selected' : '' }}>🛵 Deliveries</option>
                        <option value="auth" {{ request('module') == 'auth' ? 'selected' : '' }}>🔐 Auth & Security</option>
                        <option value="loyalty" {{ request('module') == 'loyalty' ? 'selected' : '' }}>🎁 Loyalty</option>
                        <option value="settings" {{ request('module') == 'settings' ? 'selected' : '' }}>⚙️ Settings</option>
                    </select>
                </div>

                {{-- Role Filter --}}
                <div class="col-lg-2 col-sm-6">
                    <label class="form-label small fw-bold text-muted mb-1">User Role</label>
                    <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="all" {{ request('role') == 'all' || !request('role') ? 'selected' : '' }}>All Roles</option>
                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="rider" {{ request('role') == 'rider' ? 'selected' : '' }}>Rider</option>
                        <option value="customer" {{ request('role') == 'customer' ? 'selected' : '' }}>Customer</option>
                        <option value="system" {{ request('role') == 'system' ? 'selected' : '' }}>System</option>
                    </select>
                </div>

                {{-- Date Range --}}
                <div class="col-lg-2 col-sm-6">
                    <label class="form-label small fw-bold text-muted mb-1">Timeframe</label>
                    <select name="date_range" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="" {{ !request('date_range') ? 'selected' : '' }}>All Time</option>
                        <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="week" {{ request('date_range') == 'week' ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="month" {{ request('date_range') == 'month' ? 'selected' : '' }}>Last 30 Days</option>
                    </select>
                </div>

                {{-- Action Buttons --}}
                <div class="col-lg-2 col-sm-6 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                        <i class="fas fa-filter me-1"></i>Filter
                    </button>
                    @if(request()->hasAny(['search', 'module', 'role', 'date_range']))
                        <a href="{{ route('admin.activity-logs') }}" class="btn btn-light btn-sm border" title="Reset filters">
                            <i class="fas fa-undo"></i>
                        </a>
                    @endif
                </div>

            </div>
        </form>
    </div>

    {{-- Main Activity Logs Table Card --}}
    <div class="logs-table-card">
        <div class="p-3 border-bottom bg-light d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2">
                <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-list-alt me-2 text-primary"></i>Audit Trail Log Entries</h6>
                <span class="badge bg-secondary rounded-pill">{{ $logs->total() }} total</span>
            </div>
            
            <form action="{{ route('admin.activity-logs.clear') }}" method="POST" onsubmit="return confirm('WARNING: Are you sure you want to delete ALL activity logs? This action is permanent and cannot be undone.');">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-trash-alt me-1"></i>Clear All Logs
                </button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr class="small text-uppercase text-muted" style="font-size: 0.75rem;">
                        <th style="width: 170px;">Date & Time</th>
                        <th style="width: 200px;">Actor / User</th>
                        <th style="width: 130px;">Module</th>
                        <th style="width: 130px;">Action</th>
                        <th>Activity Description</th>
                        <th style="width: 140px;">IP Address</th>
                    </tr>
                </thead>
                <tbody style="font-size: 0.88rem;">
                    @forelse($logs as $log)
                        <tr>
                            {{-- Timestamp --}}
                            <td class="text-nowrap">
                                <div class="fw-bold text-dark">{{ $log->created_at->format('M d, Y') }}</div>
                                <small class="text-muted">{{ $log->created_at->format('h:i:s A') }}</small>
                                <span class="badge bg-light text-muted border d-block mt-1" style="font-size: 0.68rem;">
                                    {{ $log->created_at->diffForHumans() }}
                                </span>
                            </td>

                            {{-- Actor / User --}}
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="actor-avatar">
                                        {{ strtoupper(substr($log->user_name ?? 'S', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark text-truncate" style="max-width: 130px;" title="{{ $log->user_name ?? 'System' }}">
                                            {{ $log->user_name ?? 'System' }}
                                        </div>
                                        @php
                                            $roleBadgeClass = match(strtolower($log->user_role ?? '')) {
                                                'admin' => 'bg-danger',
                                                'rider' => 'bg-warning text-dark',
                                                'customer' => 'bg-primary',
                                                default => 'bg-secondary',
                                            };
                                        @endphp
                                        <span class="badge {{ $roleBadgeClass }}" style="font-size: 0.68rem;">
                                            {{ ucfirst($log->user_role ?? 'System') }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            {{-- Module --}}
                            <td>
                                @php
                                    $moduleIcon = match(strtolower($log->module)) {
                                        'orders' => 'fa-shopping-cart text-primary',
                                        'products' => 'fa-box text-success',
                                        'deliveries' => 'fa-motorcycle text-warning',
                                        'auth' => 'fa-user-lock text-info',
                                        'loyalty' => 'fa-gift text-danger',
                                        'settings' => 'fa-cog text-secondary',
                                        default => 'fa-circle text-muted',
                                    };
                                @endphp
                                <span class="badge bg-light text-dark border">
                                    <i class="fas {{ $moduleIcon }} me-1"></i>{{ ucfirst($log->module) }}
                                </span>
                            </td>

                            {{-- Action --}}
                            <td>
                                @php
                                    $actionBadgeClass = match(strtolower($log->action)) {
                                        'created', 'register' => 'bg-success',
                                        'updated', 'status_change', 'out_for_delivery' => 'bg-info text-white',
                                        'deleted', 'failed' => 'bg-danger',
                                        'login' => 'bg-primary',
                                        'logout' => 'bg-secondary',
                                        'password_reset' => 'bg-warning text-dark',
                                        'redeemed' => 'bg-success',
                                        'assigned' => 'bg-dark',
                                        default => 'bg-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $actionBadgeClass }}" style="font-size: 0.72rem;">
                                    {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                                </span>
                            </td>

                            {{-- Description --}}
                            <td>
                                <span class="text-dark fw-medium">{{ $log->description }}</span>
                            </td>

                            {{-- IP Address --}}
                            <td>
                                <code>{{ $log->ip_address ?? '—' }}</code>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-clipboard-list fa-3x mb-3 text-secondary" style="opacity: 0.4;"></i>
                                <h6 class="fw-bold mb-1">No Activity Logs Found</h6>
                                <p class="small mb-0">No logs match your current search or filter criteria.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($logs->hasPages())
            <div class="p-3 border-top d-flex justify-content-center">
                {{ $logs->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

</div>
@endsection
