@extends('layouts.admin')

@section('title', 'GasGo Admin - Orders')
@section('nav-orders', 'active')
@section('page-title', 'Order Management')

@section('admin-styles')
<style>
    .filter-tabs { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:20px; }
    .filter-tab {
        padding:8px 20px; border-radius:25px; border:2px solid #e0e0e0;
        background:#fff; color:#666; font-size:.85rem; font-weight:600; cursor:pointer; transition:.3s;
    }
    .filter-tab:hover, .filter-tab.active { border-color:var(--gasgo-blue); background:var(--gasgo-blue); color:#fff; }
    .filter-tab .count { margin-left:6px; padding:2px 8px; border-radius:12px; font-size:.72rem; background:rgba(0,0,0,.1); }
    .filter-tab.active .count { background:rgba(255,255,255,.3); }
    .gasgo-table {
        background: white;
        border-radius: 12px;
        overflow-x: auto;
        box-shadow: 0 2px 8px rgba(0,0,0,.08);
        border: 1px solid #f0f0f0;
        width: 100%;
        display: block;
    }
    .gasgo-table .table {
        margin-bottom: 0;
        font-size: 0.9rem;
        width: 100%;
        table-layout: auto;
        display: table;
    }
    .gasgo-table thead {
        background: linear-gradient(135deg, #f8f9fa 0%, #f0f2f5 100%);
        border-bottom: 2px solid #e8e8e8;
        display: table-header-group;
    }
    .gasgo-table tbody {
        display: table-row-group;
        width: 100%;
    }
    .gasgo-table thead th {
        padding: 14px 12px;
        font-weight: 600;
        color: #334155;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        border: none;
    }
    .gasgo-table tbody tr {
        border-bottom: 1px solid #f5f5f5;
        transition: all 0.2s ease;
        display: table-row;
        visibility: visible;
    }
    .gasgo-table tbody tr:last-child {
        border-bottom: none;
    }
    .gasgo-table tbody td {
        padding: 12px;
        vertical-align: middle;
        color: #333;
    }
    .order-row:hover {
        background: linear-gradient(90deg, rgba(26, 109, 176, 0.04) 0%, rgba(26, 109, 176, 0.02) 100%) !important;
    }
    .order-row { 
        cursor: pointer; 
        transition: background 0.2s ease;
        display: table-row !important;
        visibility: visible !important;
    }
    .order-row.hidden {
        display: none !important;
    }
    .order-row.is-updating { opacity:.62; }
    .search-box { position:relative; max-width:320px; }
    .search-box input {
        border-radius:25px; padding:10px 20px 10px 42px; border:2px solid #e0e0e0;
        font-size:.88rem; width:100%; transition:border-color .3s;
    }
    .search-box input:focus { border-color:var(--gasgo-blue); outline:none; box-shadow:none; }
    .search-box i { position:absolute; left:16px; top:50%; transform:translateY(-50%); color:#aaa; }
    /* Status Badge Styles */
    .badge-status {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .badge-pending { background: #fef3c7; color: #92400e; }
    .badge-approved { background: #dbeafe; color: #0c4a6e; }
    .badge-assigned { background: #e0e7ff; color: #312e81; }
    .badge-out_for_delivery { background: #f3e8ff; color: #581c87; }
    .badge-delivered { background: #dcfce7; color: #166534; }
    .badge-cancelled { background: #fee2e2; color: #991b1b; }
</style>
@endsection

@php
    $statusCounts = $orders->groupBy('status')->map->count();
@endphp

@section('content')
<!-- Top Actions -->
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="searchOrders" placeholder="Search orders..." onkeyup="filterOrders()">
    </div>
    <div class="text-muted" style="font-size:.85rem;">
        Showing <strong id="ordersVisibleCount">{{ $orders->count() }}</strong> of <strong id="ordersTotalCount">{{ $orders->count() }}</strong> orders
    </div>
</div>

<!-- Filter Tabs -->
<div class="filter-tabs">
    <button class="filter-tab active" data-filter="all" onclick="setFilter(this,'all')">All<span class="count">{{ $orders->count() }}</span></button>
    <button class="filter-tab" data-filter="pending" onclick="setFilter(this,'pending')">Pending<span class="count">{{ $statusCounts['pending'] ?? 0 }}</span></button>
    <button class="filter-tab" data-filter="approved" onclick="setFilter(this,'approved')">Approved<span class="count">{{ $statusCounts['approved'] ?? 0 }}</span></button>
    <button class="filter-tab" data-filter="assigned" onclick="setFilter(this,'assigned')">Assigned<span class="count">{{ $statusCounts['assigned'] ?? 0 }}</span></button>
    <button class="filter-tab" data-filter="out_for_delivery" onclick="setFilter(this,'out_for_delivery')">Out for Delivery<span class="count">{{ $statusCounts['out_for_delivery'] ?? 0 }}</span></button>
    <button class="filter-tab" data-filter="delivered" onclick="setFilter(this,'delivered')">Delivered<span class="count">{{ $statusCounts['delivered'] ?? 0 }}</span></button>
    <button class="filter-tab" data-filter="cancelled" onclick="setFilter(this,'cancelled')">Cancelled<span class="count">{{ $statusCounts['cancelled'] ?? 0 }}</span></button>
</div>

<!-- Bulk Actions Toolbar -->
<div id="bulkActionsToolbar" class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3" style="display:none;background:#fff8e8;border:1px solid #ffe0a6;border-radius:12px;padding:10px 14px;">
    <div style="font-size:.88rem;">
        <strong id="selectedCount">0</strong> order(s) selected
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-sm" style="background:#28a745;color:#fff;font-weight:600;" onclick="openBulkApproveModal()">
            <i class="fas fa-check me-1"></i>Approve Order
        </button>
        <button type="button" class="btn btn-sm" style="background:var(--gasgo-orange);color:#fff;font-weight:600;" onclick="openBulkAssignModal()">
            <i class="fas fa-motorcycle me-1"></i>Assign Rider
        </button>
        <button type="button" class="btn btn-sm" style="background:#17a2b8;color:#fff;font-weight:600;" onclick="openBulkMarkOutForDeliveryModal()">
            <i class="fas fa-truck me-1"></i>Mark as Out for Delivery
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearAllSelections()">
            Clear
        </button>
    </div>
</div>

<!-- Orders Table -->
<div class="gasgo-table">
    <table class="table" id="ordersTable">
        <thead>
            <tr>
                <th style="width:42px;">
                    <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)">
                </th>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Items</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Rewards</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
                <tr class="order-row" data-order-id="{{ $order->id }}" data-status="{{ $order->status }}">
                    <td>
                        @if(in_array($order->status, ['pending', 'approved', 'assigned']))
                            <input type="checkbox" class="order-checkbox" value="{{ $order->id }}" onchange="updateBulkSelection()">
                        @endif
                    </td>
                    <td class="fw-bold">#{{ $order->order_number }}</td>
                    <td>
                        {{ $order->user->name ?? 'N/A' }}
                        <br><small class="text-muted">{{ $order->contact_number }}</small>
                    </td>
                    <td>
                        @foreach($order->orderItems->where('is_reward', false) as $item)
                            {{ $item->product_name }} &times;{{ $item->quantity }}@if(!$loop->last), @endif
                        @endforeach
                    </td>
                    <td class="fw-bold order-total {{ $order->status === 'cancelled' ? 'text-decoration-line-through text-muted' : '' }}">
                        ₱{{ number_format($order->total_amount, 2) }}
                    </td>
                    <td>
                        <span class="badge {{ $order->payment_method === 'gcash' ? 'bg-success' : 'bg-secondary' }}" style="font-size:.72rem;">
                            {{ ucfirst($order->payment_method ?? 'N/A') }}
                        </span>
                    </td>
                    <td>
                        <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                            <span class="badge-status badge-{{ $order->status }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                            @if($order->is_urgent)
                                <span class="badge" style="background:#dc3545; color:white; font-size:.72rem; font-weight:600;"><i class="fas fa-bolt me-1"></i>URGENT</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        @php
                            $rewardItems = $order->orderItems->where('is_reward', true);
                        @endphp
                        @if($rewardItems->count() > 0)
                            <span class="badge bg-success" style="font-size:.72rem;" title="This order includes reward items to pack">
                                <i class="fas fa-gift me-1"></i>REWARD INCLUDED
                            </span>
                            <div style="font-size:.7rem; margin-top:4px; color:#555;">
                                @foreach($rewardItems as $reward)
                                    @if(!$loop->first)<br>@endif
                                    {{ $reward->product_name }}
                                @endforeach
                            </div>
                        @else
                            <span class="text-muted" style="font-size:.72rem;">—</span>
                        @endif
                    </td>
                    <td style="font-size:.82rem;">
                        {{ $order->created_at->format('M j, Y') }}
                        <br><small class="text-muted">{{ $order->created_at->format('h:i A') }}</small>
                    </td>
                    <td>
                        <div class="d-flex gap-1 flex-wrap">
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-info" title="View Details">
                                <i class="fas fa-eye me-1"></i>Details
                            </a>
                            @if($order->status === 'pending')
                                <form action="{{ route('admin.orders.status', $order) }}" method="POST" style="display:inline;">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="status" value="approved">
                                    <button class="btn btn-sm btn-action" style="background:#28a745;color:#fff;" title="Approve Order"><i class="fas fa-check me-1"></i>Approve</button>
                                </form>
                                <button class="btn btn-sm btn-action assign-btn" style="background:var(--gasgo-orange);color:#fff;" title="Assign Rider"
                                    data-order-id="{{ $order->id }}" data-order-number="{{ $order->order_number }}">
                                    <i class="fas fa-motorcycle me-1"></i>Assign
                                </button>
                                <form action="{{ route('admin.orders.status', $order) }}" method="POST" class="cancel-order-form" data-order-id="{{ $order->id }}" onsubmit="return confirm('Cancel this order?')">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="status" value="cancelled">
                                    <button class="btn btn-sm btn-action" style="background:#dc3545;color:#fff;" title="Cancel"><i class="fas fa-times"></i></button>
                                </form>
                            @elseif($order->status === 'approved')
                                <span class="badge bg-success me-1"><i class="fas fa-check-circle me-1"></i>Approved</span>
                                <button class="btn btn-sm btn-action assign-btn" style="background:var(--gasgo-orange);color:#fff;" title="Assign Rider"
                                    data-order-id="{{ $order->id }}" data-order-number="{{ $order->order_number }}">
                                    <i class="fas fa-motorcycle me-1"></i>Assign
                                </button>
                            @elseif(in_array($order->status, ['assigned', 'out_for_delivery']))
                                <span class="badge bg-info me-1"><i class="fas fa-check-circle me-1"></i>Assigned</span>
                                <span class="text-muted" style="font-size:.82rem;">
                                    <i class="fas fa-motorcycle me-1"></i>
                                    {{ $order->delivery->rider->name ?? 'Rider' }}
                                </span>
                            @elseif($order->status === 'delivered')
                            @elseif($order->status === 'cancelled')
                                <span class="text-muted" style="font-size:.82rem;">Cancelled</span>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center text-muted py-4">No orders found.</td>
                </tr>
            @endforelse
            <tr id="ordersNoResults" style="display:none;">
                <td colspan="10" class="text-center text-muted py-4">
                    <i class="fas fa-search me-2"></i>No orders match your filter.
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Assign Rider Modal -->
<div class="modal fade" id="assignRiderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:16px;">
            <form id="assignRiderForm" method="POST" action="{{ route('admin.deliveries.store') }}">
                @csrf
                <input type="hidden" name="order_id" id="assignOrderId">
                <div class="modal-header" style="border-bottom:none;">
                    <h5 class="modal-title fw-bold" style="color:var(--gasgo-blue);">
                        <i class="fas fa-motorcycle me-2" style="color:var(--gasgo-orange);"></i>Assign Rider
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted" style="font-size:.88rem;">
                        Assign a rider to order <strong id="assignOrderNumber"></strong>:
                    </p>
                    @if($riders->count() > 0)
                        <div class="list-group">
                            @foreach($riders as $rider)
                                <label class="list-group-item d-flex align-items-center gap-3 mb-2" style="border-radius:12px;cursor:pointer;">
                                    <input type="radio" name="rider_id" value="{{ $rider->user_id }}" class="form-check-input" required>
                                    <div>
                                        <div class="fw-bold">{{ $rider->user->name ?? 'Unknown' }}</div>
                                        <small class="text-muted">
                                            {{ $rider->vehicle_type ?? 'No vehicle info' }}
                                            @if($rider->plate_number) &bull; {{ $rider->plate_number }} @endif
                                        </small>
                                    </div>
                                    @if($rider->availability === 'available')
                                        <span class="badge bg-success ms-auto">Available</span>
                                    @elseif($rider->availability === 'busy')
                                        <span class="badge bg-warning text-dark ms-auto">Busy</span>
                                    @elseif($rider->availability === 'returning')
                                        <span class="badge bg-info ms-auto">Returning to Store</span>
                                    @else
                                        <span class="badge bg-secondary ms-auto">Offline</span>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-warning" style="border-radius:12px;">
                            <i class="fas fa-exclamation-triangle me-2"></i>No riders are currently online. Please wait for a rider to come online.
                        </div>
                    @endif
                </div>
                <div class="modal-footer" style="border-top:none;">
                    <button type="button" class="btn" data-bs-dismiss="modal" style="border-radius:10px;">Cancel</button>
                    @if($riders->count() > 0)
                        <button type="submit" class="btn" style="background:var(--gasgo-orange);color:#fff;border-radius:10px;font-weight:600;">
                            <i class="fas fa-check me-1"></i>Assign Rider
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Assign Rider Modal -->
<div class="modal fade" id="bulkAssignRiderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:16px;">
            <form id="bulkAssignRiderForm" method="POST" action="{{ route('admin.deliveries.store') }}">
                @csrf
                <div class="modal-header" style="border-bottom:none;">
                    <h5 class="modal-title fw-bold" style="color:var(--gasgo-blue);">
                        <i class="fas fa-layer-group me-2" style="color:var(--gasgo-orange);"></i>Bulk Assign Rider
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted" style="font-size:.88rem;">
                        Assign one rider to <strong><span id="bulkOrderCount">0</span></strong> selected order(s):
                    </p>
                    @if($riders->count() > 0)
                        <div class="list-group">
                            @foreach($riders as $rider)
                                <label class="list-group-item d-flex align-items-center gap-3 mb-2" style="border-radius:12px;cursor:pointer;">
                                    <input type="radio" name="rider_id" value="{{ $rider->user_id }}" class="form-check-input" required>
                                    <div>
                                        <div class="fw-bold">{{ $rider->user->name ?? 'Unknown' }}</div>
                                        <small class="text-muted">
                                            {{ $rider->vehicle_type ?? 'No vehicle info' }}
                                            @if($rider->plate_number) &bull; {{ $rider->plate_number }} @endif
                                        </small>
                                    </div>
                                    @if($rider->availability === 'available')
                                        <span class="badge bg-success ms-auto">Available</span>
                                    @elseif($rider->availability === 'busy')
                                        <span class="badge bg-warning text-dark ms-auto">Busy</span>
                                    @elseif($rider->availability === 'returning')
                                        <span class="badge bg-info ms-auto">Returning to Store</span>
                                    @else
                                        <span class="badge bg-secondary ms-auto">Offline</span>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-warning" style="border-radius:12px;">
                            <i class="fas fa-exclamation-triangle me-2"></i>No riders are currently online. Please wait for a rider to come online.
                        </div>
                    @endif
                </div>
                <div class="modal-footer" style="border-top:none;">
                    <button type="button" class="btn" data-bs-dismiss="modal" style="border-radius:10px;">Cancel</button>
                    @if($riders->count() > 0)
                        <button type="submit" class="btn" style="background:var(--gasgo-orange);color:#fff;border-radius:10px;font-weight:600;">
                            <i class="fas fa-check me-1"></i>Assign Rider
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Approve Modal -->
<div class="modal fade" id="bulkApproveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:16px;">
            <form id="bulkApproveForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-header" style="border-bottom:none;">
                    <h5 class="modal-title fw-bold" style="color:var(--gasgo-blue);">
                        <i class="fas fa-check me-2" style="color:#28a745;"></i>Approve Orders
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted" style="font-size:.88rem;">
                        Approve <strong><span id="bulkApproveCount">0</span></strong> selected order(s)?
                    </p>
                    <div class="alert alert-info" style="border-radius:12px;font-size:.88rem;">
                        <i class="fas fa-info-circle me-2"></i>This will change the order status to "Approved" and make them available for rider assignment.
                    </div>
                </div>
                <div class="modal-footer" style="border-top:none;">
                    <button type="button" class="btn" data-bs-dismiss="modal" style="border-radius:10px;">Cancel</button>
                    <button type="submit" class="btn" style="background:#28a745;color:#fff;border-radius:10px;font-weight:600;">
                        <i class="fas fa-check me-1"></i>Approve All
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Mark as Out for Delivery Modal -->
<div class="modal fade" id="bulkMarkOutForDeliveryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:16px;">
            <form id="bulkMarkOutForDeliveryForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-header" style="border-bottom:none;">
                    <h5 class="modal-title fw-bold" style="color:var(--gasgo-blue);">
                        <i class="fas fa-truck me-2" style="color:#17a2b8;"></i>Mark as Out for Delivery
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted" style="font-size:.88rem;">
                        Mark <strong><span id="bulkMarkOutForDeliveryCount">0</span></strong> selected order(s) as out for delivery?
                    </p>
                    <div class="alert alert-info" style="border-radius:12px;font-size:.88rem;">
                        <i class="fas fa-info-circle me-2"></i>This will change the order status to "Out for Delivery". Use this when riders start their delivery route.
                    </div>
                </div>
                <div class="modal-footer" style="border-top:none;">
                    <button type="button" class="btn" data-bs-dismiss="modal" style="border-radius:10px;">Cancel</button>
                    <button type="submit" class="btn" style="background:#17a2b8;color:#fff;border-radius:10px;font-weight:600;">
                        <i class="fas fa-truck me-1"></i>Mark as Out for Delivery
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let currentFilter = 'all';

    function getStatusBadgeClass(status) {
        const map = {
            pending: 'badge-pending',
            approved: 'badge-approved',
            assigned: 'badge-assigned',
            out_for_delivery: 'badge-out_for_delivery',
            delivered: 'badge-delivered',
            cancelled: 'badge-cancelled'
        };

        return map[status] || 'badge-pending';
    }

    function formatStatus(status) {
        return String(status || '').replaceAll('_', ' ').replace(/\b\w/g, c => c.toUpperCase());
    }

    function showOrderToast(message, isError = false) {
        if (typeof window.showAdminToast === 'function') {
            window.showAdminToast(message, isError);
            return;
        }
        alert(message);
    }

    function setButtonLoading(button, isLoading, loadingText) {
        if (!button) return;
        if (isLoading) {
            if (!button.dataset.originalHtml) {
                button.dataset.originalHtml = button.innerHTML;
            }
            button.classList.add('btn-loading');
            button.disabled = true;
            button.innerHTML = `<span class="btn-label">${button.dataset.originalHtml}</span><span class="btn-spinner"><i class="fas fa-circle-notch fa-spin me-1"></i>${loadingText || 'Loading...'}</span>`;
        } else {
            button.classList.remove('btn-loading');
            button.disabled = false;
            if (button.dataset.originalHtml) {
                button.innerHTML = button.dataset.originalHtml;
            }
        }
    }

    function updateTabCounts() {
        const rows = document.querySelectorAll('#ordersTable tbody .order-row');
        const counts = { all: rows.length };

        rows.forEach((row) => {
            const status = row.dataset.status;
            counts[status] = (counts[status] || 0) + 1;
        });

        document.querySelectorAll('.filter-tab').forEach((tab) => {
            const key = tab.dataset.filter;
            const countEl = tab.querySelector('.count');
            if (countEl) {
                countEl.textContent = counts[key] || 0;
            }
        });

        document.getElementById('ordersTotalCount').textContent = counts.all || 0;
    }

    function updateOrderRowStatus(orderId, newStatus, riderName = null) {
        const row = document.querySelector(`.order-row[data-order-id="${orderId}"]`);
        if (!row) return;

        row.dataset.status = newStatus;
        const badge = row.querySelector('.badge-status');
        if (badge) {
            badge.className = `badge-status ${getStatusBadgeClass(newStatus)}`;
            badge.textContent = formatStatus(newStatus);
        }

        const totalCell = row.querySelector('.order-total');
        if (totalCell) {
            totalCell.classList.toggle('text-decoration-line-through', newStatus === 'cancelled');
            totalCell.classList.toggle('text-muted', newStatus === 'cancelled');
        }

        const actionCell = row.lastElementChild;
        if (actionCell) {
            if (newStatus === 'assigned' || newStatus === 'out_for_delivery') {
                const name = riderName || 'Rider';
                actionCell.innerHTML = `<span class="text-muted" style="font-size:.82rem;"><i class="fas fa-motorcycle text-info me-1"></i>${name}</span>`;
            } else if (newStatus === 'delivered') {
                actionCell.innerHTML = '<span class="text-muted" style="font-size:.82rem;"><i class="fas fa-check-circle text-success me-1"></i>Done</span>';
            } else if (newStatus === 'cancelled') {
                actionCell.innerHTML = '<span class="text-muted" style="font-size:.82rem;">Cancelled</span>';
            }
        }

        updateTabCounts();
        filterOrders();
    }

    function setFilter(btn, status) {
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');
        currentFilter = status;
        filterOrders();
    }

    function filterOrders() {
        const search = document.getElementById('searchOrders').value.toLowerCase();
        let visibleCount = 0;

        document.querySelectorAll('#ordersTable tbody .order-row').forEach(row => {
            const matchStatus = currentFilter === 'all' || row.dataset.status === currentFilter;
            const matchSearch = !search || row.textContent.toLowerCase().includes(search);
            const visible = matchStatus && matchSearch;
            
            if (visible) {
                row.classList.remove('hidden');
                row.style.display = '';
                visibleCount++;
            } else {
                row.classList.add('hidden');
                row.style.display = 'none';
            }
        });

        document.getElementById('ordersVisibleCount').textContent = visibleCount;
        document.getElementById('ordersNoResults').style.display = visibleCount === 0 ? '' : 'none';
    }

    function openAssignModal(orderId, orderNumber) {
        document.getElementById('assignOrderId').value = orderId;
        document.getElementById('assignOrderNumber').textContent = '#' + orderNumber;
        // Uncheck any previously selected rider
        document.querySelectorAll('#assignRiderForm input[name="rider_id"]').forEach(r => r.checked = false);
        new bootstrap.Modal(document.getElementById('assignRiderModal')).show();
    }

    async function submitAssignRider(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const orderId = formData.get('order_id');
        const row = document.querySelector(`.order-row[data-order-id="${orderId}"]`);
        const submitBtn = form.querySelector('button[type="submit"]');
        const riderInput = form.querySelector('input[name="rider_id"]:checked');
        if (!riderInput) {
            showOrderToast('Please choose a rider first.', true);
            return;
        }

        const riderLabel = riderInput.closest('label');
        const riderName = riderLabel ? riderLabel.querySelector('.fw-bold')?.textContent?.trim() : 'Rider';

        setButtonLoading(submitBtn, true, 'Assigning...');
        if (row) row.classList.add('is-updating');

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: formData,
            });

            if (!response.ok) {
                showOrderToast('Unable to assign rider. Try again.', true);
                return;
            }

            const payload = await response.json();
            
            // Hide the row with animation
            if (row) {
                row.style.transition = 'opacity 0.3s ease-out';
                row.style.opacity = '0';
                setTimeout(() => {
                    row.style.display = 'none';
                    updateTabCounts();
                    filterOrders();
                }, 300);
            }
            
            const modalEl = document.getElementById('assignRiderModal');
            bootstrap.Modal.getInstance(modalEl)?.hide();
            showOrderToast(payload.message || 'Rider assigned successfully.');
        } catch (error) {
            showOrderToast('Unable to assign rider. Try again.', true);
        } finally {
            setButtonLoading(submitBtn, false);
            if (row) row.classList.remove('is-updating');
        }
    }

    async function submitCancelOrder(event) {
        event.preventDefault();
        const form = event.target;
        const orderId = form.dataset.orderId;
        const formData = new FormData(form);
        const row = document.querySelector(`.order-row[data-order-id="${orderId}"]`);
        const submitBtn = form.querySelector('button[type="submit"]');

        setButtonLoading(submitBtn, true, 'Cancelling...');
        if (row) row.classList.add('is-updating');

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: formData,
            });

            if (!response.ok) {
                showOrderToast('Unable to cancel order. Try again.', true);
                return;
            }

            const payload = await response.json();
            
            // Hide the row with animation
            if (row) {
                row.style.transition = 'opacity 0.3s ease-out';
                row.style.opacity = '0';
                setTimeout(() => {
                    row.style.display = 'none';
                    updateTabCounts();
                    filterOrders();
                }, 300);
            }
            
            showOrderToast(payload.message || 'Order cancelled.');
        } catch (error) {
            showOrderToast('Unable to cancel order. Try again.', true);
        } finally {
            setButtonLoading(submitBtn, false);
            if (row) row.classList.remove('is-updating');
        }
    }

    // Bulk Selection Functions
    function getSelectedOrderIds() {
        const checkboxes = document.querySelectorAll('#ordersTable .order-checkbox:checked');
        return Array.from(checkboxes).map(cb => cb.value);
    }

    function updateBulkSelection() {
        const selectedCount = getSelectedOrderIds().length;
        const toolbar = document.getElementById('bulkActionsToolbar');
        const selectedCountEl = document.getElementById('selectedCount');
        
        selectedCountEl.textContent = selectedCount;
        toolbar.style.display = selectedCount > 0 ? 'flex' : 'none';
    }

    function toggleSelectAll(checkbox) {
        const isChecked = checkbox.checked;
        document.querySelectorAll('#ordersTable .order-checkbox').forEach(cb => {
            if (cb.offsetParent !== null) { // Only check visible rows
                cb.checked = isChecked;
            }
        });
        updateBulkSelection();
    }

    function clearAllSelections() {
        document.getElementById('selectAllCheckbox').checked = false;
        document.querySelectorAll('#ordersTable .order-checkbox').forEach(cb => cb.checked = false);
        updateBulkSelection();
    }

    function openBulkAssignModal() {
        const selectedIds = getSelectedOrderIds();
        if (selectedIds.length === 0) {
            showOrderToast('Please select at least one order.', true);
            return;
        }
        
        document.getElementById('bulkOrderCount').textContent = selectedIds.length;
        
        // Clear previously selected rider
        document.querySelectorAll('#bulkAssignRiderForm input[name="rider_id"]').forEach(r => r.checked = false);
        
        // Store selected order IDs in hidden inputs
        let hiddenInputsContainer = document.getElementById('bulkOrderIdsContainer');
        if (!hiddenInputsContainer) {
            hiddenInputsContainer = document.createElement('div');
            hiddenInputsContainer.id = 'bulkOrderIdsContainer';
            hiddenInputsContainer.style.display = 'none';
            document.getElementById('bulkAssignRiderForm').appendChild(hiddenInputsContainer);
        }
        hiddenInputsContainer.innerHTML = '';
        
        selectedIds.forEach(orderId => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'order_ids[]';
            input.value = orderId;
            hiddenInputsContainer.appendChild(input);
        });
        
        new bootstrap.Modal(document.getElementById('bulkAssignRiderModal')).show();
    }

    async function submitBulkAssignRider(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const orderIds = Array.from(formData.getAll('order_ids[]'));
        const riderId = formData.get('rider_id');
        
        if (!riderId) {
            showOrderToast('Please choose a rider first.', true);
            return;
        }

        const riderInput = form.querySelector(`input[name="rider_id"][value="${riderId}"]`);
        const riderLabel = riderInput?.closest('label');
        const riderName = riderLabel ? riderLabel.querySelector('.fw-bold')?.textContent?.trim() : 'Rider';

        const submitBtn = form.querySelector('button[type="submit"]');
        setButtonLoading(submitBtn, true, 'Assigning...');

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: formData,
            });

            if (!response.ok) {
                showOrderToast('Unable to assign riders. Try again.', true);
                return;
            }

            const payload = await response.json();
            
            const assignedIds = Array.isArray(payload.order_ids) && payload.order_ids.length
                ? payload.order_ids.map(id => String(id))
                : orderIds;

            // Remove assigned rows from the table with animation
            assignedIds.forEach(orderId => {
                const row = document.querySelector(`.order-row[data-order-id="${orderId}"]`);
                if (row) {
                    row.style.transition = 'opacity 0.3s ease-out';
                    row.style.opacity = '0';
                    setTimeout(() => {
                        row.style.display = 'none';
                    }, 300);
                }
            });
            
            // Close modal and clear selection
            const modalEl = document.getElementById('bulkAssignRiderModal');
            bootstrap.Modal.getInstance(modalEl)?.hide();
            clearAllSelections();
            
            // Update counts and filters
            setTimeout(() => {
                updateTabCounts();
                filterOrders();
            }, 350);
            
            showOrderToast(payload.message || `Successfully assigned ${assignedIds.length} order(s) to this rider.`);
        } catch (error) {
            showOrderToast('Unable to assign riders. Try again.', true);
        } finally {
            setButtonLoading(submitBtn, false);
        }
    }

    function openBulkApproveModal() {
        const selectedIds = getSelectedOrderIds();
        if (selectedIds.length === 0) {
            showOrderToast('Please select at least one order.', true);
            return;
        }
        
        document.getElementById('bulkApproveCount').textContent = selectedIds.length;
        
        // Store selected order IDs in hidden inputs
        let hiddenInputsContainer = document.getElementById('bulkApproveOrderIdsContainer');
        if (!hiddenInputsContainer) {
            hiddenInputsContainer = document.createElement('div');
            hiddenInputsContainer.id = 'bulkApproveOrderIdsContainer';
            hiddenInputsContainer.style.display = 'none';
            document.getElementById('bulkApproveForm').appendChild(hiddenInputsContainer);
        }
        hiddenInputsContainer.innerHTML = '';
        
        selectedIds.forEach(orderId => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'order_ids[]';
            input.value = orderId;
            hiddenInputsContainer.appendChild(input);
        });
        
        new bootstrap.Modal(document.getElementById('bulkApproveModal')).show();
    }

    async function submitBulkApprove(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const orderIds = Array.from(formData.getAll('order_ids[]'));
        
        if (!orderIds.length) {
            showOrderToast('No orders selected.', true);
            return;
        }

        const submitBtn = form.querySelector('button[type="submit"]');
        setButtonLoading(submitBtn, true, 'Approving...');
        
        try {
            const response = await fetch('{{ route("admin.orders.bulk-update-status") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    order_ids: orderIds,
                    status: 'approved'
                })
            });

            if (!response.ok) {
                showOrderToast('Unable to approve orders. Try again.', true);
                return;
            }

            const payload = await response.json();
            
            // Remove rows from the table with animation
            orderIds.forEach(orderId => {
                const row = document.querySelector(`.order-row[data-order-id="${orderId}"]`);
                if (row) {
                    row.style.transition = 'opacity 0.3s ease-out';
                    row.style.opacity = '0';
                    setTimeout(() => {
                        row.style.display = 'none';
                    }, 300);
                }
            });
            
            // Close modal and clear selection
            const modalEl = document.getElementById('bulkApproveModal');
            bootstrap.Modal.getInstance(modalEl)?.hide();
            clearAllSelections();
            
            // Update counts and filters
            setTimeout(() => {
                updateTabCounts();
                filterOrders();
            }, 350);
            
            showOrderToast(`Successfully approved ${orderIds.length} order(s).`);
        } catch (error) {
            showOrderToast('Unable to approve orders. Try again.', true);
        } finally {
            setButtonLoading(submitBtn, false);
        }
    }

    function openBulkMarkOutForDeliveryModal() {
        const selectedIds = getSelectedOrderIds();
        if (selectedIds.length === 0) {
            showOrderToast('Please select at least one order.', true);
            return;
        }
        
        document.getElementById('bulkMarkOutForDeliveryCount').textContent = selectedIds.length;
        
        // Store selected order IDs in hidden inputs
        let hiddenInputsContainer = document.getElementById('bulkMarkOutForDeliveryOrderIdsContainer');
        if (!hiddenInputsContainer) {
            hiddenInputsContainer = document.createElement('div');
            hiddenInputsContainer.id = 'bulkMarkOutForDeliveryOrderIdsContainer';
            hiddenInputsContainer.style.display = 'none';
            document.getElementById('bulkMarkOutForDeliveryForm').appendChild(hiddenInputsContainer);
        }
        hiddenInputsContainer.innerHTML = '';
        
        selectedIds.forEach(orderId => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'order_ids[]';
            input.value = orderId;
            hiddenInputsContainer.appendChild(input);
        });
        
        new bootstrap.Modal(document.getElementById('bulkMarkOutForDeliveryModal')).show();
    }

    async function submitBulkMarkOutForDelivery(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const orderIds = Array.from(formData.getAll('order_ids[]'));
        
        if (!orderIds.length) {
            showOrderToast('No orders selected.', true);
            return;
        }

        const submitBtn = form.querySelector('button[type="submit"]');
        setButtonLoading(submitBtn, true, 'Updating...');
        
        try {
            const response = await fetch('{{ route("admin.orders.bulk-update-status") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    order_ids: orderIds,
                    status: 'out_for_delivery'
                })
            });

            if (!response.ok) {
                showOrderToast('Unable to mark orders as out for delivery. Try again.', true);
                return;
            }

            const payload = await response.json();
            
            // Remove rows from the table with animation
            orderIds.forEach(orderId => {
                const row = document.querySelector(`.order-row[data-order-id="${orderId}"]`);
                if (row) {
                    row.style.transition = 'opacity 0.3s ease-out';
                    row.style.opacity = '0';
                    setTimeout(() => {
                        row.style.display = 'none';
                    }, 300);
                }
            });
            
            // Close modal and clear selection
            const modalEl = document.getElementById('bulkMarkOutForDeliveryModal');
            bootstrap.Modal.getInstance(modalEl)?.hide();
            clearAllSelections();
            
            // Update counts and filters
            setTimeout(() => {
                updateTabCounts();
                filterOrders();
            }, 350);
            
            showOrderToast(`Successfully marked ${orderIds.length} order(s) as out for delivery.`);
        } catch (error) {
            showOrderToast('Unable to mark orders as out for delivery. Try again.', true);
        } finally {
            setButtonLoading(submitBtn, false);
        }
    }

    // Event listener for assign buttons
    document.addEventListener('DOMContentLoaded', function() {
        // Attach form submit handlers
        const bulkApproveForm = document.getElementById('bulkApproveForm');
        if (bulkApproveForm) {
            bulkApproveForm.addEventListener('submit', submitBulkApprove);
        }
        
        const bulkMarkOutForDeliveryForm = document.getElementById('bulkMarkOutForDeliveryForm');
        if (bulkMarkOutForDeliveryForm) {
            bulkMarkOutForDeliveryForm.addEventListener('submit', submitBulkMarkOutForDelivery);
        }

        document.querySelectorAll('.assign-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const orderId = this.dataset.orderId;
                const orderNumber = this.dataset.orderNumber;
                openAssignModal(orderId, orderNumber);
            });
        });

        document.getElementById('assignRiderForm')?.addEventListener('submit', submitAssignRider);

        document.getElementById('bulkAssignRiderForm')?.addEventListener('submit', submitBulkAssignRider);

        document.querySelectorAll('.cancel-order-form').forEach((form) => {
            form.addEventListener('submit', submitCancelOrder);
        });

        updateTabCounts();

        filterOrders();
    });
</script>
@endsection
