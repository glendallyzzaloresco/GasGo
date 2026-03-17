@extends('layouts.rider')

@section('title', 'GasGo Rider - Active Route')
@section('page-title', 'My Route')

@section('rider-styles')
<style>
    .route-map {
        width:100%; height:400px; border-radius:16px; overflow:hidden;
        background:var(--gasgo-blue-light); margin-bottom:24px;
        box-shadow: 0 6px 22px rgba(15,23,42,.1);
    }
    
    .waypoints-list {
        max-height: 600px; overflow-y: auto;
    }
    
    .waypoint-item {
        background: #fff;
        border: 1px solid var(--admin-border);
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 12px;
        cursor: pointer;
        transition: all .3s ease;
        position: relative;
        border-left: 4px solid transparent;
    }
    
    .waypoint-item:hover {
        box-shadow: 0 4px 12px rgba(15,23,42,.1);
        transform: translateX(4px);
    }
    
    .waypoint-item.completed {
        background: #f0fdf4;
        border-left-color: #22c55e;
    }
    
    .waypoint-item.active {
        background: #fef3c7;
        border-left-color: var(--gasgo-orange);
        box-shadow: 0 4px 12px rgba(247,148,29,.2);
    }
    
    .waypoint-item.pending {
        border-left-color: var(--gasgo-blue);
    }
    
    .waypoint-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: var(--gasgo-blue);
        color: #fff;
        font-weight: 700;
        font-size: .85rem;
        margin-right: 12px;
    }
    
    .waypoint-item.completed .waypoint-number {
        background: #22c55e;
    }
    
    .waypoint-item.active .waypoint-number {
        background: var(--gasgo-orange);
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(247,148,29,.4); }
        50% { box-shadow: 0 0 0 8px rgba(247,148,29,0); }
    }
    
    .waypoint-header {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
    }
    
    .waypoint-info {
        flex: 1;
    }
    
    .waypoint-order {
        font-weight: 700;
        color: var(--gasgo-blue);
        font-size: .95rem;
        margin-bottom: 4px;
    }
    
    .waypoint-customer {
        font-size: .85rem;
        color: #666;
        margin-bottom: 4px;
    }
    
    .waypoint-address {
        font-size: .8rem;
        color: #888;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
    }
    
    .waypoint-address i {
        color: var(--gasgo-orange);
        margin-right: 6px;
        flex-shrink: 0;
    }
    
    .waypoint-amount {
        font-weight: 700;
        color: var(--gasgo-orange);
        font-size: .9rem;
        margin-right: 12px;
    }
    
    .waypoint-action {
        display: flex;
        gap: 8px;
        margin-top: 10px;
    }
    
    .waypoint-action .btn {
        flex: 1;
        padding: 6px 10px;
        font-size: .75rem;
        border-radius: 8px;
    }
    
    .route-stats {
        background: linear-gradient(135deg, #f5fbff 0%, #edf6ff 100%);
        border: 1px solid var(--admin-border);
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 20px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 12px;
    }
    
    .stat-item {
        text-align: center;
    }
    
    .stat-value {
        font-size: 1.4rem;
        font-weight: 700;
        color: var(--gasgo-blue);
    }
    
    .stat-label {
        font-size: .75rem;
        color: #666;
        text-transform: uppercase;
        letter-spacing: .5px;
        margin-top: 4px;
    }
</style>
@endsection

@section('content')
<!-- Route Statistics -->
<div class="route-stats">
    <div class="stat-item">
        <div class="stat-value">{{ count($activeDeliveries) }}</div>
        <div class="stat-label">Active Stops</div>
    </div>
    <div class="stat-item">
        <div class="stat-value">{{ $deliveredCount ?? 0 }}</div>
        <div class="stat-label">Completed</div>
    </div>
    <div class="stat-item">
        <div class="stat-value">{{ round($totalDistance ?? 0, 1) }}km</div>
        <div class="stat-label">Est. Distance</div>
    </div>
</div>

<!-- Route Map -->
<div class="rider-card mb-4">
    <h6 style="color:var(--gasgo-blue);font-weight:600;margin-bottom:16px;"><i class="fas fa-map me-2" style="color:var(--gasgo-orange);"></i>Route Map</h6>
    <div class="route-map" id="routeMap"></div>
    <small class="text-muted" style="display:block;margin-top:8px;">
        <i class="fas fa-info-circle me-1"></i>Tap on waypoints to navigate to delivery details
    </small>
</div>

<!-- Waypoints List -->
<div class="rider-card">
    <h6 style="color:var(--gasgo-blue);font-weight:600;margin-bottom:16px;"><i class="fas fa-list me-2" style="color:var(--gasgo-orange);"></i>Delivery Route {{ count($activeDeliveries) > 0 ? '(' . count($activeDeliveries) . ' stops)' : '' }}</h6>
    
    @if(count($activeDeliveries) > 0)
        <div class="waypoints-list">
            @foreach($activeDeliveries as $index => $delivery)
                <div class="waypoint-item {{ $delivery->status === 'delivered' ? 'completed' : ($index === 0 ? 'active' : 'pending') }}"
                     onclick="navigateToDelivery(this.dataset.deliveryId)"
                     data-delivery-id="{{ $delivery->id }}"
                     data-lat="{{ $delivery->order->latitude ?? 0 }}"
                     data-lng="{{ $delivery->order->longitude ?? 0 }}">
                    
                    <div class="waypoint-header">
                        <div class="waypoint-number">{{ $index + 1 }}</div>
                        <div class="waypoint-info" style="flex: 1;">
                            <div class="waypoint-order">Order #{{ $delivery->order->order_number }}</div>
                            <div class="waypoint-customer">
                                <i class="fas fa-user me-1"></i>{{ $delivery->order->user->name }}
                            </div>
                        </div>
                        <span class="badge-status badge-{{ $delivery->status }}" style="white-space: nowrap;">
                            {{ str_replace('_', ' ', ucfirst($delivery->status)) }}
                        </span>
                    </div>
                    
                    <div class="waypoint-address">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>{{ Str::limit($delivery->order->delivery_address, 50) }}</span>
                    </div>
                    
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                        <span class="waypoint-amount">₱{{ number_format($delivery->order->total_amount, 2) }}</span>
                        <small class="text-muted">
                            @if($delivery->status === 'delivered' && $delivery->delivered_at)
                                Delivered {{ $delivery->delivered_at->format('g:i A') }}
                            @else
                                Assigned {{ $delivery->assigned_at?->format('M d, g:i A') ?? 'recently' }}
                            @endif
                        </small>
                    </div>
                    
                    @if($delivery->status !== 'delivered')
                        <div class="waypoint-action">
                            <a href="{{ route('rider.delivery', $delivery->id) }}" class="btn" style="background:var(--gasgo-orange);color:#fff;text-decoration:none;">
                                <i class="fas fa-eye me-1"></i>View
                            </a>
                            <a href="tel:{{ $delivery->order->contact_number }}" class="btn" style="background:var(--gasgo-blue-light);color:var(--gasgo-blue);text-decoration:none;">
                                <i class="fas fa-phone"></i>
                            </a>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center text-muted py-5">
            <i class="fas fa-inbox" style="font-size:2.5rem;color:#ccc;margin-bottom:15px;display:block;"></i>
            <p style="font-size:.9rem;">No active deliveries on your route. Check back soon!</p>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<!-- Leaflet CSS and JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="{{ asset('css/leaflet-custom.css') }}" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="{{ asset('js/leaflet-utils.js') }}"></script>
<script>
    let routeMap = null;
    let waypoints = [];
    let markers = [];
    let polyline = null;

    function navigateToDelivery(deliveryId) {
        window.location.href = `/rider/delivery/${deliveryId}`;
    }

    window.addEventListener('load', function() {
        initRouteMap();
    });

    function initRouteMap() {
        const waypontEls = document.querySelectorAll('.waypoint-item');
        if (waypontEls.length === 0) return;

        waypoints = Array.from(waypontEls).map(el => ({
            id: el.dataset.deliveryId,
            lat: parseFloat(el.dataset.lat),
            lng: parseFloat(el.dataset.lng)
        })).filter(w => w.lat && w.lng);

        if (waypoints.length === 0) {
            document.getElementById('routeMap').innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:#999;"><i style="font-size:2rem;margin-right:10px;" class="fas fa-map-marked-alt"></i>No coordinates available</div>';
            return;
        }

        const centerLat = waypoints[0].lat;
        const centerLng = waypoints[0].lng;

        // Initialize Leaflet map
        routeMap = initLeafletMap('routeMap', centerLat, centerLng, 13);

        // Array to store coordinates for polyline
        const pathCoordinates = [];

        // Add markers for each waypoint
        waypoints.forEach((waypoint, index) => {
            pathCoordinates.push([waypoint.lat, waypoint.lng]);

            // Color based on index: green for first (active), orange for second, blue for rest
            let markerColor = '#2196f3'; // blue (pending)
            if (index === 0) markerColor = '#f7941d'; // orange (active)
            else if (index === 1) markerColor = '#22c55e'; // green (next)

            // Create numbered marker
            const marker = createNumberedMarker(waypoint.lat, waypoint.lng, index + 1, markerColor);
            marker.addTo(routeMap);

            // Add popup with delivery info
            marker.bindPopup(`
                <div style="padding: 10px; text-align: center;">
                    <strong style="color: var(--gasgo-blue);">Stop ${index + 1}</strong>
                    <br><small>Click to view delivery details</small>
                </div>
            `);

            // Click handler to navigate to delivery
            marker.on('click', () => {
                navigateToDelivery(waypoint.id);
            });

            markers.push(marker);
        });

        // Draw polyline connecting all waypoints
        if (pathCoordinates.length > 1) {
            polyline = drawRouteLine(routeMap, pathCoordinates, {
                color: '#1a6db0',
                weight: 3,
                opacity: 0.7
            });
        }

        // Fit map to show all markers
        const bounds = L.latLngBounds(pathCoordinates);
        routeMap.fitBounds(bounds, { padding: [50, 50] });
    }

    // Highlight waypoint when clicking on list item
    document.querySelectorAll('.waypoint-item').forEach((el, index) => {
        el.addEventListener('click', function() {
            document.querySelectorAll('.waypoint-item').forEach(e => e.style.opacity = '0.6');
            this.style.opacity = '1';

            if (markers[index] && routeMap) {
                routeMap.setView(markers[index].getLatLng(), 15, {
                    animate: true,
                    duration: 0.5
                });
            }
        });
    });
</script>
@endsection
