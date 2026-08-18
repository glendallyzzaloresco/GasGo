@extends('layouts.admin')

@section('title', 'Deliveries')
@section('nav-deliveries', 'active')
@section('page-title', 'Delivery Tracking')

@section('admin-styles')
<style>
    .filter-tabs { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:14px; }
    .filter-tab {
        padding:8px 18px; border-radius:25px; border:2px solid #e0e0e0;
        background:#fff; color:#666; font-size:.82rem; font-weight:600; cursor:pointer; transition:.3s;
    }
    .filter-tab:hover, .filter-tab.active { border-color:var(--gasgo-blue); background:var(--gasgo-blue); color:#fff; }
    .delivery-search { position:relative; max-width:360px; margin-bottom:14px; }
    .delivery-search input {
        border-radius:25px; padding:10px 20px 10px 42px; border:2px solid #e0e0e0;
        font-size:.88rem; width:100%; transition:border-color .3s;
    }
    .delivery-search input:focus { border-color:var(--gasgo-blue); outline:none; box-shadow:none; }
    .delivery-search i { position:absolute; left:16px; top:50%; transform:translateY(-50%); color:#aaa; }
    
    /* Delivery Cards and Status Colors */
    .delivery-item {
        background:#fff; border-radius:14px; padding:18px;
        box-shadow:0 2px 10px rgba(0,0,0,.05); transition:transform .2s;
    }
    .delivery-item:hover { transform:translateX(4px); }
    .delivery-item.active-delivery { background:var(--gasgo-orange-light); }
    .delivery-item.completed-delivery { background:#f0f9f5; }
    .delivery-item.returning-delivery { background:#fef3e6; }
    
    /* Status Badges - Specific Colors for Each Status */
    .badge-assigned { background:#3498db; color:#fff; padding:4px 10px; border-radius:12px; font-size:.75rem; font-weight:600; }
    .badge-picked_up { background:#2196F3; color:#fff; padding:4px 10px; border-radius:12px; font-size:.75rem; font-weight:600; }
    .badge-out_for_delivery { background:var(--gasgo-orange); color:#fff; padding:4px 10px; border-radius:12px; font-size:.75rem; font-weight:600; }
    .badge-returning { background:#9C27B0; color:#fff; padding:4px 10px; border-radius:12px; font-size:.75rem; font-weight:600; }
    .badge-delivered { background:#27ae60; color:#fff; padding:4px 10px; border-radius:12px; font-size:.75rem; font-weight:600; }
    .badge-standby { background:#5DADE2; color:#fff; padding:4px 10px; border-radius:12px; font-size:.75rem; font-weight:600; }
    .badge-failed { background:#e74c3c; color:#fff; padding:4px 10px; border-radius:12px; font-size:.75rem; font-weight:600; }
    
    .delivery-actions { margin-top:10px; display:flex; justify-content:flex-end; }
    .btn-delivery-progress {
        border:none; border-radius:10px; padding:7px 12px; font-size:.78rem; font-weight:600;
        background:var(--gasgo-blue); color:#fff; transition:.25s;
    }
    .btn-delivery-progress:hover { background:#145a8f; }
    .delivery-card.is-updating { opacity:.65; }
    
    /* Timeline Progress Bar - 3 Steps with Labels */
    .timeline-mini { display:flex; gap:4px; margin-top:8px; flex-direction:column; }
    .timeline-steps { display:flex; gap:4px; }
    .timeline-mini .step {
        flex:1; height:5px; border-radius:3px; background:#e0e0e0; position:relative;
    }
    .timeline-mini .step.step-1.done { background:var(--gasgo-orange); }
    .timeline-mini .step.step-1.current { background:var(--gasgo-orange); animation:pulse 1.5s infinite; }
    .timeline-mini .step.step-2.done { background:#27ae60; }
    .timeline-mini .step.step-2.current { background:#27ae60; animation:pulse 1.5s infinite; }
    .timeline-mini .step.step-3.done { background:#9C27B0; }
    .timeline-mini .step.step-3.current { background:#9C27B0; animation:pulse 1.5s infinite; }
    .timeline-labels { display:flex; gap:4px; margin-top:6px; font-size:.7rem; font-weight:500; color:#666; }
    .timeline-labels .label { flex:1; text-align:center; }
    @keyframes pulse { 0%,100%{opacity:1;} 50%{opacity:.6;} }
    
    /* Status Legend */
    .status-legend { margin-top:20px; padding:15px; background:#f5f5f5; border-radius:10px; font-size:.85rem; }
    .legend-item { display:inline-flex; align-items:center; gap:8px; margin-right:20px; }
    .legend-color { width:16px; height:16px; border-radius:3px; }

    @media (max-width: 768px) {
        .status-legend {
            padding: 10px 12px;
            font-size: 0.78rem;
            margin-bottom: 16px !important;
        }
        .legend-item {
            margin-right: 10px;
            margin-bottom: 6px;
            font-size: 0.74rem;
        }
        .delivery-item {
            padding: 12px 14px;
            border-radius: 14px;
        }
        .timeline-mini {
            margin-top: 10px;
        }
        .timeline-labels {
            font-size: 0.65rem;
        }
    }
</style>
@endsection

@php
    /* Delivery Status Workflow - 3 Step Process */
    $statusOrder = ['out_for_delivery', 'delivered', 'returning'];
    $statusLabels = [
        'pending' => 'Pending Assignment',
        'assigned' => 'Rider Assigned',
        'picked_up' => 'Picked Up',
        'out_for_delivery' => 'Out for Delivery',
        'delivered' => 'Order Delivered',
        'returning' => 'Returning to Store',
        'failed' => 'Delivery Failed',
        'cancelled' => 'Cancelled',
    ];
    $statusBadgeClasses = [
        'pending' => 'badge-assigned',
        'assigned' => 'badge-assigned',
        'picked_up' => 'badge-picked_up',
        'out_for_delivery' => 'badge-out_for_delivery',
        'delivered' => 'badge-delivered',
        'returning' => 'badge-returning',
        'failed' => 'badge-failed',
        'cancelled' => 'badge-failed',
    ];
    
    // Split deliveries into active and completed
    $activeDeliveries = $deliveries->filter(fn($d) => !in_array($d->status, ['delivered', 'failed', 'cancelled']));
    $completedDeliveries = $deliveries->filter(fn($d) => in_array($d->status, ['delivered', 'failed', 'cancelled']));
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-2">
    <div class="delivery-search">
        <i class="fas fa-search"></i>
        <input type="text" id="searchDeliveries" placeholder="Search by order, customer, rider, or address..." onkeyup="filterDeliveries()">
    </div>
    <div class="text-muted" style="font-size:.85rem;">
        Showing <strong id="deliveriesVisibleCount">{{ $deliveries->count() }}</strong> of <strong>{{ $deliveries->count() }}</strong> deliveries
    </div>
</div>

<div class="filter-tabs">
    <button class="filter-tab active" onclick="setDeliveryFilter(this, 'all')">All</button>
    <button class="filter-tab" onclick="setDeliveryFilter(this, 'active')">Active</button>
    <button class="filter-tab" onclick="setDeliveryFilter(this, 'completed')">Completed</button>
</div>

<!-- Status Legend - Moved to Top -->
<div class="status-legend" style="margin-bottom: 24px;">
    <strong style="display:block; margin-bottom:10px; color:#333;">Delivery Status Guide:</strong>
    <div class="legend-item">
        <div class="legend-color" style="background:var(--gasgo-orange);"></div>
        <span><strong>Out for Delivery (Step 1):</strong> Rider is delivering orders to customers</span>
    </div>
    <div class="legend-item">
        <div class="legend-color" style="background:#27ae60;"></div>
        <span><strong>Order Delivered (Step 2):</strong> Order successfully delivered to customer</span>
    </div>
    <div class="legend-item">
        <div class="legend-color" style="background:#9C27B0;"></div>
        <span><strong>Returning to Store (Step 3):</strong> Rider returning to store after all deliveries</span>
    </div>
</div>

<!-- Active Deliveries List -->
<div id="deliveriesContainer-active" class="deliveries-section">
    <h5 class="mb-3" style="color:#333; font-weight:600;">Active Deliveries</h5>
    <div id="activeDeliveriesList">
        @forelse($activeDeliveries as $delivery)
            <div class="delivery-item active-delivery delivery-card" id="delivery-{{ $delivery->id }}" data-id="{{ $delivery->id }}" data-status="{{ $delivery->status }}" data-group="active" data-search="order #{{ $delivery->order_id }} {{ strtolower($delivery->order->customer_name ?? $delivery->order->user->name ?? '') }} {{ strtolower($delivery->rider->name ?? '') }} {{ strtolower($delivery->order->delivery_address ?? '') }}">
                <div style="display:flex; justify-content:space-between; align-items:start;">
                    <div style="flex:1;">
                        <div style="margin-bottom:4px;">
                            <strong style="color:#333;">Order #{{ $delivery->order_id }}</strong>
                            <span class="badge {{ $statusBadgeClasses[$delivery->status] ?? 'badge-assigned' }}" style="margin-left:8px;">{{ $statusLabels[$delivery->status] ?? ucwords(str_replace('_', ' ', $delivery->status ?: ($delivery->order->status ?? 'Pending'))) }}</span>
                        </div>
                        <div style="font-size:.82rem; color:#666; margin-bottom:2px;">
                            <strong>Customer:</strong> {{ $delivery->order->customer_name ?? $delivery->order->user->name ?? 'N/A' }}
                        </div>
                        <div style="font-size:.82rem; color:#666; margin-bottom:2px;">
                            <strong>Rider:</strong> {{ $delivery->rider->name ?? 'Unassigned' }}
                        </div>
                        <div style="font-size:.82rem; color:#666; margin-bottom:2px;">
                            <strong>Address:</strong> {{ $delivery->order->delivery_address ?? 'No address provided' }}
                        </div>
                    </div>
                </div>
                <div class="timeline-mini">
                    <div class="timeline-steps">
                        @foreach($statusOrder as $i => $status)
                            @php
                                $currentIndex = array_search($delivery->status, $statusOrder);
                                $isDone = ($currentIndex !== false) && ($i <= $currentIndex);
                                $isCurrent = ($currentIndex !== false) && ($i === $currentIndex);
                                $stepNumber = $i + 1;
                            @endphp
                            <div class="step step-{{ $stepNumber }} {{ $isDone ? 'done' : '' }} {{ $isCurrent ? 'current' : '' }}"></div>
                        @endforeach
                    </div>
                    <div class="timeline-labels">
                        <div class="label">Out for Delivery</div>
                        <div class="label">Delivered</div>
                        <div class="label">Returned</div>
                    </div>
                </div>
            </div>
        @empty
            <div style="padding:20px; text-align:center; color:#aaa;">
                <i class="fas fa-box" style="font-size:2rem; display:block; margin-bottom:8px;"></i>
                No active deliveries
            </div>
        @endforelse
    </div>
</div>

<!-- Completed Deliveries List -->
<div id="deliveriesContainer-completed" class="deliveries-section" style="display:none;">
    <h5 class="mb-3" style="color:#333; font-weight:600;">Completed Deliveries</h5>
    <div id="completedDeliveriesList">
        @forelse($completedDeliveries as $delivery)
            <div class="delivery-item completed-delivery delivery-card" id="delivery-{{ $delivery->id }}" data-id="{{ $delivery->id }}" data-status="{{ $delivery->status }}" data-group="completed" data-search="order #{{ $delivery->order_id }} {{ strtolower($delivery->order->customer_name ?? $delivery->order->user->name ?? '') }} {{ strtolower($delivery->rider->name ?? '') }} {{ strtolower($delivery->order->delivery_address ?? '') }}">
                <div style="display:flex; justify-content:space-between; align-items:start;">
                    <div style="flex:1;">
                        <div style="margin-bottom:4px;">
                            <strong style="color:#333;">Order #{{ $delivery->order_id }}</strong>
                            <span class="badge {{ $statusBadgeClasses[$delivery->status] ?? 'badge-delivered' }}" style="margin-left:8px;">{{ $statusLabels[$delivery->status] ?? ucwords(str_replace('_', ' ', $delivery->status ?: ($delivery->order->status ?? 'Completed'))) }}</span>
                        </div>
                        <div style="font-size:.82rem; color:#666; margin-bottom:2px;">
                            <strong>Customer:</strong> {{ $delivery->order->customer_name ?? $delivery->order->user->name ?? 'N/A' }}
                        </div>
                        <div style="font-size:.82rem; color:#666; margin-bottom:2px;">
                            <strong>Rider:</strong> {{ $delivery->rider->name ?? 'System' }}
                        </div>
                        <div style="font-size:.82rem; color:#666; margin-bottom:2px;">
                            <strong>Address:</strong> {{ $delivery->order->delivery_address ?? 'No address provided' }}
                        </div>
                    </div>
                </div>
                <div class="timeline-mini">
                    <div class="timeline-steps">
                        @foreach($statusOrder as $i => $status)
                            @php
                                $stepNumber = $i + 1;
                            @endphp
                            <div class="step step-{{ $stepNumber }} done"></div>
                        @endforeach
                    </div>
                    <div class="timeline-labels">
                        <div class="label">Out for Delivery</div>
                        <div class="label">Delivered</div>
                        <div class="label">Returned</div>
                    </div>
                </div>
            </div>
        @empty
            <div style="padding:20px; text-align:center; color:#aaa;">
                <i class="fas fa-check-circle" style="font-size:2rem; display:block; margin-bottom:8px;"></i>
                No completed deliveries
            </div>
        @endforelse
    </div>
</div>

<!-- All Deliveries List (Active + Completed combined) -->
<div id="deliveriesContainer-all" class="deliveries-section">
    <h5 class="mb-3" style="color:#333; font-weight:600;">All Deliveries</h5>
    <div id="allDeliveriesList">
        @forelse($deliveries as $delivery)
            <div class="delivery-item delivery-card" id="delivery-{{ $delivery->id }}" data-id="{{ $delivery->id }}" data-status="{{ $delivery->status }}" data-group="all" data-search="order #{{ $delivery->order_id }} {{ strtolower($delivery->order->customer_name ?? $delivery->order->user->name ?? '') }} {{ strtolower($delivery->rider->name ?? '') }} {{ strtolower($delivery->order->delivery_address ?? '') }}">
                <div style="display:flex; justify-content:space-between; align-items:start;">
                    <div style="flex:1;">
                        <div style="margin-bottom:4px;">
                            <strong style="color:#333;">Order #{{ $delivery->order_id }}</strong>
                            <span class="badge {{ $statusBadgeClasses[$delivery->status] ?? 'badge-assigned' }}" style="margin-left:8px;">{{ $statusLabels[$delivery->status] ?? ucwords(str_replace('_', ' ', $delivery->status ?: ($delivery->order->status ?? 'Pending'))) }}</span>
                        </div>
                        <div style="font-size:.82rem; color:#666; margin-bottom:2px;">
                            <strong>Customer:</strong> {{ $delivery->order->customer_name ?? $delivery->order->user->name ?? 'N/A' }}
                        </div>
                        <div style="font-size:.82rem; color:#666; margin-bottom:2px;">
                            <strong>Rider:</strong> {{ $delivery->rider->name ?? 'Unassigned' }}
                        </div>
                        <div style="font-size:.82rem; color:#666; margin-bottom:2px;">
                            <strong>Address:</strong> {{ $delivery->order->delivery_address ?? 'No address provided' }}
                        </div>
                    </div>
                </div>
                <div class="timeline-mini">
                    <div class="timeline-steps">
                        @foreach($statusOrder as $i => $status)
                            @php
                                $currentIndex = array_search($delivery->status, $statusOrder);
                                $isDone = ($currentIndex !== false) && ($i <= $currentIndex);
                                $isCurrent = ($currentIndex !== false) && ($i === $currentIndex);
                                $stepNumber = $i + 1;
                            @endphp
                            <div class="step step-{{ $stepNumber }} {{ $isDone ? 'done' : '' }} {{ $isCurrent ? 'current' : '' }}"></div>
                        @endforeach
                    </div>
                    <div class="timeline-labels">
                        <div class="label">Out for Delivery</div>
                        <div class="label">Delivered</div>
                        <div class="label">Returned</div>
                    </div>
                </div>
            </div>
        @empty
            <div style="padding:20px; text-align:center; color:#aaa;">
                <i class="fas fa-inbox" style="font-size:2rem; display:block; margin-bottom:8px;"></i>
                No deliveries
            </div>
        @endforelse
    </div>
</div>

@endsection

@section('scripts')
<script>
    let currentDeliveryFilter = 'all';
    const deliveryStatusOrder = ['out_for_delivery', 'delivered', 'returning'];
    const deliveryStatusLabels = {
        pending: 'Pending Assignment',
        assigned: 'Rider Assigned',
        picked_up: 'Picked Up',
        out_for_delivery: 'Out for Delivery',
        delivered: 'Order Delivered',
        returning: 'Returning to Store',
        failed: 'Delivery Failed',
        cancelled: 'Cancelled',
    };
    const deliveryStatusBadgeClass = {
        pending: 'badge-assigned',
        assigned: 'badge-assigned',
        picked_up: 'badge-picked_up',
        out_for_delivery: 'badge-out_for_delivery',
        delivered: 'badge-delivered',
        returning: 'badge-returning',
        failed: 'badge-failed',
        cancelled: 'badge-failed',
    };

    function showDeliveryToast(message, isError = false) {
        if (typeof window.showAdminToast === 'function') {
            window.showAdminToast(message, isError);
            return;
        }
        alert(message);
    }

    function setProgressButtonLoading(button, isLoading) {
        if (!button) return;
        if (isLoading) {
            button.dataset.originalLabel = button.textContent;
            button.disabled = true;
            button.classList.add('btn-loading');
            button.innerHTML = '<span class="btn-label"></span><span class="btn-spinner"><i class="fas fa-circle-notch fa-spin me-1"></i>Updating...</span>';
        } else {
            button.disabled = false;
            button.classList.remove('btn-loading');
            if (button.dataset.originalLabel) {
                button.textContent = button.dataset.originalLabel;
            }
        }
    }

    function updateDeliveryCounts() {
        const activeCount = document.querySelectorAll('#activeDeliveriesList .delivery-card').length;
        const completedCount = document.querySelectorAll('#completedDeliveriesList .delivery-card').length;
        document.getElementById('activeDeliveriesCount').textContent = activeCount;
        document.getElementById('completedDeliveriesCount').textContent = completedCount;
    }

    function updateTimelineSteps(card, status) {
        const timeline = card.querySelector('.timeline-mini');
        if (!timeline) return;

        const statusIndex = deliveryStatusOrder.indexOf(status);
        timeline.querySelectorAll('.step').forEach((step, idx) => {
            step.classList.remove('done', 'current');
            if (idx < (statusIndex + 1)) {
                step.classList.add('done');
            }
            if (idx === statusIndex) {
                step.classList.add('current');
            }
        });
    }

    function updateProgressButton(card, status) {
        const nextMap = {
            assigned: 'picked_up',
            picked_up: 'out_for_delivery',
            out_for_delivery: 'delivered',
        };
        const nextStatus = nextMap[status] || null;
        const wrap = card.querySelector('.delivery-actions');

        if (!nextStatus) {
            if (wrap) wrap.remove();
            return;
        }

        if (!wrap) {
            const div = document.createElement('div');
            div.className = 'delivery-actions';
            div.innerHTML = `<button type="button" class="btn-delivery-progress" data-delivery-id="${card.dataset.deliveryId}" data-next-status="${nextStatus}">Move to ${deliveryStatusLabels[nextStatus]}</button>`;
            card.appendChild(div);
        } else {
            const btn = wrap.querySelector('.btn-delivery-progress');
            btn.dataset.nextStatus = nextStatus;
            btn.textContent = `Move to ${deliveryStatusLabels[nextStatus]}`;
        }
    }

    function updateDeliveryCard(card, status, deliveredAt = null) {
        card.dataset.deliveryStatus = status;
        const badge = card.querySelector('.delivery-status-badge');
        if (badge) {
            badge.className = `badge-status delivery-status-badge ${deliveryStatusBadgeClass[status] || 'badge-assigned'}`;
            badge.textContent = deliveryStatusLabels[status] || status;
        }

        updateTimelineSteps(card, status);

        const isCompleted = status === 'delivered' || status === 'failed';
        card.dataset.group = isCompleted ? 'completed' : 'active';
        card.classList.toggle('active-delivery', !isCompleted);
        card.classList.toggle('completed-delivery', isCompleted);

        if (isCompleted) {
            document.getElementById('completedDeliveriesList').prepend(card);
            const dateTimeEl = card.querySelector('.delivery-date-time');
            if (dateTimeEl && deliveredAt) {
                dateTimeEl.textContent = deliveredAt;
            }
        } else {
            document.getElementById('activeDeliveriesList').prepend(card);
        }

        updateProgressButton(card, status);
        updateDeliveryCounts();
        filterDeliveries();
    }

    async function advanceDeliveryStatus(button) {
        const deliveryId = button.dataset.deliveryId;
        const nextStatus = button.dataset.nextStatus;
        if (!deliveryId || !nextStatus) return;
        const card = button.closest('.delivery-card');
        if (card) card.classList.add('is-updating');
        setProgressButtonLoading(button, true);

        try {
            const response = await fetch(`{{ url('/admin/deliveries') }}/${deliveryId}/status`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: new URLSearchParams({
                    _method: 'PUT',
                    status: nextStatus,
                }),
            });

            if (!response.ok) {
                throw new Error('Failed to update delivery status.');
            }

            const payload = await response.json();
            const latestCard = document.querySelector(`.delivery-card[data-delivery-id="${payload.delivery_id}"]`);
            if (latestCard) {
                updateDeliveryCard(latestCard, payload.status, payload.delivered_at);
            }

            showDeliveryToast(payload.message || 'Delivery updated successfully.');
        } catch (error) {
            showDeliveryToast('Unable to update delivery status right now.', true);
        } finally {
            if (card) card.classList.remove('is-updating');
            setProgressButtonLoading(button, false);
        }
    }

    function setDeliveryFilter(btn, status) {
        document.querySelectorAll('.filter-tab').forEach(tab => tab.classList.remove('active'));
        btn.classList.add('active');
        currentDeliveryFilter = status;
        
        // Hide all containers first
        document.getElementById('deliveriesContainer-all').style.display = 'none';
        document.getElementById('deliveriesContainer-active').style.display = 'none';
        document.getElementById('deliveriesContainer-completed').style.display = 'none';
        
        // Show the appropriate container
        if (status === 'all') {
            document.getElementById('deliveriesContainer-all').style.display = '';
        } else if (status === 'active') {
            document.getElementById('deliveriesContainer-active').style.display = '';
        } else if (status === 'completed') {
            document.getElementById('deliveriesContainer-completed').style.display = '';
        }
        
        filterDeliveries();
    }

    function filterDeliveries() {
        const query = document.getElementById('searchDeliveries').value.toLowerCase();
        let visible = 0;

        document.querySelectorAll('.delivery-card').forEach(card => {
            const matchGroup = currentDeliveryFilter === 'all' || card.dataset.group === currentDeliveryFilter;
            const matchText = !query || card.dataset.search.includes(query);
            const show = matchGroup && matchText;
            card.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        document.getElementById('deliveriesVisibleCount').textContent = visible;
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.delivery-card').forEach((card) => {
            const badge = card.querySelector('.delivery-status-badge');
            if (badge) {
                const statusText = badge.textContent.trim().toLowerCase().replaceAll(' ', '_');
                card.dataset.deliveryStatus = statusText;
            }

            const button = card.querySelector('.btn-delivery-progress');
            if (button) {
                const id = button.dataset.deliveryId;
                card.dataset.deliveryId = id;
            }
        });

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-delivery-progress');
            if (btn) {
                advanceDeliveryStatus(btn);
            }
        });

        updateDeliveryCounts();
        filterDeliveries();

        // Start automatic refresh for real-time updates
        startDeliveryPolling();
    });

    // Automatic polling to refresh deliveries every 5 seconds
    let pollingInterval = null;
    function startDeliveryPolling() {
        // Poll every 5 seconds for real-time delivery updates
        pollingInterval = setInterval(() => {
            refreshDeliveriesData();
        }, 5000);
    }

    function stopDeliveryPolling() {
        if (pollingInterval) {
            clearInterval(pollingInterval);
            pollingInterval = null;
        }
    }

    async function refreshDeliveriesData() {
        try {
            const response = await fetch('{{ route("admin.deliveries.api") }}', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) return;

            const data = await response.json();
            const deliveries = data.deliveries || [];

            // Update each delivery card
            deliveries.forEach(delivery => {
                const card = document.querySelector(`.delivery-card[data-id="${delivery.id}"]`);
                if (card) {
                    const currentStatus = card.dataset.status;
                    
                    // If status has changed, update the card
                    if (currentStatus !== delivery.status) {
                        card.dataset.status = delivery.status;
                        
                        // Update status badge
                        const statusMap = {
                            'pending': 'Pending Assignment',
                            'assigned': 'Rider Assigned',
                            'picked_up': 'Picked Up',
                            'out_for_delivery': 'Out for Delivery',
                            'delivered': 'Order Delivered',
                            'returning': 'Returning to Store',
                            'failed': 'Delivery Failed',
                            'cancelled': 'Cancelled',
                        };
                        
                        const badge = card.querySelector('.badge');
                        if (badge) {
                            badge.textContent = statusMap[delivery.status] || (delivery.status ? delivery.status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'Pending');
                            // Update badge class
                            badge.className = 'badge badge-' + (delivery.status ? delivery.status.replace(/_/g, '-') : 'assigned');
                        }
                        
                        // Update timeline
                        const steps = card.querySelectorAll('.step');
                        const statusOrder = ['out_for_delivery', 'delivered', 'returning'];
                        const currentIndex = statusOrder.indexOf(delivery.status);
                        
                        steps.forEach((step, i) => {
                            step.classList.remove('done', 'current');
                            if (currentIndex !== -1) {
                                if (i < currentIndex) step.classList.add('done');
                                else if (i === currentIndex) step.classList.add('current');
                            }
                        });
                        
                        // Move card to appropriate section if status is delivered/failed
                        if (delivery.status === 'delivered' || delivery.status === 'failed') {
                            const wasInActive = card.parentElement.id === 'activeDeliveriesList';
                            if (wasInActive) {
                                // Move to completed list
                                const completedList = document.getElementById('completedDeliveriesList');
                                card.style.animation = 'slideOut 0.3s ease-out';
                                setTimeout(() => {
                                    card.remove();
                                    completedList.insertAdjacentElement('afterbegin', card);
                                    card.dataset.group = 'completed';
                                    filterDeliveries();
                                    updateDeliveryCounts();
                                }, 300);
                            }
                        }
                    }
                }
            });
        } catch (error) {
            console.error('Error refreshing deliveries:', error);
        }
    }

    // Add slide out animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideOut {
            0% { opacity: 1; transform: translateX(0); }
            100% { opacity: 0; transform: translateX(-100%); }
        }
    `;
    document.head.appendChild(style);

</script>
@endsection
