@extends('layouts.admin')

@section('title', 'Orders')
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

    @media (max-width: 768px) {
        .filter-tabs {
            display: flex;
            flex-wrap: nowrap;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            padding-bottom: 6px;
            gap: 6px;
            scrollbar-width: none;
        }
        .filter-tabs::-webkit-scrollbar { display: none; }
        .filter-tab {
            flex-shrink: 0;
            padding: 6px 14px;
            font-size: 0.78rem;
        }
        .search-box {
            max-width: 100%;
            width: 100%;
        }
        .gasgo-table {
            border-radius: 12px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .gasgo-table thead th {
            padding: 10px 8px;
            font-size: 0.72rem;
            white-space: nowrap;
        }
        .gasgo-table tbody td {
            padding: 10px 8px;
            font-size: 0.82rem;
        }
        .btn-action {
            padding: 4px 8px;
            font-size: 0.74rem;
        }
    }
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

<!-- Orders Table -->
<div class="gasgo-table">
    <table class="table" id="ordersTable">
        <thead>
            <tr>
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
                        ₱{{ number_format($order->fee_free_total, 2) }}
                        @if($order->discount > 0)
                            <div class="text-muted" style="font-size:.78rem; margin-top:4px;">
                                Discount applied: ₱{{ number_format($order->discount, 2) }}
                            </div>
                        @endif
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
                        <div class="d-flex gap-1 flex-wrap align-items-center">
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-info" title="View Details">
                                <i class="fas fa-eye me-1"></i>Details
                            </a>
                            @if($order->status === 'pending')
                                <form action="{{ route('admin.orders.status', $order) }}" method="POST" class="cancel-order-form" data-order-id="{{ $order->id }}" onsubmit="return confirm('Cancel this order?')">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="status" value="cancelled">
                                    <button class="btn btn-sm btn-action" style="background:#dc3545;color:#fff;" title="Cancel"><i class="fas fa-times me-1"></i>Cancel</button>
                                </form>
                            @elseif(in_array($order->status, ['assigned', 'out_for_delivery']))
                                <span class="text-muted" style="font-size:.82rem;">
                                    <i class="fas fa-motorcycle me-1"></i>
                                    {{ $order->delivery->rider->name ?? 'Rider' }}
                                </span>
                            @elseif($order->status === 'cancelled')
                                <span class="text-muted" style="font-size:.82rem;">Cancelled</span>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">No orders found.</td>
                </tr>
            @endforelse
            <tr id="ordersNoResults" style="display:none;">
                <td colspan="9" class="text-center text-muted py-4">
                    <i class="fas fa-search me-2"></i>No orders match your filter.
                </td>
            </tr>
        </tbody>
    </table>
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
                actionCell.innerHTML = `<a href="/admin/orders/${orderId}" class="btn btn-sm btn-info me-1" title="View Details"><i class="fas fa-eye me-1"></i>Details</a><span class="text-muted" style="font-size:.82rem;"><i class="fas fa-motorcycle text-info me-1"></i>${name}</span>`;
            } else if (newStatus === 'delivered') {
                actionCell.innerHTML = `<a href="/admin/orders/${orderId}" class="btn btn-sm btn-info me-1" title="View Details"><i class="fas fa-eye me-1"></i>Details</a><span class="text-muted" style="font-size:.82rem;"><i class="fas fa-check-circle text-success me-1"></i>Done</span>`;
            } else if (newStatus === 'cancelled') {
                actionCell.innerHTML = `<a href="/admin/orders/${orderId}" class="btn btn-sm btn-info me-1" title="View Details"><i class="fas fa-eye me-1"></i>Details</a><span class="text-muted" style="font-size:.82rem;">Cancelled</span>`;
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
            
            if (row) {
                row.dataset.status = 'cancelled';
                row.querySelector('.badge-status')?.classList.remove('badge-pending', 'badge-approved', 'badge-assigned', 'badge-out_for_delivery', 'badge-delivered');
                row.querySelector('.badge-status')?.classList.add('badge-cancelled');
                if (row.querySelector('.badge-status')) {
                    row.querySelector('.badge-status').textContent = 'Cancelled';
                }
                row.querySelector('.order-total')?.classList.add('text-decoration-line-through', 'text-muted');
                const actionCell = row.querySelector('td:last-child');
                if (actionCell) {
                    actionCell.innerHTML = `<a href="/admin/orders/${orderId}" class="btn btn-sm btn-info me-1" title="View Details"><i class="fas fa-eye me-1"></i>Details</a><span class="text-muted" style="font-size:.82rem;">Cancelled</span>`;
                }
                updateTabCounts();
                filterOrders();
            }
            
            showOrderToast(payload.message || 'Order cancelled.');
        } catch (error) {
            showOrderToast('Unable to cancel order. Try again.', true);
        } finally {
            setButtonLoading(submitBtn, false);
            if (row) row.classList.remove('is-updating');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.cancel-order-form').forEach((form) => {
            form.addEventListener('submit', submitCancelOrder);
        });

        updateTabCounts();
        filterOrders();
    });
</script>
@endsection
