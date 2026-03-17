@extends('layouts.admin')

@section('title', 'GasGo Admin - Deliveries')
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
    .map-placeholder {
        width:100%; height:350px; border-radius:16px; overflow:hidden;
        background:linear-gradient(135deg,var(--gasgo-blue-light),#d5e8f7);
        display:flex; align-items:center; justify-content:center; position:relative;
        box-shadow:0 4px 15px rgba(0,0,0,.06);
    }
    .map-placeholder .map-text {
        text-align:center; color:var(--gasgo-blue); z-index:2;
    }
    .map-placeholder .map-text i { font-size:3rem; margin-bottom:10px; display:block; }
    .delivery-item {
        background:#fff; border-radius:14px; padding:18px;
        box-shadow:0 2px 10px rgba(0,0,0,.05); transition:transform .2s; cursor:pointer;
        border-left:4px solid transparent;
    }
    .delivery-item:hover { transform:translateX(4px); }
    .delivery-item.active-delivery { border-left-color:var(--gasgo-orange); background:var(--gasgo-orange-light); }
    .delivery-item.completed-delivery { border-left-color:#27ae60; }
    .delivery-actions { margin-top:10px; display:flex; justify-content:flex-end; }
    .btn-delivery-progress {
        border:none; border-radius:10px; padding:7px 12px; font-size:.78rem; font-weight:600;
        background:var(--gasgo-blue); color:#fff; transition:.25s;
    }
    .btn-delivery-progress:hover { background:#145a8f; }
    .delivery-card.is-updating { opacity:.65; }
    .timeline-mini { display:flex; gap:4px; margin-top:8px; }
    .timeline-mini .step {
        flex:1; height:4px; border-radius:2px; background:#e0e0e0;
    }
    .timeline-mini .step.done { background:var(--gasgo-orange); }
    .timeline-mini .step.current { background:var(--gasgo-blue); animation:pulse 1.5s infinite; }
    @keyframes pulse { 0%,100%{opacity:1;} 50%{opacity:.5;} }
</style>
@endsection

@php
    $statusOrder = ['assigned', 'picked_up', 'out_for_delivery', 'delivered'];
    $statusLabels = [
        'assigned' => 'Assigned',
        'picked_up' => 'Picked Up',
        'out_for_delivery' => 'Out for Delivery',
        'delivered' => 'Delivered',
        'failed' => 'Failed'
    ];
    $statusBadgeClasses = [
        'assigned' => 'badge-assigned',
        'picked_up' => 'badge-assigned',
        'out_for_delivery' => 'badge-out_for_delivery',
        'delivered' => 'badge-delivered',
        'failed' => 'badge-cancelled'
    ];
    
    // Split deliveries into active and completed
    $activeDeliveries = $deliveries->filter(fn($d) => !in_array($d->status, ['delivered', 'failed']));
    $completedDeliveries = $deliveries->filter(fn($d) => in_array($d->status, ['delivered', 'failed']));
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

<!-- Map Section -->
<div class="map-placeholder mb-4">
    <div class="map-text">
        <i class="fas fa-map-marked-alt"></i>
        <h5 class="fw-bold">Live Delivery Map</h5>
        <p style="font-size:.88rem;">Integrate with Google Maps or Leaflet.js for real-time tracking</p>
    </div>
</div>

<div class="row g-4">
    <!-- Active Deliveries -->
    <div class="col-lg-6">
        <h6 class="fw-bold mb-3" style="color:var(--gasgo-blue);"><i class="fas fa-shipping-fast me-2" style="color:var(--gasgo-orange);"></i>Active Deliveries <span class="badge bg-warning text-dark ms-1" id="activeDeliveriesCount">{{ $activeDeliveries->count() }}</span></h6>
        <div class="d-flex flex-column gap-3" id="activeDeliveriesList">
            @forelse($activeDeliveries as $delivery)
                @php
                    $order = $delivery->order;
                    $statusIndex = array_search($delivery->status, $statusOrder);
                    $isActive = !in_array($delivery->status, ['delivered', 'failed']);
                    $nextStatus = null;
                    if ($delivery->status === 'assigned') $nextStatus = 'picked_up';
                    elseif ($delivery->status === 'picked_up') $nextStatus = 'out_for_delivery';
                    elseif ($delivery->status === 'out_for_delivery') $nextStatus = 'delivered';
                @endphp
                <div class="delivery-item {{ $isActive ? 'active-delivery' : '' }} delivery-card" data-delivery-id="{{ $delivery->id }}" data-group="active" data-search="{{ strtolower(($order->order_number ?? '') . ' ' . ($order->user->name ?? '') . ' ' . ($delivery->rider->name ?? '') . ' ' . ($order->delivery_address ?? '')) }}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-bold" style="color:var(--gasgo-blue);">#{{ $order->order_number ?? 'N/A' }}</div>
                            <div style="font-size:.85rem;">{{ $order->user->name ?? 'Unknown' }} &bullet; 
                                @if($order->orderItems)
                                    @foreach($order->orderItems as $item)
                                        {{ $item->product_name ?? 'Product' }} ×{{ $item->quantity }}{{ !$loop->last ? ', ' : '' }}
                                    @endforeach
                                @endif
                            </div>
                            <div style="font-size:.8rem;color:#888;"><i class="fas fa-map-marker-alt me-1"></i>{{ $order->delivery_address ?? 'Address not provided' }}</div>
                        </div>
                        <div class="text-end">
                            <span class="badge-status delivery-status-badge {{ $statusBadgeClasses[$delivery->status] ?? 'badge-assigned' }}">{{ $statusLabels[$delivery->status] ?? $delivery->status }}</span>
                            <div style="font-size:.78rem;color:#888;margin-top:4px;"><i class="fas fa-motorcycle me-1"></i>{{ $delivery->rider->name ?? 'Unassigned' }}</div>
                        </div>
                    </div>
                    <div class="timeline-mini" data-delivery-id="{{ $delivery->id }}">
                        @foreach($statusOrder as $idx => $status)
                            <div class="step {{ $idx < ($statusIndex + 1) ? 'done' : '' }} {{ $idx == $statusIndex ? 'current' : '' }}"></div>
                        @endforeach
                    </div>
                    @if($nextStatus)
                        <div class="delivery-actions">
                            <button
                                type="button"
                                class="btn-delivery-progress"
                                data-delivery-id="{{ $delivery->id }}"
                                data-next-status="{{ $nextStatus }}">
                                Move to {{ $statusLabels[$nextStatus] ?? ucfirst($nextStatus) }}
                            </button>
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-inbox fa-2x mb-2" style="color:#ddd;"></i>
                    <p>No active deliveries at the moment.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Recent Completed -->
    <div class="col-lg-6">
        <h6 class="fw-bold mb-3" style="color:var(--gasgo-blue);"><i class="fas fa-check-circle me-2 text-success"></i>Recently Completed <span class="badge bg-success ms-1" id="completedDeliveriesCount">{{ $completedDeliveries->count() }}</span></h6>
        <div class="d-flex flex-column gap-3" id="completedDeliveriesList">
            @forelse($completedDeliveries->take(5) as $delivery)
                @php
                    $order = $delivery->order;
                    $deliveryTime = $delivery->delivered_at ? $delivery->delivered_at->diffForHumans() : 'N/A';
                @endphp
                <div class="delivery-item completed-delivery delivery-card" data-delivery-id="{{ $delivery->id }}" data-group="completed" data-search="{{ strtolower(($order->order_number ?? '') . ' ' . ($order->user->name ?? '') . ' ' . ($delivery->rider->name ?? '') . ' ' . ($order->delivery_address ?? '')) }}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-bold" style="color:var(--gasgo-blue);">#{{ $order->order_number ?? 'N/A' }}</div>
                            <div style="font-size:.85rem;">{{ $order->user->name ?? 'Unknown' }} &bullet; 
                                @if($order->orderItems)
                                    @foreach($order->orderItems as $item)
                                        {{ $item->product_name ?? 'Product' }} ×{{ $item->quantity }}{{ !$loop->last ? ', ' : '' }}
                                    @endforeach
                                @endif
                            </div>
                            <div class="delivery-meta-time" style="font-size:.8rem;color:#888;"><i class="fas fa-motorcycle me-1"></i>{{ $delivery->rider->name ?? 'Unknown' }} &bullet; {{ $deliveryTime }}</div>
                        </div>
                        <div class="text-end">
                            <span class="badge-status delivery-status-badge badge-{{ $delivery->status }}">{{ $statusLabels[$delivery->status] ?? ucfirst($delivery->status) }}</span>
                            <div class="delivery-date-time" style="font-size:.78rem;color:#888;margin-top:4px;">{{ $delivery->delivered_at ? $delivery->delivered_at->format('M d g:i A') : 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="timeline-mini">
                        <div class="step done"></div>
                        <div class="step done"></div>
                        <div class="step done"></div>
                        <div class="step done"></div>
                        <div class="step done"></div>
                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-inbox fa-2x mb-2" style="color:#ddd;"></i>
                    <p>No completed deliveries yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let currentDeliveryFilter = 'all';
    const deliveryStatusOrder = ['assigned', 'picked_up', 'out_for_delivery', 'delivered'];
    const deliveryStatusLabels = {
        assigned: 'Assigned',
        picked_up: 'Picked Up',
        out_for_delivery: 'Out for Delivery',
        delivered: 'Delivered',
        failed: 'Failed',
    };
    const deliveryStatusBadgeClass = {
        assigned: 'badge-assigned',
        picked_up: 'badge-assigned',
        out_for_delivery: 'badge-out_for_delivery',
        delivered: 'badge-delivered',
        failed: 'badge-cancelled',
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
    });
</script>
@endsection
