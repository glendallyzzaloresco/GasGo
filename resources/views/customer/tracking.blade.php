@extends('layouts.customer')

@section('title', 'GasGo - Track Order #{{ $order->order_number }}')

@section('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, var(--gasgo-blue) 0%, #2196f3 100%);
        color: white; padding: 50px 0 60px; margin-bottom: -30px; position: relative;
    }
    .page-header::after {
        content: ''; position: absolute; bottom: -2px; left: 0; right: 0; height: 60px;
        background: #f8f9fa; clip-path: ellipse(55% 100% at 50% 100%);
    }

    .tracking-card {
        background: white; border-radius: 20px; padding: 30px;
        box-shadow: 0 8px 30px rgba(0,0,0,.08);
    }

    /* Status Timeline */
    .status-timeline { position: relative; padding: 0; list-style: none; margin: 0; }
    .status-timeline::before {
        content: ''; position: absolute; left: 22px; top: 0; bottom: 0;
        width: 3px; background: #eee;
    }
    .timeline-step { display: flex; align-items: flex-start; gap: 18px; padding: 18px 0; position: relative; }
    .timeline-dot {
        width: 44px; height: 44px; border-radius: 50%; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem; color: white; background: #ddd; z-index: 1; transition: all .3s;
    }
    .timeline-step.active .timeline-dot {
        background: var(--gasgo-orange);
        box-shadow: 0 4px 14px rgba(247,148,29,.4);
        animation: pulseActive 2s infinite;
    }
    .timeline-step.done .timeline-dot { background: #27ae60; }
    .timeline-step.cancelled .timeline-dot { background: #e74c3c; }
    .timeline-info h6 { font-weight: 700; margin-bottom: 2px; }
    .timeline-info p { margin: 0; font-size: .85rem; color: #888; }
    .timeline-info .time-label { font-size: .78rem; color: var(--gasgo-orange); font-weight: 600; margin-top: 2px; }

    @keyframes pulseActive {
        0%, 100% { box-shadow: 0 4px 14px rgba(247,148,29,.4); }
        50% { box-shadow: 0 4px 24px rgba(247,148,29,.7); }
    }

    /* Live indicator */
    .live-dot {
        width: 10px; height: 10px; background: #27ae60; border-radius: 50%;
        display: inline-block; margin-right: 6px; animation: blink 1.5s infinite;
    }
    @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: .3; }
    }

    /* Map */
    .map-container {
        border-radius: 16px; height: 380px; overflow: hidden; position: relative;
        background: var(--gasgo-blue-light);
    }
    #trackingMap { width: 100%; height: 100%; }
    .map-overlay {
        position: absolute; top: 0; left: 0; right: 0; bottom: 0;
        display: flex; align-items: center; justify-content: center; flex-direction: column;
        color: var(--gasgo-blue); z-index: 10; background: var(--gasgo-blue-light);
    }
    .map-overlay i { font-size: 3rem; margin-bottom: 10px; }
    .map-overlay.hidden { display: none; }

    /* Rider Card */
    .rider-card {
        background: white; border-radius: 16px; padding: 20px;
        box-shadow: 0 4px 20px rgba(0,0,0,.06); display: flex; align-items: center; gap: 16px;
    }
    .rider-avatar {
        width: 56px; height: 56px; border-radius: 50%;
        background: linear-gradient(135deg, var(--gasgo-blue), #2196f3);
        color: white; display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem; font-weight: 700;
    }

    /* Order Items */
    .order-item-mini { display: flex; gap: 10px; padding: 8px 0; border-bottom: 1px solid #f8f8f8; }
    .order-item-mini img { width: 42px; height: 42px; border-radius: 8px; object-fit: contain; background: #fff; }
    .order-item-mini .name { font-weight: 600; font-size: .82rem; color: #333; }
    .order-item-mini .qty { font-size: .76rem; color: #888; }

    /* Status badge large */
    .status-badge-lg {
        display: inline-flex; align-items: center; gap: 8px; padding: 10px 22px;
        border-radius: 30px; font-weight: 700; font-size: .9rem;
    }
    .status-pending { background: #fff3cd; color: #856404; }
    .status-approved { background: #d1ecf1; color: #0c5460; }
    .status-assigned { background: #e8f4fc; color: #1a6db0; }
    .status-out_for_delivery { background: #fff5e6; color: #e07d0a; }
    .status-delivered { background: #d4edda; color: #155724; }
    .status-cancelled { background: #f8d7da; color: #721c24; }
</style>
@endsection

@section('content')
@php
    $statusOrder = ['pending', 'approved', 'assigned', 'out_for_delivery', 'delivered'];
    $currentIndex = array_search($order->status, $statusOrder);
    if ($currentIndex === false) $currentIndex = -1; // cancelled

    $steps = [
        ['key' => 'pending',             'icon' => 'fas fa-clipboard-check', 'title' => 'Order Placed',       'desc' => 'Your order has been received'],
        ['key' => 'approved',            'icon' => 'fas fa-thumbs-up',       'title' => 'Order Approved',     'desc' => 'Your order has been confirmed'],
        ['key' => 'assigned',            'icon' => 'fas fa-user-check',      'title' => 'Rider Assigned',     'desc' => 'A rider has been assigned'],
        ['key' => 'out_for_delivery',    'icon' => 'fas fa-truck',           'title' => 'Out for Delivery',   'desc' => 'Your order is on its way'],
        ['key' => 'delivered',           'icon' => 'fas fa-check-circle',    'title' => 'Delivered',          'desc' => 'Order delivered successfully'],
    ];
@endphp

<section class="page-header">
    <div class="container text-center">
        <h1 class="fw-bold"><i class="fas fa-map-marked-alt me-2"></i>Track Your Order</h1>
        <p class="mb-0" style="opacity:.9;">Order #{{ $order->order_number }}</p>
    </div>
</section>

<section class="container section-padding" style="position:relative;z-index:2;">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" data-aos="fade-up">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert" data-aos="fade-up">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Left Column: Map + Rider + Items -->
        <div class="col-lg-8">
            <!-- Live Map -->
            <div class="tracking-card mb-4" data-aos="fade-right"
                id="trackingData"
                data-order-id="{{ $order->id }}"
                data-order-status="{{ $order->status }}"
                data-order-lat="{{ $order->latitude ?? '' }}"
                data-order-lng="{{ $order->longitude ?? '' }}"
                data-delivery-lat="{{ ($order->delivery && $order->delivery->latitude) ? $order->delivery->latitude : '' }}"
                data-delivery-lng="{{ ($order->delivery && $order->delivery->longitude) ? $order->delivery->longitude : '' }}"
                data-status-url="{{ route('customer.tracking.status', $order) }}">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0" style="color:var(--gasgo-blue);">
                        <i class="fas fa-satellite-dish me-2" style="color:var(--gasgo-orange);"></i>Live Tracking
                    </h5>
                    <div class="d-flex align-items-center gap-3">
                        <span id="distanceIndicator" style="font-size:.82rem;color:var(--gasgo-orange);font-weight:600;display:none;">
                            <i class="fas fa-route me-1"></i><span id="distanceValue">--</span> km away
                        </span>
                        <span id="liveIndicator" style="font-size:.82rem;color:#27ae60;font-weight:600;">
                            <span class="live-dot"></span>Live
                        </span>
                    </div>
                </div>
                <div class="map-container">
                    <div class="map-overlay" id="mapOverlay">
                        <i class="fas fa-map-marked-alt"></i>
                        <h5 class="fw-bold">Real-Time Map</h5>
                        <p class="text-muted mb-0" id="mapMessage">
                            @if($order->status === 'pending' || $order->status === 'approved')
                                Rider location will appear once a rider is assigned
                            @elseif($order->status === 'delivered')
                                Order has been delivered!
                            @elseif($order->status === 'cancelled')
                                This order was cancelled
                            @else
                                Loading map...
                            @endif
                        </p>
                    </div>
                    <div id="trackingMap"></div>
                </div>
            </div>

            <!-- Rider Info -->
            <div class="rider-card mb-4" data-aos="fade-up" id="riderCard">
                <div class="rider-avatar"><i class="fas fa-motorcycle"></i></div>
                <div class="flex-grow-1">
                    <h6 class="fw-bold mb-0" id="riderName">
                        @if($order->delivery && $order->delivery->rider)
                            {{ $order->delivery->rider->name }}
                        @else
                            Your Rider
                        @endif
                    </h6>
                    <p class="text-muted mb-0" style="font-size:.88rem;" id="riderInfo">
                        @if($order->delivery && $order->delivery->rider)
                            <i class="fas fa-phone me-1"></i>{{ $order->delivery->rider->phone ?? 'No phone' }}
                        @else
                            Will be assigned once order is approved
                        @endif
                    </p>
                </div>
                <div class="text-end">
                    <span class="status-badge-lg status-{{ $order->status }}" id="riderStatusBadge">
                        @if($order->status === 'out_for_delivery')
                            <i class="fas fa-truck"></i> On the way
                        @elseif($order->status === 'delivered')
                            <i class="fas fa-check-circle"></i> Delivered
                        @elseif($order->status === 'assigned')
                            <i class="fas fa-user-check"></i> Assigned
                        @else
                            <i class="fas fa-clock"></i> Pending
                        @endif
                    </span>
                </div>
            </div>

            <!-- Order Items -->
            <div class="tracking-card" data-aos="fade-up">
                <h5 class="fw-bold mb-3" style="color:var(--gasgo-blue);">
                    <i class="fas fa-box me-2" style="color:var(--gasgo-orange);"></i>Order Items
                </h5>
                @foreach($order->orderItems as $item)
                @php
                    $itemImage = $item->product?->resolved_image ?: null;
                @endphp
                <div class="order-item-mini">
                    @if($itemImage)
                        <img src="{{ $itemImage }}" alt="{{ $item->product_name }}">
                    @else
                        <span class="text-muted small">No image available</span>
                    @endif
                    <div class="flex-grow-1">
                        <div class="name">{{ $item->product_name }}</div>
                        <div class="qty">Qty: {{ $item->quantity }} &times; ₱{{ number_format($item->price, 2) }}</div>
                    </div>
                    <div class="fw-bold" style="font-size:.88rem;">₱{{ number_format($item->subtotal, 2) }}</div>
                </div>
                @endforeach

                <div class="d-flex justify-content-between mt-3 pt-2" style="border-top:2px solid #f0f0f0;">
                    <span class="text-muted">Subtotal</span>
                    <span>₱{{ number_format($order->subtotal, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Delivery Fee</span>
                    <span>₱{{ number_format($order->delivery_fee, 2) }}</span>
                </div>
                @if ((float) $order->discount > 0)
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Reward Discount</span>
                    <span style="color:#1e7e34;">-₱{{ number_format($order->discount, 2) }}</span>
                </div>
                @endif
                <div class="d-flex justify-content-between mt-1 pt-2" style="border-top:2px solid var(--gasgo-orange);font-weight:700;font-size:1.1rem;">
                    <span>Total</span>
                    <span style="color:var(--gasgo-orange);">₱{{ number_format($order->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Right Column: Status Timeline -->
        <div class="col-lg-4">
            <div class="tracking-card" data-aos="fade-left">
                <h5 class="fw-bold mb-1" style="color:var(--gasgo-blue);">
                    <i class="fas fa-clipboard-list me-2" style="color:var(--gasgo-orange);"></i>Order Status
                </h5>
                <p class="text-muted mb-3" style="font-size:.85rem;">
                    Placed {{ $order->created_at->format('M j, Y — g:i A') }}
                </p>

                @if($order->status === 'cancelled')
                    <div class="alert" style="background:#f8d7da;color:#721c24;border-radius:12px;border:none;">
                        <i class="fas fa-ban me-2"></i>This order has been cancelled.
                    </div>
                @endif

                <ul class="status-timeline" id="statusTimeline">
                    @foreach($steps as $i => $step)
                    @php
                        $stepClass = '';
                        if ($order->status === 'cancelled') {
                            $stepClass = ($i === 0) ? 'cancelled' : '';
                        } elseif ($i < $currentIndex) {
                            $stepClass = 'done';
                        } elseif ($i === $currentIndex) {
                            $stepClass = 'active';
                        }
                    @endphp
                    <li class="timeline-step {{ $stepClass }}" data-step="{{ $step['key'] }}">
                        <div class="timeline-dot">
                            <i class="{{ $step['icon'] }}"></i>
                        </div>
                        <div class="timeline-info">
                            <h6>{{ $step['title'] }}</h6>
                            <p>{{ $step['desc'] }}</p>
                            @if($stepClass === 'done' || $stepClass === 'active')
                                @if($step['key'] === 'pending')
                                    <div class="time-label">{{ $order->created_at->format('g:i A') }}</div>
                                @elseif($step['key'] === 'assigned' && $order->delivery && $order->delivery->assigned_at)
                                    <div class="time-label">{{ $order->delivery->assigned_at->format('g:i A') }}</div>
                                @elseif($step['key'] === 'out_for_delivery' && $order->delivery && $order->delivery->picked_up_at)
                                    <div class="time-label">{{ $order->delivery->picked_up_at->format('g:i A') }}</div>
                                @elseif($step['key'] === 'delivered' && $order->delivered_at)
                                    <div class="time-label">{{ $order->delivered_at->format('g:i A') }}</div>
                                @endif
                            @endif
                        </div>
                    </li>
                    @endforeach
                </ul>

                <hr>

                <!-- Delivery Details -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted" style="font-size:.88rem;">Payment</span>
                        <span class="fw-bold" style="font-size:.88rem;">{{ ucfirst($order->payment_method) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted" style="font-size:.88rem;">Delivery To</span>
                        <span class="fw-bold text-end" style="font-size:.82rem;max-width:60%;">{{ $order->delivery_address }}</span>
                    </div>
                    @if($order->estimated_delivery_time)
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted" style="font-size:.88rem;">Estimated Delivery</span>
                        <span class="fw-bold" style="color:var(--gasgo-orange);font-size:.88rem;" id="estimatedTime">
                            {{ $order->estimated_delivery_time->format('g:i A') }}
                        </span>
                    </div>
                    @endif
                </div>

                @if($order->status === 'pending')
                <form method="POST" action="{{ route('customer.order.cancel', $order) }}" class="mt-2">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-outline-danger w-100" style="padding:12px;" onclick="return confirm('Cancel this order?');">
                        <i class="fas fa-ban me-2"></i>Cancel Order
                    </button>
                </form>
                @endif

                <a href="{{ route('customer.orders') }}" class="btn btn-gasgo-outline w-100 mt-2" style="padding:12px;">
                    <i class="fas fa-receipt me-2"></i>Back to Orders
                </a>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<!-- Leaflet CSS and JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="{{ asset('css/leaflet-custom.css') }}" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="{{ asset('js/leaflet-utils.js') }}"></script>

<script>
(function() {
    // Read data from HTML attributes
    const trackingEl = document.getElementById('trackingData');
    const orderId = parseInt(trackingEl.dataset.orderId);
    const orderStatus = trackingEl.dataset.orderStatus;
    const statusUrl = trackingEl.dataset.statusUrl;
    
    // Parse coordinates
    const deliveryLat = trackingEl.dataset.deliveryLat ? parseFloat(trackingEl.dataset.deliveryLat) : null;
    const deliveryLng = trackingEl.dataset.deliveryLng ? parseFloat(trackingEl.dataset.deliveryLng) : null;
    const hasDeliveryCoords = (deliveryLat && deliveryLng) ? true : false;
    
    const deliveryInitLat = deliveryLat || (trackingEl.dataset.orderLat ? parseFloat(trackingEl.dataset.orderLat) : null);
    const deliveryInitLng = deliveryLng || (trackingEl.dataset.orderLng ? parseFloat(trackingEl.dataset.orderLng) : null);

    const statusOrder = ['pending', 'approved', 'assigned', 'out_for_delivery', 'delivered'];
    const statusBadgeLabels = {
        pending: '<i class="fas fa-clock"></i> Pending',
        approved: '<i class="fas fa-thumbs-up"></i> Approved',
        assigned: '<i class="fas fa-user-check"></i> Assigned',
        out_for_delivery: '<i class="fas fa-truck"></i> On the way',
        delivered: '<i class="fas fa-check-circle"></i> Delivered',
        cancelled: '<i class="fas fa-ban"></i> Cancelled'
    };

    let map = null;
    let riderMarker = null;
    let destMarker = null;
    let waypointMarkers = [];
    let polyline = null;
    let bounds = null;
    let currentStatus = orderStatus;

    // Wait for Google Maps to load
    window.addEventListener('load', function() {
        if (hasDeliveryCoords && deliveryInitLat && deliveryInitLng) {
            initMap(deliveryInitLat, deliveryInitLng);
        }
    });

    function initMap(lat, lng) {
        if (map) return; // already initialized

        document.getElementById('mapOverlay').classList.add('hidden');

        // Initialize Leaflet map
        map = initLeafletMap('trackingMap', lat, lng, 15);

        bounds = L.latLngBounds();

        // Rider marker (orange pulsing)
        riderMarker = L.marker([lat, lng], {
            icon: L.divIcon({
                className: 'rider-marker-icon',
                html: `
                    <div class="rider-icon-container" style="
                        width: 50px;
                        height: 50px;
                        background: #f7941d;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        color: white;
                        font-size: 22px;
                        border: 4px solid white;
                        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                        animation: riderPulse 2s infinite;
                    ">
                        <i class="fas fa-motorcycle"></i>
                    </div>
                    <style>
                    @keyframes riderPulse {
                        0%, 100% {
                            box-shadow: 0 4px 12px rgba(0,0,0,0.3), 0 0 0 0 rgba(247, 148, 29, 0.7);
                        }
                        50% {
                            box-shadow: 0 4px 12px rgba(0,0,0,0.3), 0 0 0 20px rgba(247, 148, 29, 0);
                        }
                    }
                    </style>
                `,
                iconSize: [50, 50],
                iconAnchor: [25, 25]
            })
        });
        riderMarker.addTo(map);
        riderMarker.bindPopup('<div style="padding:8px;"><b>🏍️ Your Rider</b><br><small>Live location</small></div>');
        bounds.extend(riderMarker.getLatLng());

        // Destination marker (blue)
        if (deliveryLat && deliveryLng) {
            destMarker = createCustomMarker(deliveryLat, deliveryLng, {
                color: '#1a6db0',
                iconType: 'circle',
                size: 24
            });
            destMarker.addTo(map);
            destMarker.bindPopup('<div style="padding:8px;"><b>Your Delivery Address</b></div>');
            bounds.extend(destMarker.getLatLng());
        }

        // Fit bounds
        if (!bounds.isEmpty()) {
            map.fitBounds(bounds, { padding: [50, 50] });
        }
    }

    function updateRiderPosition(lat, lng) {
        if (!map) {
            initMap(lat, lng);
        } else if (riderMarker) {
            smoothMoveMarker(riderMarker, lat, lng, 2000); // 2 second smooth animation

            if (destMarker) {
                bounds = L.latLngBounds([
                    riderMarker.getLatLng(),
                    destMarker.getLatLng()
                ]);
                map.fitBounds(bounds, { padding: [50, 50] });
            } else {
                map.panTo([lat, lng]);
            }
        }
    }

    function updateTimeline(newStatus) {
        const newIndex = statusOrder.indexOf(newStatus);
        const steps = document.querySelectorAll('.timeline-step');

        steps.forEach((step, i) => {
            step.classList.remove('done', 'active', 'cancelled');
            if (newStatus === 'cancelled') {
                if (i === 0) step.classList.add('cancelled');
            } else if (i < newIndex) {
                step.classList.add('done');
            } else if (i === newIndex) {
                step.classList.add('active');
            }
        });
    }

    function updateUI(data) {
        // Update status badge
        const badge = document.getElementById('riderStatusBadge');
        if (badge && statusBadgeLabels[data.status]) {
            badge.className = 'status-badge-lg status-' + data.status;
            badge.innerHTML = statusBadgeLabels[data.status];
        }

        // Update rider info
        if (data.rider_name) {
            document.getElementById('riderName').textContent = data.rider_name;
            const info = document.getElementById('riderInfo');
            if (data.rider_phone) {
                info.innerHTML = '<i class="fas fa-phone me-1"></i>' + data.rider_phone;
            }
        }

        // Update map
        if (data.rider_lat && data.rider_lng) {
            updateRiderPosition(parseFloat(data.rider_lat), parseFloat(data.rider_lng));
        }

        // Render waypoints from rider's route
        if (data.waypoints && Array.isArray(data.waypoints) && data.waypoints.length > 0 && map) {
            renderWaypoints(data.waypoints, parseFloat(data.rider_lat), parseFloat(data.rider_lng));
        }

        // Update map overlay message
        const overlay = document.getElementById('mapOverlay');
        const msg = document.getElementById('mapMessage');
        if (data.status === 'delivered') {
            overlay.classList.remove('hidden');
            msg.textContent = 'Order has been delivered!';
        } else if (data.status === 'cancelled') {
            overlay.classList.remove('hidden');
            msg.textContent = 'This order was cancelled';
        } else if (!data.rider_lat && !data.rider_lng) {
            if (data.status === 'pending' || data.status === 'approved') {
                msg.textContent = 'Rider location will appear once a rider is assigned';
            } else {
                msg.textContent = 'Waiting for rider location update...';
            }
        }

        // Update timeline if status changed
        if (data.status !== currentStatus) {
            currentStatus = data.status;
            updateTimeline(data.status);
        }

        // Update estimated delivery
        if (data.estimated_delivery) {
            const estEl = document.getElementById('estimatedTime');
            if (estEl) estEl.textContent = data.estimated_delivery;
        }
    }

    function renderWaypoints(waypoints, riderLat, riderLng) {
        // Clear existing waypoint markers
        if (waypointMarkers && waypointMarkers.length > 0) {
            waypointMarkers.forEach(marker => map.removeLayer(marker));
        }
        waypointMarkers = [];

        // Clear existing polyline
        if (polyline) {
            map.removeLayer(polyline);
            polyline = null;
        }

        // Build waypoint path for polyline
        const waypointPath = [[riderLat, riderLng]];

        // Create markers for each waypoint
        waypoints.forEach((waypoint, index) => {
            const waypointLat = parseFloat(waypoint.latitude);
            const waypointLng = parseFloat(waypoint.longitude);
            const isCurrentDelivery = waypoint.is_current === true || waypoint.is_current === 1 || waypoint.is_current === '1';

            const position = [waypointLat, waypointLng];
            waypointPath.push(position);

            // Determine color and styling
            let color = '#9e9e9e'; // Gray for pending
            if (waypoint.status === 'completed') {
                color = '#4caf50'; // Green
            } else if (isCurrentDelivery) {
                color = '#1a6db0'; // Blue for current
            }

            // Create numbered marker
            const marker = createNumberedMarker(waypointLat, waypointLng, index + 1, color);
            marker.addTo(map);

            // Info window content
            const popupContent = `
                <div style="padding: 10px; max-width: 260px; font-family: system-ui;">
                    <h6 style="margin: 0 0 8px 0; font-size: 14px;"><strong>Stop ${waypoint.sequence}</strong> - ${waypoint.customer}</h6>
                    <p style="margin: 0 0 5px 0; font-size: 12px;"><strong>Address:</strong> ${waypoint.address}</p>
                    <p style="margin: 0 0 5px 0; font-size: 12px;"><strong>Order:</strong> #${waypoint.order_number}</p>
                    <p style="margin: 0 0 5px 0; font-size: 12px;"><strong>Amount:</strong> ₱${parseFloat(waypoint.amount).toFixed(2)}</p>
                    <p style="margin: 0; font-size: 12px;"><span style="display: inline-block; padding: 2px 6px; background-color: ${color}; color: white; border-radius: 3px; font-size: 11px;">${waypoint.status.toUpperCase()}</span></p>
                </div>
            `;
            marker.bindPopup(popupContent);

            waypointMarkers.push(marker);
        });

        // Draw polyline connecting all waypoints
        if (waypointPath.length > 1) {
            polyline = drawRouteLine(map, waypointPath, {
                color: '#2196f3',
                weight: 3,
                opacity: 0.6
            });
        }
    }

    // Poll for updates every 10 seconds (only for active orders)
    function pollStatus() {
        if (currentStatus === 'delivered' || currentStatus === 'cancelled') return;

        fetch(statusUrl, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (!data.error) updateUI(data);
        })
        .catch(() => {});

        setTimeout(pollStatus, 10000);
    }

    // Start polling after initial page load
    if (currentStatus !== 'delivered' && currentStatus !== 'cancelled') {
        setTimeout(pollStatus, 10000);
    }
})();
</script>
@endsection
