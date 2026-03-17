<!-- stylelint-disable -->
@extends('layouts.admin')

@section('title', 'GasGo Admin - Dashboard')
@section('nav-dashboard', 'active')
@section('page-title', 'Dashboard')

@section('admin-styles')
<style>
    .stat-card {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0,0,0,.15);
        border-color: var(--gasgo-blue);
    }
    .modal-detail {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }
    .modal-detail.show {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .modal-detail-content {
        background-color: white;
        padding: 0;
        border-radius: 16px;
        width: 90%;
        max-width: 800px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }
    .modal-header-custom {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 24px;
        border-bottom: 1px solid #f0f0f0;
        background: linear-gradient(135deg, var(--gasgo-blue) 0%, #2196f3 100%);
        color: white;
        border-radius: 16px 16px 0 0;
    }
    .modal-header-custom h4 {
        margin: 0;
        font-weight: 700;
    }
    .modal-body-custom {
        padding: 24px;
    }
    .close-modal {
        cursor: pointer;
        font-size: 26px;
        font-weight: bold;
        color: white;
        border: none;
        background: none;
        transition: opacity 0.2s;
    }
    .close-modal:hover {
        opacity: 0.7;
    }
    .detail-item {
        padding: 16px;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        margin-bottom: 12px;
        transition: all 0.2s;
    }
    .detail-item:hover {
        background: #f8f9fa;
        border-color: var(--gasgo-blue);
    }
    .detail-label {
        font-weight: 600;
        color: var(--gasgo-blue);
        font-size: 0.9rem;
        margin-bottom: 6px;
    }
    .detail-value {
        font-size: 1.1rem;
        font-weight: 700;
        color: #333;
    }
    .detail-subtitle {
        font-size: 0.85rem;
        color: #888;
        margin-top: 4px;
    }
</style>
@endsection

@section('content')
<!-- Stats Row -->
<div class="row g-4 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="stat-card" onclick="showModal('totalOrdersModal')">
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
        <div class="stat-card" onclick="showModal('revenueModal')">
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
        <div class="stat-card" onclick="showModal('activeRidersModal')">
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
        <div class="stat-card" onclick="showModal('pendingOrdersModal')">
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
    <div class="col-lg-3 col-md-6">
        <div class="stat-card" onclick="showModal('customersModal')">
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

<!-- Customers Count + Low Stock Alerts + Recent Orders -->
<div class="row g-4 mb-4">
    <!-- Low Stock Alerts -->
    <div class="col-lg-4">
        <div class="stat-card" onclick="showModal('inventoryModal')" style="cursor:pointer;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0" style="color:var(--gasgo-blue);"><i class="fas fa-boxes me-2" style="color:var(--gasgo-orange);"></i>Inventory Overview</h6>
                <i class="fas fa-arrow-up-right" style="color:#999;font-size:.8rem;"></i>
            </div>
            
            @php
                $productsCount = $products->count();
                $freebiesCount = $freebies->count();
                $lowStockCount = $allItems->where('stock', '<=', 5)->count();
            @endphp
            
            <!-- Summary Stats -->
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <div style="background:#e3f2fd;padding:12px;border-radius:10px;text-align:center;">
                        <div style="font-size:1.3rem;font-weight:700;color:var(--gasgo-blue);">{{ $productsCount }}</div>
                        <div style="font-size:.75rem;color:#666;">Products</div>
                    </div>
                </div>
                <div class="col-6">
                    <div style="background:#fffbf0;padding:12px;border-radius:10px;text-align:center;">
                        <div style="font-size:1.3rem;font-weight:700;color:#ffc107;">{{ $freebiesCount }}</div>
                        <div style="font-size:.75rem;color:#666;">Freebies</div>
                    </div>
                </div>
            </div>
            
            <!-- Low Stock Alert -->
            @if($lowStockCount > 0)
                <div style="background:#fff5e6;border-left:3px solid var(--gasgo-orange);padding:12px;border-radius:8px;margin-bottom:12px;">
                    <div style="font-weight:600;color:var(--gasgo-orange);font-size:.85rem;"><i class="fas fa-warning me-1"></i>{{ $lowStockCount }} item{{ $lowStockCount > 1 ? 's' : '' }} low in stock</div>
                </div>
            @endif
            
            <!-- Items List -->
            <div style="max-height:280px;overflow-y:auto;">
                @forelse($allItems as $item)
                    @php
                        $isLow = $item->stock <= 5;
                        $isFreebie = $item->item_type === 'freebie';
                        $bgColor = $isLow ? '#fff5e6' : '#f8f9fa';
                        $borderColor = $isFreebie ? '#ffc107' : 'var(--gasgo-blue)';
                        $iconClass = $isFreebie ? 'fa-gift' : 'fa-box';
                        $iconColor = $isFreebie ? '#ffc107' : 'var(--gasgo-blue)';
                    @endphp
                    <div class="d-flex align-items-center p-2 mb-2" style="background:<?php echo $bgColor; ?>;border-left:3px solid <?php echo $borderColor; ?>;border-radius:8px;">
                        <div style="width:32px;height:32px;border-radius:8px;background:#e0e0e0;display:flex;align-items:center;justify-content:center;margin-right:10px;flex-shrink:0;">
                            @if($item->image)
                                <img src="{{ asset('images/' . $item->image) }}" alt="{{ $item->name }}" style="width:100%;height:100%;border-radius:6px;object-fit:cover;">
                            @else
                                <i class="fas <?php echo $iconClass; ?>" style="font-size:.75rem;color:<?php echo $iconColor; ?>;"></i>
                            @endif
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div class="fw-bold" style="font-size:.8rem;margin-bottom:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $item->name }}</div>
                            <div style="font-size:.7rem;color:#999;">Stock: <strong class="{{ $isLow ? 'text-danger' : '' }}">{{ $item->stock }}</strong></div>
                        </div>
                        <div style="margin-left:8px;flex-shrink:0;">
                            @if($isLow)
                                <span class="badge bg-warning text-dark" style="font-size:.65rem;">Low</span>
                            @else
                                <span class="badge bg-success" style="font-size:.65rem;">OK</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0 text-center py-3">No items found.</p>
                @endforelse
            </div>
            
            <div style="text-align:center;margin-top:12px;padding-top:12px;border-top:1px solid #e0e0e0;">
                <small style="color:var(--gasgo-blue);cursor:pointer;"><i class="fas fa-external-link-alt me-1"></i>View full inventory</small>
            </div>
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


<!-- ===== MODALS ===== -->

<!-- Total Orders Modal -->
<div id="totalOrdersModal" class="modal-detail">
    <div class="modal-detail-content">
        <div class="modal-header-custom">
            <h4><i class="fas fa-shopping-bag me-2"></i>Total Orders Details</h4>
            <button class="close-modal" onclick="closeModal('totalOrdersModal')">&times;</button>
        </div>
        <div class="modal-body-custom">
            @php
                $ordersByStatus = [
                    'pending' => $orders->where('status', 'pending')->count(),
                    'approved' => $orders->where('status', 'approved')->count(),
                    'assigned' => $orders->where('status', 'assigned')->count(),
                    'out_for_delivery' => $orders->where('status', 'out_for_delivery')->count(),
                    'delivered' => $orders->where('status', 'delivered')->count(),
                    'cancelled' => $orders->where('status', 'cancelled')->count(),
                ];
            @endphp
            <div class="detail-item">
                <div class="detail-label">All Time Orders</div>
                <div class="detail-value">{{ $totalOrders }}</div>
            </div>
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="detail-item" style="background: #fff3cd;">
                        <div class="detail-label">Pending</div>
                        <div class="detail-value">{{ $ordersByStatus['pending'] }}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-item" style="background: #d1ecf1;">
                        <div class="detail-label">Approved</div>
                        <div class="detail-value">{{ $ordersByStatus['approved'] }}</div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="detail-item" style="background: #e8f4fc;">
                        <div class="detail-label">Assigned</div>
                        <div class="detail-value">{{ $ordersByStatus['assigned'] }}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-item" style="background: #fff5e6;">
                        <div class="detail-label">Out for Delivery</div>
                        <div class="detail-value">{{ $ordersByStatus['out_for_delivery'] }}</div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="detail-item" style="background: #d4edda;">
                        <div class="detail-label">Delivered</div>
                        <div class="detail-value">{{ $ordersByStatus['delivered'] }}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-item" style="background: #f8d7da;">
                        <div class="detail-label">Cancelled</div>
                        <div class="detail-value">{{ $ordersByStatus['cancelled'] }}</div>
                    </div>
                </div>
            </div>
            <a href="{{ route('admin.orders') }}" class="btn btn-gasgo w-100 mt-4">
                <i class="fas fa-list me-2"></i>View All Orders
            </a>
        </div>
    </div>
</div>

<!-- Revenue Modal -->
<div id="revenueModal" class="modal-detail">
    <div class="modal-detail-content">
        <div class="modal-header-custom">
            <h4><i class="fas fa-peso-sign me-2"></i>Revenue Details</h4>
            <button class="close-modal" onclick="closeModal('revenueModal')">&times;</button>
        </div>
        <div class="modal-body-custom">
            @php
                $deliveredAmount = $orders->where('status', 'delivered')->sum('total_amount');
                $pendingAmount = $orders->where('status', 'pending')->sum('total_amount');
                $approvedAmount = $orders->where('status', 'approved')->sum('total_amount');
                $assignedAmount = $orders->where('status', 'assigned')->sum('total_amount');
                $outForDeliveryAmount = $orders->where('status', 'out_for_delivery')->sum('total_amount');
            @endphp
            <div class="detail-item">
                <div class="detail-label">Total Revenue (All Orders)</div>
                <div class="detail-value">₱{{ number_format($revenue, 2) }}</div>
                <div class="detail-subtitle">From {{ $orders->count() }} orders</div>
            </div>
            <div class="detail-item" style="background: #d4edda; margin-top: 20px;">
                <div class="detail-label">Completed Revenue</div>
                <div class="detail-value">₱{{ number_format($deliveredAmount, 2) }}</div>
                <div class="detail-subtitle">From {{ $orders->where('status', 'delivered')->count() }} delivered orders</div>
            </div>
            <div class="detail-item" style="background: #e8f4fc; margin-top: 12px;">
                <div class="detail-label">In Progress Revenue</div>
                <div class="detail-value">₱{{ number_format($approvedAmount + $assignedAmount + $outForDeliveryAmount, 2) }}</div>
                <div class="detail-subtitle">From {{ $orders->where('status', 'approved')->count() + $orders->where('status', 'assigned')->count() + $orders->where('status', 'out_for_delivery')->count() }} active orders</div>
            </div>
            <div class="detail-item" style="background: #fff3cd; margin-top: 12px;">
                <div class="detail-label">Pending Revenue</div>
                <div class="detail-value">₱{{ number_format($pendingAmount, 2) }}</div>
                <div class="detail-subtitle">From {{ $orders->where('status', 'pending')->count() }} pending orders</div>
            </div>
            <a href="{{ route('admin.orders') }}" class="btn btn-gasgo w-100 mt-4">
                <i class="fas fa-chart-bar me-2"></i>View Revenue Report
            </a>
        </div>
    </div>
</div>

<!-- Active Riders Modal -->
<div id="activeRidersModal" class="modal-detail">
    <div class="modal-detail-content">
        <div class="modal-header-custom">
            <h4><i class="fas fa-motorcycle me-2"></i>Active Riders</h4>
            <button class="close-modal" onclick="closeModal('activeRidersModal')">&times;</button>
        </div>
        <div class="modal-body-custom">
            <div class="detail-item">
                <div class="detail-label">Total Active Riders</div>
                <div class="detail-value">{{ $activeRiders }}</div>
            </div>
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="detail-item" style="background: #d4edda;">
                        <div class="detail-label">Available Now</div>
                        <div class="detail-value">{{ $riders->where('availability', 'available')->count() }}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-item" style="background: #fff3cd;">
                        <div class="detail-label">Busy</div>
                        <div class="detail-value">{{ $riders->where('availability', 'busy')->count() }}</div>
                    </div>
                </div>
            </div>
            <div class="detail-item" style="background: #e0e0e0; margin-top: 12px;">
                <div class="detail-label">Offline</div>
                <div class="detail-value">{{ $riders->where('availability', 'offline')->count() }}</div>
            </div>
            <h6 class="mt-4 mb-3 fw-bold" style="color: var(--gasgo-blue);">Rider Breakdown</h6>
            @forelse($riders as $rider)
                <div class="detail-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="detail-label">{{ $rider->user->name ?? 'Unknown' }}</div>
                            <div class="detail-subtitle">Deliveries Today: {{ $todayDeliveries[$rider->user_id] ?? 0 }}</div>
                        </div>
                        <span class="badge {{ $rider->availability === 'available' ? 'bg-success' : ($rider->availability === 'busy' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                            {{ ucfirst($rider->availability) }}
                        </span>
                    </div>
                </div>
            @empty
                <p class="text-muted">No riders registered.</p>
            @endforelse
            <a href="{{ route('admin.riders') }}" class="btn btn-gasgo w-100 mt-4">
                <i class="fas fa-users me-2"></i>Manage Riders
            </a>
        </div>
    </div>
</div>

<!-- Pending Orders Modal -->
<div id="pendingOrdersModal" class="modal-detail">
    <div class="modal-detail-content">
        <div class="modal-header-custom">
            <h4><i class="fas fa-clock me-2"></i>Pending Orders</h4>
            <button class="close-modal" onclick="closeModal('pendingOrdersModal')">&times;</button>
        </div>
        <div class="modal-body-custom">
            <div class="detail-item">
                <div class="detail-label">Pending Orders Awaiting Approval</div>
                <div class="detail-value">{{ $pendingOrders }}</div>
                <div class="detail-subtitle">Orders need admin review and approval</div>
            </div>
            @php
                $pendingList = $orders->where('status', 'pending')->take(10);
            @endphp
            @if($pendingList->count() > 0)
                <h6 class="mt-4 mb-3 fw-bold" style="color: var(--gasgo-blue);">Recent Pending Orders</h6>
                @foreach($pendingList as $order)
                    <div class="detail-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="detail-label">#{{ $order->order_number }}</div>
                                <div class="detail-subtitle">Customer: {{ $order->user->name ?? 'N/A' }}</div>
                                <div class="detail-subtitle">{{ $order->created_at->format('M j, Y — g:i A') }}</div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold" style="color: var(--gasgo-orange);">₱{{ number_format($order->total_amount, 2) }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <p class="text-muted text-center mt-4">No pending orders! All orders have been processed.</p>
            @endif
            <a href="{{ route('admin.orders') }}" class="btn btn-gasgo w-100 mt-4">
                <i class="fas fa-list me-2"></i>View All Orders
            </a>
        </div>
    </div>
</div>

<!-- Inventory Status Modal -->
<div id="inventoryModal" class="modal-detail">
    <div class="modal-detail-content">
        <div class="modal-header-custom">
            <h4><i class="fas fa-boxes me-2"></i>Inventory Status</h4>
            <button class="close-modal" onclick="closeModal('inventoryModal')">&times;</button>
        </div>
        <div class="modal-body-custom">
            @php
                $totalStock = $allItems->sum('stock');
            @endphp
            
            <!-- Summary Stats -->
            <div class="row g-2 mb-4">
                <div class="col-md-3">
                    <div style="background:#e3f2fd;padding:16px;border-radius:12px;text-align:center;border-left:4px solid var(--gasgo-blue);">
                        <div style="font-size:1.5rem;font-weight:700;color:var(--gasgo-blue);">{{ $products->sum('stock') }}</div>
                        <div style="font-size:.85rem;color:#666;">Product Stock</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div style="background:#fffbf0;padding:16px;border-radius:12px;text-align:center;border-left:4px solid #ffc107;">
                        <div style="font-size:1.5rem;font-weight:700;color:#ffc107;">{{ $freebies->sum('stock') }}</div>
                        <div style="font-size:.85rem;color:#666;">Freebie Stock</div>
                    </div>
                </div>
                <div class="col-md-3">
                    @php
                        $lowStockBg = $lowStockCount > 0 ? '#fff5e6' : '#f0f8ff';
                        $lowStockBorder = $lowStockCount > 0 ? 'var(--gasgo-orange)' : 'var(--gasgo-blue)';
                        $lowStockColor = $lowStockCount > 0 ? 'var(--gasgo-orange)' : 'var(--gasgo-blue)';
                    @endphp
                    <div style="background:<?php echo $lowStockBg; ?>;padding:16px;border-radius:12px;text-align:center;border-left:4px solid <?php echo $lowStockBorder; ?>;">
                        <div style="font-size:1.5rem;font-weight:700;color:<?php echo $lowStockColor; ?>;">{{ $lowStockCount }}</div>
                        <div style="font-size:.85rem;color:#666;">Low Stock</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div style="background:#f0f5ff;padding:16px;border-radius:12px;text-align:center;border-left:4px solid var(--gasgo-blue);">
                        <div style="font-size:1.5rem;font-weight:700;color:var(--gasgo-blue);">{{ $totalStock }}</div>
                        <div style="font-size:.85rem;color:#666;">Total Items</div>
                    </div>
                </div>
            </div>
            
            <!-- Products Section -->
            <div style="margin-bottom:24px;">
                <h6 class="fw-bold mb-3" style="color:var(--gasgo-blue);padding-bottom:10px;border-bottom:2px solid var(--gasgo-blue);"><i class="fas fa-bag-shopping me-2"></i>Products For Sale ({{ $products->count() }})</h6>
                @forelse($products as $item)
                    @php
                        $bgColor = $item->stock <= 5 ? '#fff5e6' : '#f8f9fa';
                        $isLow = $item->stock <= 5;
                    @endphp
                    <div class="detail-item" style="background: {{ $bgColor }};border-left:4px solid {{ $isLow ? 'var(--gasgo-orange)' : 'var(--gasgo-blue)' }};">
                        <div class="d-flex justify-content-between align-items-start">
                            <div style="flex:1;">
                                <div class="detail-label">{{ $item->name }}</div>
                                <div class="detail-subtitle">Stock: <strong class="{{ $isLow ? 'text-danger' : '' }}">{{ $item->stock }} units</strong></div>
                                <div class="detail-subtitle">₱{{ number_format($item->price, 2) }}</div>
                            </div>
                            <span class="badge {{ $isLow ? 'bg-warning text-dark' : 'bg-success' }}">{{ $isLow ? 'Low Stock' : 'OK' }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center py-3">No products found.</p>
                @endforelse
            </div>
            
            <!-- Freebies Section -->
            <div>
                <h6 class="fw-bold mb-3" style="color:#ffc107;padding-bottom:10px;border-bottom:2px solid #ffc107;"><i class="fas fa-gift me-2"></i>Freebies & Rewards ({{ $freebies->count() }})</h6>
                @forelse($freebies as $item)
                    @php
                        $bgColor = $item->stock <= 5 ? '#fff8e1' : '#fffbf0';
                        $isLow = $item->stock <= 5;
                    @endphp
                    <div class="detail-item" style="background: {{ $bgColor }};border-left:4px solid {{ $isLow ? 'var(--gasgo-orange)' : '#ffc107' }};">
                        <div class="d-flex justify-content-between align-items-start">
                            <div style="flex:1;">
                                <div class="detail-label" style="color:#ffc107;">{{ $item->name }}</div>
                                <div class="detail-subtitle">Stock: <strong class="{{ $isLow ? 'text-danger' : '' }}">{{ $item->stock }} units</strong></div>
                                <div class="detail-subtitle">{{ $item->category ?? 'Promotional' }}</div>
                            </div>
                            <span class="badge {{ $isLow ? 'bg-warning text-dark' : 'bg-success' }}">{{ $isLow ? 'Low Stock' : 'OK' }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center py-3">No freebies found.</p>
                @endforelse
            </div>
            <a href="{{ route('admin.products') }}" class="btn btn-gasgo w-100 mt-4">
                <i class="fas fa-warehouse me-2"></i>Manage Inventory
            </a>
        </div>
    </div>
</div>

<!-- Total Customers Modal -->
<div id="customersModal" class="modal-detail">
    <div class="modal-detail-content">
        <div class="modal-header-custom">
            <h4><i class="fas fa-users me-2"></i>Total Customers</h4>
            <button class="close-modal" onclick="closeModal('customersModal')">&times;</button>
        </div>
        <div class="modal-body-custom">
            <div class="detail-item">
                <div class="detail-label">Registered Customers</div>
                <div class="detail-value">{{ $totalCustomers }}</div>
                <div class="detail-subtitle">Total members of GasGo platform</div>
            </div>
            @php
                $customers = \App\Models\User::where('role', 'customer')->orderBy('created_at', 'desc')->get();
            @endphp
            <h6 class="mt-4 mb-3 fw-bold" style="color: var(--gasgo-blue);">Recent Customers</h6>
            @forelse($customers->take(10) as $customer)
                <div class="detail-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="detail-label">{{ $customer->name }}</div>
                            <div class="detail-subtitle">{{ $customer->email }}</div>
                            <div class="detail-subtitle" style="font-size: 0.75rem; color: #aaa;">
                                Joined: {{ $customer->created_at->format('M j, Y') }}
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-muted">No customers found.</p>
            @endforelse
            <a href="{{ route('admin.customers') }}" class="btn btn-gasgo w-100 mt-4">
                <i class="fas fa-list me-2"></i>View All Customers
            </a>
        </div>
    </div>
</div>

@section('admin-scripts')
<script>
    function showModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = 'auto';
        }
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target.classList.contains('modal-detail')) {
            event.target.classList.remove('show');
            document.body.style.overflow = 'auto';
        }
    }

    // Close on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            document.querySelectorAll('.modal-detail.show').forEach(modal => {
                modal.classList.remove('show');
                document.body.style.overflow = 'auto';
            });
        }
    });
</script>
@endsection

@endsection
