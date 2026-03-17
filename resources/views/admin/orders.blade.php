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
    .order-row { cursor:pointer; transition:background .2s; }
    .order-row:hover { background:var(--gasgo-blue-light) !important; }
    .search-box { position:relative; max-width:320px; }
    .search-box input {
        border-radius:25px; padding:10px 20px 10px 42px; border:2px solid #e0e0e0;
        font-size:.88rem; width:100%; transition:border-color .3s;
    }
    .search-box input:focus { border-color:var(--gasgo-blue); outline:none; box-shadow:none; }
    .search-box i { position:absolute; left:16px; top:50%; transform:translateY(-50%); color:#aaa; }
    .order-row.is-updating { opacity:.62; }
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
                        ₱{{ number_format($order->total_amount, 2) }}
                    </td>
                    <td>
                        <span class="badge {{ $order->payment_method === 'gcash' ? 'bg-success' : 'bg-secondary' }}" style="font-size:.72rem;">
                            {{ ucfirst($order->payment_method ?? 'N/A') }}
                        </span>
                    </td>
                    <td><span class="badge-status badge-{{ $order->status }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span></td>
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
                        @if($order->status === 'pending')
                            <div class="d-flex gap-1">
                                <button class="btn btn-sm btn-action assign-btn" style="background:var(--gasgo-orange);color:#fff;" title="Assign Rider"
                                    data-order-id="{{ $order->id }}" data-order-number="{{ $order->order_number }}">
                                    <i class="fas fa-motorcycle me-1"></i>Assign
                                </button>
                                <form action="{{ route('admin.orders.status', $order) }}" method="POST" class="cancel-order-form" data-order-id="{{ $order->id }}" onsubmit="return confirm('Cancel this order?')">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="status" value="cancelled">
                                    <button class="btn btn-sm btn-action" style="background:#dc3545;color:#fff;" title="Cancel"><i class="fas fa-times"></i></button>
                                </form>
                            </div>
                        @elseif($order->status === 'approved')
                            <button class="btn btn-sm btn-action assign-btn" style="background:var(--gasgo-orange);color:#fff;" title="Assign Rider"
                                data-order-id="{{ $order->id }}" data-order-number="{{ $order->order_number }}">
                                <i class="fas fa-motorcycle me-1"></i>Assign
                            </button>
                        @elseif(in_array($order->status, ['assigned', 'out_for_delivery']))
                            <span class="text-muted" style="font-size:.82rem;">
                                <i class="fas fa-motorcycle text-info me-1"></i>
                                {{ $order->delivery->rider->name ?? 'Rider' }}
                            </span>
                        @elseif($order->status === 'delivered')
                            <span class="text-muted" style="font-size:.82rem;"><i class="fas fa-check-circle text-success me-1"></i>Done</span>
                        @elseif($order->status === 'cancelled')
                            <span class="text-muted" style="font-size:.82rem;">Cancelled</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">No orders found.</td>
                </tr>
            @endforelse
            <tr id="ordersNoResults" style="display:none;">
                <td colspan="8" class="text-center text-muted py-4">
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
                                    @else
                                        <span class="badge bg-warning text-dark ms-auto">Busy</span>
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
            row.style.display = visible ? '' : 'none';

            if (visible) {
                visibleCount++;
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
            updateOrderRowStatus(orderId, payload.status || 'assigned', payload.rider_name || riderName);
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
            updateOrderRowStatus(orderId, payload.status || 'cancelled');
            showOrderToast(payload.message || 'Order cancelled.');
        } catch (error) {
            showOrderToast('Unable to cancel order. Try again.', true);
        } finally {
            setButtonLoading(submitBtn, false);
            if (row) row.classList.remove('is-updating');
        }
    }

    // Event listener for assign buttons
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.assign-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const orderId = this.dataset.orderId;
                const orderNumber = this.dataset.orderNumber;
                openAssignModal(orderId, orderNumber);
            });
        });

        document.getElementById('assignRiderForm')?.addEventListener('submit', submitAssignRider);

        document.querySelectorAll('.cancel-order-form').forEach((form) => {
            form.addEventListener('submit', submitCancelOrder);
        });

        updateTabCounts();

        filterOrders();
    });
</script>
@endsection
