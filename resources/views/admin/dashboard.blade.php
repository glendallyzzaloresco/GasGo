@extends('layouts.admin')

@section('title', 'GasGo Admin - Dashboard')
@section('nav-dashboard', 'active')
@section('page-title', 'Dashboard')

@section('content')
<!-- Stats Row -->
<div class="row g-4 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p>Total Orders</p>
                    <h3>{{ $totalOrders }}</h3>
                </div>
                <div class="stat-icon blue"><i class="fas fa-shopping-bag"></i></div>
            </div>
            <small class="text-muted">All time orders</small>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p>Revenue</p>
                    <h3>₱{{ number_format($revenue, 2) }}</h3>
                </div>
                <div class="stat-icon orange"><i class="fas fa-peso-sign"></i></div>
            </div>
            <small class="text-muted">Total revenue</small>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p>Active Riders</p>
                    <h3>{{ $activeRiders }}</h3>
                </div>
                <div class="stat-icon green"><i class="fas fa-motorcycle"></i></div>
            </div>
            <small class="text-muted">{{ $riders->where('availability', 'available')->count() }} available now</small>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p>Pending Orders</p>
                    <h3>{{ $pendingOrders }}</h3>
                </div>
                <div class="stat-icon red"><i class="fas fa-clock"></i></div>
            </div>
            @if($pendingOrders > 0)
                <small class="text-danger"><i class="fas fa-exclamation-circle me-1"></i>Needs attention</small>
            @else
                <small class="text-success"><i class="fas fa-check-circle me-1"></i>All clear</small>
            @endif
        </div>
    </div>
</div>

<!-- Customers Count + Low Stock Alerts + Recent Orders -->
<div class="row g-4 mb-4">
    <!-- Low Stock Alerts -->
    <div class="col-lg-4">
        <div class="stat-card">
            <h6 class="fw-bold mb-3" style="color:var(--gasgo-blue);"><i class="fas fa-exclamation-triangle me-2" style="color:var(--gasgo-orange);"></i>Inventory Status</h6>
            @forelse($products as $product)
                @php
                    $bgColor = $product->stock <= 5 ? '#fff5e6' : '#f8f9fa';
                @endphp
                <div class="d-flex justify-content-between align-items-center p-2 mb-2" style="background:{{ $bgColor }};border-radius:10px;">
                    <div class="d-flex align-items-center gap-2">
                        @if($product->image)
                            <img src="{{ asset('images/' . $product->image) }}" alt="{{ $product->name }}" style="width:36px;height:36px;border-radius:8px;object-fit:cover;">
                        @else
                            <div style="width:36px;height:36px;border-radius:8px;background:#e0e0e0;display:flex;align-items:center;justify-content:center;"><i class="fas fa-image" style="font-size:.8rem;color:#999;"></i></div>
                        @endif
                        <div>
                            <div class="fw-bold" style="font-size:.85rem;">{{ $product->name }}</div>
                            <div style="font-size:.75rem;color:#888;">Stock: <strong class="{{ $product->stock <= 5 ? 'text-danger' : '' }}">{{ $product->stock }}</strong></div>
                        </div>
                    </div>
                    @if($product->stock <= 5)
                        <span class="badge bg-warning text-dark" style="font-size:.7rem;">Low</span>
                    @else
                        <span class="badge bg-success" style="font-size:.7rem;">OK</span>
                    @endif
                </div>
            @empty
                <p class="text-muted mb-0">No products found.</p>
            @endforelse
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="col-lg-8">
        <div class="gasgo-table">
            <div class="d-flex justify-content-between align-items-center px-3 pt-3 pb-2">
                <h6 class="fw-bold mb-0" style="color:var(--gasgo-blue);"><i class="fas fa-clock me-2" style="color:var(--gasgo-orange);"></i>Recent Orders</h6>
                <a href="{{ url('/admin/orders') }}" class="btn btn-sm" style="color:var(--gasgo-orange);font-weight:600;">View All <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                        <tr>
                            <td class="fw-bold">#{{ $order->order_number }}</td>
                            <td>{{ $order->user->name ?? 'N/A' }}</td>
                            <td class="fw-bold">₱{{ number_format($order->total_amount, 2) }}</td>
                            <td><span class="badge bg-primary" style="font-size:.7rem;">{{ ucfirst($order->payment_method ?? 'N/A') }}</span></td>
                            <td><span class="badge-status badge-{{ $order->status }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No orders yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Rider Status -->
<div class="row g-4">
    <div class="col-12">
        <div class="gasgo-table">
            <div class="d-flex justify-content-between align-items-center px-3 pt-3 pb-2">
                <h6 class="fw-bold mb-0" style="color:var(--gasgo-blue);"><i class="fas fa-motorcycle me-2" style="color:var(--gasgo-orange);"></i>Rider Status Overview</h6>
                <a href="{{ url('/admin/riders') }}" class="btn btn-sm" style="color:var(--gasgo-orange);font-weight:600;">Manage Riders <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Rider</th>
                        <th>Availability</th>
                        <th>Current Delivery</th>
                        <th>Deliveries Today</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riders as $rider)
                        <tr>
                            <td class="fw-bold">{{ $rider->user->name ?? 'Unknown' }}</td>
                            <td>
                                @if($rider->availability === 'available')
                                    <span class="badge bg-success">Available</span>
                                @elseif($rider->availability === 'busy')
                                    <span class="badge bg-warning text-dark">Busy</span>
                                @else
                                    <span class="badge bg-secondary">Offline</span>
                                @endif
                            </td>
                            <td>
                                @if(isset($activeDeliveries[$rider->user_id]))
                                    #{{ $activeDeliveries[$rider->user_id]->order->order_number ?? '—' }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $todayDeliveries[$rider->user_id] ?? 0 }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No riders registered.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Customers Summary -->
<div class="row g-4 mt-1">
    <div class="col-lg-4 col-md-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p>Total Customers</p>
                    <h3>{{ $totalCustomers }}</h3>
                </div>
                <div class="stat-icon blue"><i class="fas fa-users"></i></div>
            </div>
            <small class="text-muted">Registered customers</small>
        </div>
    </div>
</div>
@endsection
