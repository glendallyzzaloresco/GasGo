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
        border-radius: 20px; height: 480px; overflow: hidden; position: relative;
        box-shadow: inset 0 0 20px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.08);
    }
    #trackingMap { width: 100%; height: 100%; }
    .map-overlay {
        position: absolute; top: 0; left: 0; right: 0; bottom: 0;
        display: flex; align-items: center; justify-content: center; flex-direction: column;
        color: var(--gasgo-blue); z-index: 10; background: rgba(248, 249, 250, 0.95);
        backdrop-filter: blur(8px);
    }
    .map-overlay i { font-size: 3.5rem; margin-bottom: 15px; color: var(--gasgo-orange); }
    .map-overlay.hidden { display: none; }

    /* Rider marker animation - Grab/Foodpanda style */
    .rider-marker-pulsing {
        position: relative;
        width: 60px;
        height: 60px;
        background: #f7941d;
        border: 3px solid #ffffff;
        border-radius: 50%;
        box-shadow: 0 4px 15px rgba(0,0,0,0.25);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
    }
    .rider-marker-pulsing::before {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: rgba(247, 148, 29, 0.4);
        animation: grabPulse 1.8s infinite ease-out;
        z-index: -1;
    }
    @keyframes grabPulse {
        0% {
            transform: scale(1);
            opacity: 1;
        }
        100% {
            transform: scale(2.2);
            opacity: 0;
        }
    }

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
                data-order-lat="{{ $order->latitude ?? '15.7968' }}"
                data-order-lng="{{ $order->longitude ?? '120.5631' }}"
                data-delivery-lat="{{ ($order->delivery && $order->delivery->latitude) ? $order->delivery->latitude : ($order->delivery && $order->delivery->rider && $order->delivery->rider->rider && $order->delivery->rider->rider->current_latitude ? $order->delivery->rider->rider->current_latitude : '16.0196129') }}"
                data-delivery-lng="{{ ($order->delivery && $order->delivery->longitude) ? $order->delivery->longitude : ($order->delivery && $order->delivery->rider && $order->delivery->rider->rider && $order->delivery->rider->rider->current_longitude ? $order->delivery->rider->rider->current_longitude : '120.3593023') }}"
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

                <!-- Location Permission Reminder -->
                <div class="alert alert-info alert-dismissible fade show mb-3" id="locationPermissionAlert" role="alert" style="background: linear-gradient(135deg, #e8f4fc 0%, #d1ecf1 100%); border: 1px solid #0c5460; border-radius: 12px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <i class="fas fa-map-marker-alt" style="font-size: 1.2rem; color: #0c5460;"></i>
                        <div>
                            <strong style="color: #0c5460;">Enable Location for Better Tracking</strong>
                            <br>
                            <small style="color: #0c5460; opacity: 0.9;">Click the <strong>🔒 lock icon</strong> in your browser address bar and select <strong>"Allow"</strong> to get real-time rider location updates.</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
                <div class="rider-avatar"><i class="fas fa-truck"></i></div>
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

            <!-- Customer Address Info -->
            <div class="rider-card mb-4" data-aos="fade-up" style="background: linear-gradient(135deg, #e8f4fc 0%, #f0f8ff 100%); border-left: 4px solid #1a6db0;">
                <div class="rider-avatar" style="background: linear-gradient(135deg, #1a6db0, #0d3a70);"><i class="fas fa-map-marker-alt"></i></div>
                <div class="flex-grow-1">
                    <h6 class="fw-bold mb-0" style="color:#1a6db0;">Delivery Address</h6>
                    <p class="text-muted mb-0" style="font-size:.88rem;" id="customerAddressInfo">
                        {{ $order->delivery_address }}
                    </p>
                </div>
                <div class="text-end">
                    <span style="font-size:1.4rem;color:#1a6db0;"><i class="fas fa-location-dot"></i></span>
                </div>
            </div>

            <!-- Order Items -->
            <div class="tracking-card" data-aos="fade-up">
                <h5 class="fw-bold mb-3" style="color:var(--gasgo-blue);">
                    <i class="fas fa-box me-2" style="color:var(--gasgo-orange);"></i>Order Items
                </h5>
                @foreach($order->orderItems as $item)
                @php
                    // For reward items, use the stored image URL; for regular items, use product image
                    $itemImage = null;
                    
                    if ($item->is_reward) {
                        // First try the stored reward_image_url
                        if ($item->reward_image_url) {
                            $itemImage = $item->reward_image_url;
                        } else {
                            // Fallback for legacy: try to get from product relationship
                            $itemImage = $item->product?->resolved_image;
                        }
                    } else {
                        // For regular items, always use product image
                        $itemImage = $item->product?->resolved_image;
                    }
                @endphp
                <div class="order-item-mini">
                    @if($itemImage)
                        <img src="{{ $itemImage }}" alt="{{ $item->product_name }}">
                    @else
                        <div class="text-muted small" style="padding: 8px; background: #f8f9fa; border-radius: 8px; min-width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;">
                            No image
                        </div>
                    @endif
                    <div class="flex-grow-1">
                        <div class="name">
                            {{ $item->product_name }}
                            @if($item->is_reward)
                                <span style="background: #d4edda; color: #155724; padding: 2px 6px; border-radius: 4px; font-size: .7rem; font-weight: 600; margin-left: 4px;"><i class="fas fa-gift me-1"></i>FREE</span>
                            @endif
                        </div>
                        <div class="qty">Qty: {{ $item->quantity }} &times; ₱{{ number_format($item->price, 2) }}</div>
                    </div>
                    <div class="fw-bold" style="font-size:.88rem;">₱{{ number_format($item->subtotal, 2) }}</div>
                </div>
                @endforeach

                <div class="d-flex justify-content-between mt-3 pt-2" style="border-top:2px solid #f0f0f0;">
                    <span class="text-muted">Subtotal</span>
                    <span>₱{{ number_format($order->subtotal, 2) }}</span>
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
                <p class="text-muted mb-1" style="font-size:.85rem;">
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
                                @elseif($step['key'] === 'approved' && $order->approved_at)
                                    <div class="time-label">{{ $order->approved_at->format('g:i A') }}</div>
                                @elseif($step['key'] === 'assigned' && $order->delivery && $order->delivery->assigned_at)
                                    <div class="time-label">{{ $order->delivery->assigned_at->format('g:i A') }}</div>
                                @elseif($step['key'] === 'out_for_delivery')
                                    @if($order->delivery && $order->delivery->picked_up_at)
                                        <div class="time-label">{{ $order->delivery->picked_up_at->format('g:i A') }}</div>
                                    @elseif($order->status === 'out_for_delivery')
                                        <div class="time-label">{{ $order->updated_at->format('g:i A') }}</div>
                                    @endif
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
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted" style="font-size:.88rem;">Delivery Fee</span>
                        <span class="fw-bold">₱{{ number_format($order->delivery_fee, 2) }}</span>
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
    const orderLat = trackingEl.dataset.orderLat ? parseFloat(trackingEl.dataset.orderLat) : null;
    const orderLng = trackingEl.dataset.orderLng ? parseFloat(trackingEl.dataset.orderLng) : null;
    const deliveryLat = trackingEl.dataset.deliveryLat ? parseFloat(trackingEl.dataset.deliveryLat) : null;
    const deliveryLng = trackingEl.dataset.deliveryLng ? parseFloat(trackingEl.dataset.deliveryLng) : null;
    const hasDeliveryCoords = (deliveryLat && deliveryLng) ? true : false;
    
    const deliveryInitLat = deliveryLat || orderLat;
    const deliveryInitLng = deliveryLng || orderLng;

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
    let routeLine = null;
    let currentStatus = orderStatus;

    // Initialize map immediately on load if rider is assigned/active
    window.addEventListener('load', function() {
        const msg = document.getElementById('mapMessage');
        if (orderStatus === 'pending' || orderStatus === 'approved') {
            msg.textContent = 'Rider location will appear once a rider is assigned';
        } else if (['assigned', 'picked_up', 'out_for_delivery'].includes(orderStatus)) {
            if (deliveryInitLat && deliveryInitLng) {
                initMap(deliveryInitLat, deliveryInitLng);
            } else {
                msg.textContent = 'Loading map...';
            }
        }
        
        // Start polling updates immediately if order is active
        if (orderStatus !== 'delivered' && orderStatus !== 'cancelled') {
            pollStatus();
        }
    });

    function initMap(lat, lng) {
        if (map) return; // already initialized

        document.getElementById('mapOverlay').classList.add('hidden');

        // Initialize Leaflet map
        map = initLeafletMap('trackingMap', lat, lng, 15);

        // Rider marker - Grab/Foodpanda style pulsing marker
        const riderHtml = `<div class="rider-marker-pulsing">🛵</div>`;

        riderMarker = L.marker([lat, lng], {
            icon: L.divIcon({
                html: riderHtml,
                iconSize: [60, 60],
                iconAnchor: [30, 30],
                className: ''
            })
        }).addTo(map);
        riderMarker.bindPopup('<div style="padding:8px;"><b>Rider Location</b><br><small>Live tracking</small></div>');

        // Destination marker - use the delivery coordinates from order
        const destLat = parseFloat(trackingEl.dataset.orderLat);
        const destLng = parseFloat(trackingEl.dataset.orderLng);
        
        if (destLat && destLng && !isNaN(destLat) && !isNaN(destLng)) {
            // Create a custom destination marker with label
            const destDiv = document.createElement('div');
            destDiv.style.width = '50px';
            destDiv.style.height = '50px';
            destDiv.style.background = '#1a6db0';
            destDiv.style.borderRadius = '50%';
            destDiv.style.display = 'flex';
            destDiv.style.alignItems = 'center';
            destDiv.style.justifyContent = 'center';
            destDiv.style.border = '4px solid white';
            destDiv.style.boxShadow = '0 4px 12px rgba(0,0,0,0.3)';
            destDiv.style.fontSize = '24px';
            destDiv.style.color = 'white';
            destDiv.style.fontWeight = 'bold';
            destDiv.textContent = '📍';
            
            destMarker = L.marker([destLat, destLng], {
                icon: L.divIcon({
                    html: destDiv.outerHTML,
                    iconSize: [50, 50],
                    iconAnchor: [25, 25]
                })
            }).addTo(map);
            destMarker.bindPopup('<div style="padding:8px;"><b>Your Delivery Address</b></div>');

            // Draw route line using OSRM for road-based routing
            drawDeliveryRoute(lat, lng, destLat, destLng);
        } else {
            console.log('Destination coordinates missing or invalid:', {destLat, destLng});
        }
    }

    function drawDeliveryRoute(fromLat, fromLng, toLat, toLng) {
        // Use OSRM API to get road-based route
        const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${fromLng},${fromLat};${toLng},${toLat}?overview=full&geometries=geojson`;
        
        fetch(osrmUrl, {timeout: 5000})
            .then(response => response.json())
            .then(data => {
                if (data.routes && data.routes.length > 0) {
                    const route = data.routes[0];
                    const coordinates = route.geometry.coordinates.map(coord => [coord[1], coord[0]]);
                    
                    if (window.routeBgLine) {
                        map.removeLayer(window.routeBgLine);
                    }
                    if (routeLine) {
                        map.removeLayer(routeLine);
                    }
                    
                    // Soft neon-green glow background line
                    window.routeBgLine = L.polyline(coordinates, {
                        color: '#00b14f',
                        weight: 12,
                        opacity: 0.2,
                        lineCap: 'round',
                        lineJoin: 'round'
                    }).addTo(map);

                    // Solid bright-green foreground line
                    routeLine = L.polyline(coordinates, {
                        color: '#00b14f',
                        weight: 5,
                        opacity: 0.95,
                        lineCap: 'round',
                        lineJoin: 'round'
                    }).addTo(map);
                    console.log('Route drawn via OSRM');
                } else {
                    console.log('No OSRM route found, using direct line');
                    drawDirectLine(fromLat, fromLng, toLat, toLng);
                }
            })
            .catch(error => {
                console.error('OSRM error:', error);
                drawDirectLine(fromLat, fromLng, toLat, toLng);
            });
    }

    function drawDirectLine(fromLat, fromLng, toLat, toLng) {
        if (window.routeBgLine) {
            map.removeLayer(window.routeBgLine);
        }
        if (routeLine) {
            map.removeLayer(routeLine);
        }
        
        window.routeBgLine = L.polyline(
            [[fromLat, fromLng], [toLat, toLng]],
            {
                color: '#00b14f',
                weight: 12,
                opacity: 0.2
            }
        ).addTo(map);

        routeLine = L.polyline(
            [[fromLat, fromLng], [toLat, toLng]],
            {
                color: '#00b14f',
                weight: 5,
                opacity: 0.95,
                dashArray: '5, 8'
            }
        ).addTo(map);
        console.log('Direct line drawn (fallback)');
    }

    function updateRiderPosition(lat, lng) {
        if (!map) {
            initMap(lat, lng);
        } else if (riderMarker) {
            smoothMoveMarker(riderMarker, lat, lng, 2000); // 2 second smooth animation
            map.panTo([lat, lng]); // Pan to rider location without zooming

            // Update route line to delivery address
            const destLat = parseFloat(trackingEl.dataset.orderLat);
            const destLng = parseFloat(trackingEl.dataset.orderLng);
            
            if (destLat && destLng && !isNaN(destLat) && !isNaN(destLng)) {
                drawDeliveryRoute(lat, lng, destLat, destLng);
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

        // Upd
        if (data.rider_name) {
            document.getElementById('riderName').textContent = data.rider_name;
            const info = document.getElementById('riderInfo');
            if (data.rider_phone) {
                info.innerHTML = '<i class="fas fa-phone me-1"></i>' + data.rider_phone;
            }
        }

        // Calculate and display distance
        if (data.rider_lat && data.rider_lng && orderLat && orderLng) {
            const distance = calculateDistance(
                parseFloat(data.rider_lat),
                parseFloat(data.rider_lng),
                orderLat,
                orderLng
            );
            
            const distanceEl = document.getElementById('distanceIndicator');
            if (distanceEl) {
                distanceEl.style.display = 'inline-flex';
                document.getElementById('distanceValue').textContent = distance.toFixed(1);
            }
        }

        const canShowRiderMap = ['assigned', 'picked_up', 'out_for_delivery'].includes(data.status);

        // Initialize map when rider is assigned/active
        if (canShowRiderMap && data.rider_lat && data.rider_lng) {
            if (!map) {
                initMap(parseFloat(data.rider_lat), parseFloat(data.rider_lng));
            } else {
                updateRiderPosition(parseFloat(data.rider_lat), parseFloat(data.rider_lng));
            }
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
        } else if (!canShowRiderMap) {
            overlay.classList.remove('hidden');
            msg.textContent = 'Rider location will appear once a rider is assigned';
        } else if (!data.rider_lat || !data.rider_lng) {
            overlay.classList.remove('hidden');
            msg.textContent = 'Waiting for rider location update...';
        } else {
            overlay.classList.add('hidden');
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

    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371; // Earth's radius in km
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = 
            Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
            Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

    // Poll for updates (increased frequency to 2 seconds for live tracking)
    function pollStatus() {
        if (currentStatus === 'delivered' || currentStatus === 'cancelled') return;

        fetch(statusUrl, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (!data.error) {
                updateUI(data);
            }
        })
        .catch(() => {});

        // Poll every 2 seconds instead of 5 for better real-time tracking
        setTimeout(pollStatus, 2000);
    }

    // Start polling immediately (not after delay) for live tracking
    if (currentStatus !== 'delivered' && currentStatus !== 'cancelled') {
        // First poll immediately
        fetch(statusUrl, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (!data.error) {
                updateUI(data);
                // Then continue polling
                setTimeout(pollStatus, 2000);
            }
        })
        .catch(() => {
            // If first poll fails, still start polling
            setTimeout(pollStatus, 2000);
        });
    }

    // Check and request geolocation permission
    function checkLocationPermission() {
        if (!navigator.geolocation) {
            return;
        }

        // Try to get current position to check permission status
        navigator.geolocation.getCurrentPosition(
            function(position) {
                // Permission granted, hide alert
                const alert = document.getElementById('locationPermissionAlert');
                if (alert) {
                    alert.style.display = 'none';
                }
            },
            function(error) {
                // Permission denied or error
                if (error.code === error.PERMISSION_DENIED || error.code === 1) {
                    const alert = document.getElementById('locationPermissionAlert');
                    if (alert) {
                        // Keep alert visible if permission is denied
                        alert.style.display = 'block';
                    }
                }
            },
            {
                timeout: 5000,
                maximumAge: 30000
            }
        );
    }

    // Update the live clock every second
    function updateCurrentTime() {
        const timeEl = document.getElementById('currentTime');
        if (!timeEl) return;

        const now = new Date();
        const hours = now.getHours();
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        const hour12 = hours % 12 || 12;
        timeEl.textContent = `${hour12}:${minutes}:${seconds} ${ampm}`;
    }

    updateCurrentTime();
    setInterval(updateCurrentTime, 1000);

    // Check location permission after page loads
    window.addEventListener('load', function() {
        setTimeout(checkLocationPermission, 1000); // Check after 1 second
    });
})();
</script>
@endsection
