@extends('layouts.rider')

@section('title', 'GasGo Rider - Live Route Map')
@section('page-title', 'Live Route Map')
@section('nav-route', 'active')

@section('rider-styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
<link rel="stylesheet" href="{{ asset('css/leaflet-custom.css') }}" />
<style>
    .route-map-container {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 20px;
        height: calc(100vh - 180px);
        min-height: 500px;
    }

    @media (max-width: 992px) {
        .route-map-container {
            grid-template-columns: 1fr;
            height: auto;
        }
    }

    /* Map Section */
    .map-section {
        background: #1a2744;
        border-radius: 16px;
        overflow: hidden;
        position: relative;
    }

    .map-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        background: linear-gradient(135deg, #1a2744, #243656);
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }

    .map-title {
        display: flex;
        align-items: center;
        gap: 10px;
        color: white;
        font-weight: 700;
        font-size: 1.1rem;
    }

    .map-title i {
        color: var(--gasgo-orange);
    }

    .map-subtitle {
        color: rgba(255,255,255,0.6);
        font-size: 0.8rem;
        margin-top: 4px;
    }

    .map-actions {
        display: flex;
        gap: 10px;
    }

    .btn-track {
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }

    .btn-track.start {
        background: #27ae60;
        color: white;
    }

    .btn-track.stop {
        background: #e74c3c;
        color: white;
    }

    .btn-track:hover {
        transform: scale(1.03);
    }

    .btn-refresh {
        padding: 10px 16px;
        border-radius: 8px;
        background: rgba(255,255,255,0.1);
        color: white;
        border: 1px solid rgba(255,255,255,0.2);
        font-weight: 600;
        font-size: 0.85rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }

    .btn-refresh:hover {
        background: rgba(255,255,255,0.15);
    }

    #liveRouteMap {
        width: 100%;
        height: calc(100% - 70px);
        min-height: 400px;
    }

    /* Tasks Panel */
    .tasks-panel {
        background: #1a2744;
        border-radius: 16px;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .tasks-header {
        padding: 20px;
        background: linear-gradient(135deg, #1a2744, #243656);
        border-bottom: 1px solid rgba(255,255,255,0.1);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .tasks-title {
        color: white;
        font-weight: 700;
        font-size: 1rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .tasks-title i {
        color: var(--gasgo-orange);
    }

    .tasks-count {
        background: var(--gasgo-blue);
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .tasks-list {
        flex: 1;
        overflow-y: auto;
        padding: 16px;
    }

    .task-card {
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 12px;
        transition: all 0.2s;
        cursor: pointer;
    }

    .task-card:hover {
        background: rgba(255,255,255,0.08);
        border-color: var(--gasgo-orange);
        transform: translateX(4px);
    }

    .task-card.active {
        border-color: var(--gasgo-orange);
        background: rgba(247, 148, 29, 0.1);
    }

    .task-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .task-order {
        color: white;
        font-weight: 700;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .task-order .order-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: var(--gasgo-orange);
    }

    .task-status {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .task-status.locating {
        background: rgba(39, 174, 96, 0.2);
        color: #27ae60;
    }

    .task-status.pending {
        background: rgba(241, 196, 15, 0.2);
        color: #f1c40f;
    }

    .task-status.delivering {
        background: rgba(52, 152, 219, 0.2);
        color: #3498db;
    }

    .task-customer {
        color: white;
        font-weight: 600;
        font-size: 0.88rem;
        margin-bottom: 6px;
    }

    .task-address {
        color: rgba(255,255,255,0.6);
        font-size: 0.78rem;
        margin-bottom: 8px;
        display: flex;
        align-items: flex-start;
        gap: 6px;
    }

    .task-address i {
        color: var(--gasgo-orange);
        margin-top: 2px;
    }

    .task-details {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 10px;
        border-top: 1px solid rgba(255,255,255,0.1);
    }

    .task-phone {
        color: var(--gasgo-blue);
        font-size: 0.8rem;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .task-phone i {
        color: #27ae60;
    }

    .task-amount {
        color: var(--gasgo-orange);
        font-weight: 700;
        font-size: 0.9rem;
    }

    .task-actions {
        display: flex;
        gap: 8px;
        margin-top: 12px;
    }

    .btn-locate {
        flex: 1;
        padding: 8px 12px;
        border-radius: 8px;
        background: var(--gasgo-orange);
        color: white;
        border: none;
        font-weight: 600;
        font-size: 0.78rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: all 0.2s;
    }

    .btn-locate:hover {
        background: #e68a1a;
        transform: scale(1.02);
    }

    .btn-navigate {
        padding: 8px 12px;
        border-radius: 8px;
        background: #27ae60;
        color: white;
        border: none;
        font-weight: 600;
        font-size: 0.78rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: all 0.2s;
    }

    .btn-navigate:hover {
        background: #229954;
    }

    .btn-call {
        padding: 8px 12px;
        border-radius: 8px;
        background: rgba(255,255,255,0.1);
        color: white;
        border: 1px solid rgba(255,255,255,0.2);
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-call:hover {
        background: rgba(255,255,255,0.15);
    }

    .btn-delivered {
        flex: 1;
        padding: 10px 12px;
        border-radius: 8px;
        background: #27ae60;
        color: white;
        border: none;
        font-weight: 700;
        font-size: 0.78rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: all 0.2s;
    }

    .btn-delivered:hover {
        background: #229954;
        transform: scale(1.02);
    }

    .btn-delivered:disabled {
        background: #95a5a6;
        cursor: not-allowed;
        transform: none;
    }

    .btn-view-details {
        padding: 10px 12px;
        border-radius: 8px;
        background: var(--gasgo-blue);
        color: white;
        border: none;
        font-weight: 600;
        font-size: 0.78rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-view-details:hover {
        background: #1555a0;
        color: white;
    }

    .task-actions-row {
        display: flex;
        gap: 8px;
        margin-top: 8px;
    }

    /* Footer */
    .tasks-footer {
        padding: 16px;
        border-top: 1px solid rgba(255,255,255,0.1);
    }

    .btn-back {
        width: 100%;
        padding: 14px;
        border-radius: 10px;
        background: rgba(255,255,255,0.1);
        color: white;
        border: 1px solid rgba(255,255,255,0.2);
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s;
    }

    .btn-back:hover {
        background: rgba(255,255,255,0.15);
    }

    /* Empty state */
    .empty-tasks {
        text-align: center;
        padding: 40px 20px;
        color: rgba(255,255,255,0.5);
    }

    .empty-tasks i {
        font-size: 3rem;
        margin-bottom: 16px;
        opacity: 0.5;
    }

    /* Tracking indicator */
    .tracking-indicator {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: rgba(39, 174, 96, 0.2);
        border-radius: 20px;
        color: #27ae60;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .tracking-indicator .pulse {
        width: 10px;
        height: 10px;
        background: #27ae60;
        border-radius: 50%;
        animation: pulse 1.5s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(1.2); }
    }

    /* Rider marker on map */
    .rider-marker-label {
        background: var(--gasgo-orange);
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
    }

    /* Route styling */
    .leaflet-routing-container {
        background: rgba(26, 39, 68, 0.95) !important;
        border-radius: 8px !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        color: white !important;
        max-height: 300px;
        overflow-y: auto;
    }

    .leaflet-routing-container h3 {
        color: var(--gasgo-orange) !important;
        padding: 10px !important;
        margin: 0 !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
    }

    .leaflet-routing-alt {
        background: rgba(255, 255, 255, 0.05) !important;
        color: white !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
    }

    .leaflet-routing-alt h4 {
        color: var(--gasgo-orange) !important;
    }

    .leaflet-routing-error {
        color: #e74c3c !important;
        padding: 10px !important;
    }

    /* Route line styling */
    .leaflet-routing-line {
        stroke: #f7941d !important;
        stroke-width: 5 !important;
        opacity: 0.8 !important;
    }

    .leaflet-routing-Alt {
        opacity: 0.5 !important;
    }

    /* Urgent Badge */
    .badge-urgent {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #e74c3c;
        color: white;
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        margin-right: 8px;
    }

    .task-status-badges {
        display: flex;
        align-items: center;
        gap: 6px;
    }
</style>
@endsection

@section('content')
<div class="route-map-container">
    <!-- Map Section -->
    <div class="map-section">
        <div class="map-header">
            <div>
                <div class="map-title">
                    <i class="fas fa-map-marked-alt"></i>
                    <span>Route Map</span>
                </div>
                <div class="map-subtitle">Track your assigned deliveries in real-time and optimize your routes</div>
            </div>
            <div class="map-actions">
                <button class="btn-track start" id="trackingBtn" onclick="toggleTracking()">
                    <i class="fas fa-play"></i>
                    <span>START TRACKING</span>
                </button>
                <button class="btn-refresh" onclick="refreshData()">
                    <i class="fas fa-sync-alt"></i>
                    <span>REFRESH DATA</span>
                </button>
            </div>
        </div>
        <div id="liveRouteMap"></div>
    </div>

    <!-- Tasks Panel -->
    <div class="tasks-panel">
        <div class="tasks-header">
            <div class="tasks-title">
                <i class="fas fa-clipboard-list"></i>
                <span>Assigned Tasks</span>
            </div>
            <span class="tasks-count" id="tasksCount">{{ count($activeDeliveries) }}</span>
        </div>

        <div class="tasks-list" id="tasksList">
            @forelse($activeDeliveries as $index => $delivery)
                <div class="task-card {{ $index === 0 ? 'active' : '' }}"
                     id="task-{{ $delivery->id }}"
                     data-lat="{{ $delivery->order->latitude ?? 0 }}"
                     data-lng="{{ $delivery->order->longitude ?? 0 }}"
                     data-delivery-id="{{ $delivery->id }}">
                    <div class="task-header">
                        <div class="task-order">
                            <span class="order-dot"></span>
                            #ORD-{{ $delivery->order->id }}
                        </div>
                        <div class="task-status-badges">
                            @if($delivery->order->is_urgent)
                                <span class="badge badge-urgent">
                                    <i class="fas fa-bolt"></i> URGENT
                                </span>
                            @endif
                            <span class="task-status {{ $delivery->status === 'out_for_delivery' ? 'delivering' : 'locating' }}">
                                <i class="fas fa-{{ $delivery->status === 'out_for_delivery' ? 'truck' : 'map-marker-alt' }} me-1"></i>
                                {{ $delivery->status === 'out_for_delivery' ? 'DELIVERING' : 'LOCATING' }}
                            </span>
                        </div>
                    </div>
                    <div class="task-customer">{{ $delivery->order->user->name }}</div>
                    <div class="task-address">
                        <i class="fas fa-map-pin"></i>
                        <span>{{ Str::limit($delivery->order->delivery_address, 50) }}</span>
                    </div>
                    <div class="task-details">
                        <div class="task-phone">
                            <i class="fas fa-phone"></i>
                            {{ $delivery->order->contact_number }}
                        </div>
                        <div class="task-fee" style="font-size:.82rem;color:#555;margin-top:4px;">
                            Delivery Fee: ₱{{ number_format($delivery->order->delivery_fee, 2) }}
                        </div>
                        <div class="task-amount">₱{{ number_format($delivery->order->total_amount, 2) }}</div>
                    </div>
                    <div class="task-actions">
                        <button type="button" class="btn-locate" data-action="locate">
                            <i class="fas fa-crosshairs"></i> LOCATE
                        </button>
                       
                        <a href="tel:{{ $delivery->order->contact_number }}" class="btn-call" data-action="call">
                            <i class="fas fa-phone"></i>
                        </a>
                    </div>
                    <!-- Delivery Action Buttons -->
                    <div class="task-actions-row">
                        <button type="button" class="btn-delivered" data-action="deliver" id="deliverBtn-{{ $delivery->id }}">
                            <i class="fas fa-check-circle"></i> MARK DELIVERED
                        </button>
                        <a href="{{ route('rider.delivery', $delivery->id) }}" class="btn-view-details" data-action="details">
                            <i class="fas fa-eye"></i> DETAILS
                        </a>
                    </div>
                </div>
            @empty
                <div class="empty-tasks">
                    <i class="fas fa-inbox d-block"></i>
                    <p>No assigned deliveries</p>
                    <small>Accept orders from the dashboard to see them here</small>
                </div>
            @endforelse
        </div>

        <div class="tasks-footer">
            <a href="{{ route('rider.dashboard') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                BACK TO DASHBOARD
            </a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.umd.js"></script>
<script src="{{ asset('js/leaflet-utils.js') }}"></script>
<script>
    // Helper function to show alerts
    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.style.cssText = `
            position: fixed;
            top: 80px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            padding: 16px 24px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            background: ${type === 'success' ? '#27ae60' : '#e74c3c'};
            color: white;
        `;
        alertDiv.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            ${message}
        `;
        document.body.appendChild(alertDiv);
        setTimeout(() => alertDiv.remove(), 3000);
    }

    let routeMap = null;
    let markers = [];
    let riderMarker = null;
    let polyline = null;
    let isTracking = false;
    let watchId = null;
    let riderPosition = null;

    // Initialize map on page load
    document.addEventListener('DOMContentLoaded', function() {
        initRouteMap();
        bindTaskActions();
        publishCurrentLocationOnce();
        // Auto-start tracking when page loads
        startTracking();
    });

    function bindTaskActions() {
        document.querySelectorAll('.task-card').forEach(function (task) {
            task.addEventListener('click', function () {
                const deliveryId = this.dataset.deliveryId;
                const lat = parseFloat(this.dataset.lat);
                const lng = parseFloat(this.dataset.lng);
                focusOnTask(deliveryId, lat, lng);
            });

            const locateBtn = task.querySelector('[data-action="locate"]');
            if (locateBtn) {
                locateBtn.addEventListener('click', function (event) {
                    event.stopPropagation();
                    const lat = parseFloat(task.dataset.lat);
                    const lng = parseFloat(task.dataset.lng);
                    locateOnMap(lat, lng);
                });
            }

            const navigateBtn = task.querySelector('[data-action="navigate"]');
            if (navigateBtn) {
                navigateBtn.addEventListener('click', function (event) {
                    event.stopPropagation();
                    const deliveryId = task.dataset.deliveryId;
                    // Redirect to full-screen navigation
                    window.location.href = `/rider/route/navigation/${deliveryId}`;
                });
            }

            const callBtn = task.querySelector('[data-action="call"]');
            if (callBtn) {
                callBtn.addEventListener('click', function (event) {
                    event.stopPropagation();
                });
            }

            const deliverBtn = task.querySelector('[data-action="deliver"]');
            if (deliverBtn) {
                deliverBtn.addEventListener('click', function (event) {
                    event.stopPropagation();
                    const deliveryId = task.dataset.deliveryId;
                    markAsDelivered(deliveryId);
                });
            }

            const detailsBtn = task.querySelector('[data-action="details"]');
            if (detailsBtn) {
                detailsBtn.addEventListener('click', function (event) {
                    event.stopPropagation();
                });
            }
        });
    }

    function initRouteMap() {
        // Get all task coordinates
        const tasks = document.querySelectorAll('.task-card');
        let coordinates = [];
        let allTasksMap = {};

        tasks.forEach(task => {
            const lat = parseFloat(task.dataset.lat);
            const lng = parseFloat(task.dataset.lng);
            if (lat && lng) {
                const coord = { lat, lng, id: task.dataset.deliveryId };
                coordinates.push(coord);
                allTasksMap[task.dataset.deliveryId] = coord;
            }
        });

        // Default center (Philippines)
        let centerLat = 14.5995;
        let centerLng = 120.9842;

        if (coordinates.length > 0) {
            centerLat = coordinates[0].lat;
            centerLng = coordinates[0].lng;
        }

        // Store all coordinates globally for reference
        window.allDeliveryCoordinates = allTasksMap;

        // Initialize map
        routeMap = initLeafletMap('liveRouteMap', centerLat, centerLng, 14);

        // Show ALL deliveries as numbered pins
        coordinates.forEach((coord, index) => {
            const marker = createNumberedMarker(coord.lat, coord.lng, index + 1, '#f7941d');
            marker.addTo(routeMap);
            marker.bindPopup(`<div style="padding:8px;text-align:center;"><strong>Stop ${index + 1}</strong><br><small>Click LOCATE to view route</small></div>`);
            marker.on('click', () => {
                focusOnTask(coord.id, coord.lat, coord.lng);
            });
            markers.push({ marker, id: coord.id, lat: coord.lat, lng: coord.lng });
        });

        // Draw delivery sequence line
        if (coordinates.length > 0) {
            drawDeliverySequenceLine(coordinates.map(c => [c.lat, c.lng]));
        }

        // Fit map to show all deliveries
        if (coordinates.length > 0) {
            const bounds = L.latLngBounds(coordinates.map(c => [c.lat, c.lng]));
            routeMap.fitBounds(bounds, { padding: [50, 50] });
        }
    }

    // Draw line connecting all delivery points in sequence using OSRM routing
    function drawDeliverySequenceLine(coords) {
        if (!routeMap || coords.length < 2) return;
        
        // Remove existing sequence line
        if (window.deliverySequenceLine) {
            routeMap.removeLayer(window.deliverySequenceLine);
        }
        
        // Build OSRM URL for all waypoints
        const waypoints = coords.map(coord => `${coord[1]},${coord[0]}`).join(';');
        const osmUrl = `https://router.project-osrm.org/route/v1/driving/${waypoints}?overview=full&geometries=geojson`;
        
        fetch(osmUrl, { method: 'GET', timeout: 8000 })
            .then(response => {
                if (!response.ok) throw new Error('Failed to fetch route');
                return response.json();
            })
            .then(data => {
                if (data.code === 'Ok' && data.routes && data.routes.length > 0) {
                    const route = data.routes[0];
                    const routeCoords = route.geometry.coordinates.map(coord => [coord[1], coord[0]]);
                    
                    // Draw road-based delivery sequence line
                    window.deliverySequenceLine = L.polyline(routeCoords, {
                        color: '#3498db',
                        weight: 3,
                        opacity: 0.5,
                        lineCap: 'round',
                        lineJoin: 'round'
                    }).addTo(routeMap);
                } else {
                    // Fallback: simple line between all stops
                    window.deliverySequenceLine = L.polyline(coords, {
                        color: '#95a5a6',
                        weight: 2,
                        opacity: 0.3,
                        dashArray: '5,5',
                        lineCap: 'round'
                    }).addTo(routeMap);
                }
            })
            .catch(error => {
                console.error('Delivery sequence routing error:', error);
                // Minimal fallback line
                window.deliverySequenceLine = L.polyline(coords, {
                    color: '#95a5a6',
                    weight: 2,
                    opacity: 0.2,
                    dashArray: '5,5',
                    lineCap: 'round'
                }).addTo(routeMap);
            });
    }

    function displaySingleDeliveryOnMap(delivery) {
        // Clear existing route lines and temp markers (but keep delivery pins)
        if (currentRouteLine) {
            routeMap.removeLayer(currentRouteLine);
            currentRouteLine = null;
        }
    }

    function toggleTracking() {
        const btn = document.getElementById('trackingBtn');

        if (isTracking) {
            // Stop tracking
            stopTracking();
            btn.classList.remove('stop');
            btn.classList.add('start');
            btn.innerHTML = '<i class="fas fa-play"></i><span>START TRACKING</span>';
        } else {
            // Start tracking
            const started = startTracking();
            if (started) {
                btn.classList.remove('start');
                btn.classList.add('stop');
                btn.innerHTML = '<i class="fas fa-stop"></i><span>STOP TRACKING</span>';
            }
        }
    }

    function startTracking() {
        if (!navigator.geolocation) {
            showAlert('error', 'Geolocation is not supported by your browser');
            return false;
        }

        isTracking = true;

        // Immediately get one precise location
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                riderPosition = { lat, lng };
                updateRiderMarker(lat, lng);
                sendLocationToServer(lat, lng);
                showAlert('success', 'Location tracking started!');
            },
            function(error) {
                console.error('Initial location error:', error);
                let errorMsg = 'Location tracking failed.';
                
                if (error.code === error.PERMISSION_DENIED) {
                    errorMsg = 'Location permission denied! Click the 🔒 lock icon in your address bar and enable location access.';
                } else if (error.code === error.POSITION_UNAVAILABLE) {
                    errorMsg = 'Location information unavailable. GPS may not be available.';
                } else if (error.code === error.TIMEOUT) {
                    errorMsg = 'Location request timed out. Please try again.';
                }
                
                showAlert('error', errorMsg);
                isTracking = false;
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );

        // Then watch for continuous updates
        watchId = navigator.geolocation.watchPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                riderPosition = { lat, lng };

                updateRiderMarker(lat, lng);
                sendLocationToServer(lat, lng);
            },
            function(error) {
                console.error('Geolocation watch error:', error);
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );

        return true;
    }

    function stopTracking() {
        isTracking = false;
        if (watchId) {
            navigator.geolocation.clearWatch(watchId);
            watchId = null;
        }
    }

    function updateRiderMarker(lat, lng) {
        if (!routeMap) return;

        if (riderMarker) {
            // Smoothly move existing marker
            smoothMoveMarker(riderMarker, lat, lng, 1000);
        } else {
            // Create new rider marker
            riderMarker = createPulsingMarker(lat, lng, '#27ae60');
            riderMarker.addTo(routeMap);
            riderMarker.bindPopup('<div style="padding:8px;"><b>Your Location</b></div>');
        }

        // Update polyline to include rider position using road routing
        if (markers.length > 0) {
            // Build OSRM waypoints: rider position + all delivery points
            const waypoints = `${lng},${lat};` + markers.map(m => `${m.lng},${m.lat}`).join(';');
            const osmUrl = `https://router.project-osrm.org/route/v1/driving/${waypoints}?overview=full&geometries=geojson`;
            
            fetch(osmUrl, { method: 'GET', timeout: 5000 })
                .then(response => {
                    if (!response.ok) throw new Error('Route fetch failed');
                    return response.json();
                })
                .then(data => {
                    if (data.code === 'Ok' && data.routes && data.routes.length > 0) {
                        const route = data.routes[0];
                        const pathCoords = route.geometry.coordinates.map(coord => [coord[1], coord[0]]);
                        
                        // Remove old rider-to-deliveries line
                        if (window.riderToDeliveriesLine) {
                            routeMap.removeLayer(window.riderToDeliveriesLine);
                        }
                        
                        // Draw road-based line from rider to deliveries
                        window.riderToDeliveriesLine = L.polyline(pathCoords, {
                            color: '#27ae60',
                            weight: 2,
                            opacity: 0.5,
                            lineCap: 'round',
                            lineJoin: 'round'
                        }).addTo(routeMap);
                    }
                })
                .catch(error => {
                    console.log('Rider routing update failed, using fallback');
                    // Fallback: simple direct line (minimal - only if routing fails)
                    const pathCoords = [[lat, lng]];
                    markers.forEach(m => pathCoords.push([m.lat, m.lng]));
                    
                    if (window.riderToDeliveriesLine) {
                        routeMap.removeLayer(window.riderToDeliveriesLine);
                    }
                    
                    window.riderToDeliveriesLine = L.polyline(pathCoords, {
                        color: '#95a5a6',
                        weight: 1,
                        opacity: 0.2,
                        dashArray: '3,3'
                    }).addTo(routeMap);
                });
        }
    }

    function publishCurrentLocationOnce() {
        if (!navigator.geolocation) {
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                riderPosition = { lat, lng };

                updateRiderMarker(lat, lng);
                sendLocationToServer(lat, lng);
            },
            function(error) {
                console.warn('Initial geolocation unavailable:', error);
                if (error.code === error.PERMISSION_DENIED) {
                    showAlert('error', 'Location permission denied. Tap the 🔒 lock icon in your address bar to enable location access.');
                }
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    }

    function sendLocationToServer(lat, lng) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        fetch('/rider/location/live', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ latitude: lat, longitude: lng })
        }).catch(err => console.error('Live location update failed:', err));
    }

    function refreshData() {
        const btn = document.querySelector('.btn-refresh');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>REFRESHING...</span>';

        // Reload page after short delay
        setTimeout(() => {
            location.reload();
        }, 500);
    }

    function focusOnTask(deliveryId, lat, lng) {
        // Update active state in sidebar
        document.querySelectorAll('.task-card').forEach(card => {
            card.classList.remove('active');
        });
        document.getElementById(`task-${deliveryId}`).classList.add('active');

        // Zoom to the selected delivery on the map (without removing other pins)
        if (routeMap && lat && lng) {
            routeMap.setView([lat, lng], 15, { animate: true });
        }
        
        // Draw route line from rider's current position to delivery
        if (riderPosition) {
            drawRoute(riderPosition.lat, riderPosition.lng, lat, lng);
        }
    }

    function locateOnMap(lat, lng) {
        // First check if we have rider location from tracking
        if (!riderPosition) {
            // Try to get current location immediately
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        riderPosition = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        };
                        drawRoute(riderPosition.lat, riderPosition.lng, lat, lng);
                    },
                    function(error) {
                        console.error('Geolocation error:', error.code, error.message);
                        
                        // Show which permission issue
                        let errorMsg = 'Enable location to view route';
                        if (error.code === 1) {
                            errorMsg = 'Location permission denied - check browser settings';
                        } else if (error.code === 2) {
                            errorMsg = 'Location unavailable - try again in a moment';
                        } else if (error.code === 3) {
                            errorMsg = 'Location request timed out - try again';
                        }
                        
                        showAlert('error', errorMsg);
                        
                        // Fallback: just show destination
                        if (routeMap && lat && lng) {
                            routeMap.setView([lat, lng], 16, { animate: true });
                        }
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 8000,
                        maximumAge: 0
                    }
                );
            } else {
                showAlert('error', 'Location services not available');
            }
        } else {
            // We already have rider position from tracking - draw route immediately
            drawRoute(riderPosition.lat, riderPosition.lng, lat, lng);
        }
    }

    // Global variable to store current route line
    let currentRouteLine = null;

    // Calculate distance between two coordinates using Haversine formula
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

    function drawRoute(fromLat, fromLng, toLat, toLng) {
        console.log('drawRoute called:', fromLat, fromLng, 'to', toLat, toLng);
        
        // Clear previous route line if exists
        if (currentRouteLine) {
            routeMap.removeLayer(currentRouteLine);
            currentRouteLine = null;
        }

        // Clear markers except initial ones
        markers.forEach(m => {
            if (m.marker && m.marker._isRouteMarker) {
                routeMap.removeLayer(m.marker);
            }
        });

        // Remove rider route marker if exists
        if (riderMarker && riderMarker._isRouteMarker) {
            routeMap.removeLayer(riderMarker);
        }

        // Request route from OSRM (Open Source Routing Machine)
        const osmUrl = `https://router.project-osrm.org/route/v1/driving/${fromLng},${fromLat};${toLng},${toLat}?overview=full&geometries=geojson`;
        console.log('OSRM URL:', osmUrl);

        fetch(osmUrl, { method: 'GET', timeout: 5000 })
            .then(response => {
                console.log('OSRM response status:', response.status);
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.json();
            })
            .then(data => {
                console.log('OSRM data:', data);
                
                if (data.code === 'Ok' && data.routes && data.routes.length > 0) {
                    const route = data.routes[0];
                    console.log('Route found, distance:', route.distance, 'duration:', route.duration);
                    
                    const coordinates = route.geometry.coordinates.map(coord => [coord[1], coord[0]]);
                    console.log('Polyline coordinates count:', coordinates.length);

                    // Draw route polyline with better styling - SOLID BLUE LINE
                    currentRouteLine = L.polyline(coordinates, {
                        color: '#2196f3',
                        weight: 5,
                        opacity: 0.8,
                        lineCap: 'round',
                        lineJoin: 'round'
                    }).addTo(routeMap);

                    // Add rider position marker (temporary for this route)
                    const riderRouteMarker = L.circleMarker([fromLat, fromLng], {
                        radius: 10,
                        fillColor: '#27ae60',
                        color: '#ffffff',
                        weight: 3,
                        opacity: 1,
                        fillOpacity: 0.9
                    }).addTo(routeMap);
                    riderRouteMarker._isRouteMarker = true;
                    riderRouteMarker.bindPopup('<div style="padding:8px; text-align:center;"><b>Your Location</b></div>');

                    // Add destination marker (temporary for this route)
                    const destMarker = L.circleMarker([toLat, toLng], {
                        radius: 10,
                        fillColor: '#e74c3c',
                        color: '#ffffff',
                        weight: 3,
                        opacity: 1,
                        fillOpacity: 0.9
                    }).addTo(routeMap);
                    destMarker._isRouteMarker = true;
                    destMarker.bindPopup('<div style="padding:8px; text-align:center;"><b>Delivery Location</b></div>');

                    // Fit bounds to show entire route
                    const bounds = L.latLngBounds([[fromLat, fromLng], [toLat, toLng]]);
                    routeMap.fitBounds(bounds, { padding: [100, 100], maxZoom: 16 });

                    // Show success message with distance and duration
                    const distance = (route.distance / 1000).toFixed(1);
                    const duration = Math.round(route.duration / 60);
                    showAlert('success', `Route: ${distance}km, ~${duration} mins`);
                } else {
                    console.error('No route found in OSRM response:', data);
                    drawSimpleDirectLine(fromLat, fromLng, toLat, toLng);
                }
            })
            .catch(error => {
                console.error('OSRM fetch error:', error);
                drawSimpleDirectLine(fromLat, fromLng, toLat, toLng);
            });
    }

    // Fallback: draw simple direct line when OSRM fails
    function drawSimpleDirectLine(fromLat, fromLng, toLat, toLng) {
        console.log('Drawing direct line fallback');
        showAlert('info', 'Showing direct path to destination');
        
        // Draw simple direct line from rider to destination - SOLID ORANGE LINE
        const directLine = L.polyline([[fromLat, fromLng], [toLat, toLng]], {
            color: '#f39c12',
            weight: 5,
            opacity: 0.8,
            lineCap: 'round',
            lineJoin: 'round'
        }).addTo(routeMap);
        currentRouteLine = directLine;

        // Add rider marker
        const riderRouteMarker = L.circleMarker([fromLat, fromLng], {
            radius: 10,
            fillColor: '#27ae60',
            color: '#ffffff',
            weight: 3,
            opacity: 1,
            fillOpacity: 0.9
        }).addTo(routeMap);
        riderRouteMarker._isRouteMarker = true;
        riderRouteMarker.bindPopup('<div style="padding:8px; text-align:center;"><b>Your Location</b></div>');

        // Add destination marker
        const destMarker = L.circleMarker([toLat, toLng], {
            radius: 10,
            fillColor: '#e74c3c',
            color: '#ffffff',
            weight: 3,
            opacity: 1,
            fillOpacity: 0.9
        }).addTo(routeMap);
        destMarker._isRouteMarker = true;
        destMarker.bindPopup('<div style="padding:8px; text-align:center;"><b>Delivery Location</b></div>');
        
        // Fit bounds
        const bounds = L.latLngBounds([[fromLat, fromLng], [toLat, toLng]]);
        routeMap.fitBounds(bounds, { padding: [100, 100], maxZoom: 16 });
    }

    let navigationControl = null;

    function navigateTo(lat, lng) {
        // Get rider's current position
        if (!riderPosition) {
            showAlert('error', 'Unable to get your current location. Please enable location services.');
            return;
        }

        // Remove existing route control if present
        if (navigationControl) {
            routeMap.removeControl(navigationControl);
        }

        // Create new routing control with actual road-based route
        navigationControl = L.Routing.control({
            waypoints: [
                L.latLng(riderPosition.latitude, riderPosition.longitude), // Rider's current position
                L.latLng(lat, lng) // Destination
            ],
            routeWhileDragging: false,
            showAlternatives: false,
            lineOptions: {
                styles: [
                    { color: '#f7941d', opacity: 0.8, weight: 6 } // Orange route line
                ]
            },
            altLineOptions: {
                styles: [
                    { color: 'gray', opacity: 0.5, weight: 3 }
                ]
            },
            createMarker: function(i, wp) {
                if (i === 0) {
                    // Start marker (rider position)
                    return L.marker(wp.latLng, {
                        icon: L.icon({
                            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
                            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                            iconSize: [25, 41],
                            iconAnchor: [12, 41],
                            popupAnchor: [1, -34]
                        }),
                        title: 'Your Location'
                    });
                } else {
                    // End marker (destination)
                    return L.marker(wp.latLng, {
                        icon: L.icon({
                            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                            iconSize: [25, 41],
                            iconAnchor: [12, 41],
                            popupAnchor: [1, -34]
                        }),
                        title: 'Delivery Address'
                    });
                }
            }
        }).addTo(routeMap);

        // Fit map to show entire route
        navigationControl.on('routesfound', function(e) {
            const bounds = e.routes[0].getBounds();
            routeMap.fitBounds(bounds, { padding: [50, 50] });
            showAlert('success', 'Route calculated! Follow the orange line to the delivery address.');
        });

        navigationControl.on('routingerror', function() {
            showAlert('error', 'Unable to calculate route. Please try again or open in Google Maps.');
            // Fallback to Google Maps
            const url = `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}&travelmode=driving`;
            window.open(url, '_blank');
        });
    }

    function markAsDelivered(deliveryId) {
        if (!confirm('Mark this delivery as completed?')) {
            return;
        }

        const btn = document.getElementById(`deliverBtn-${deliveryId}`);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        // Disable button and show loading
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> UPDATING...';

        fetch(`/rider/delivery/${deliveryId}/status`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ status: 'delivered' })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to update status');
            }
            return response.json();
        })
        .then(data => {
            // Show success
            btn.innerHTML = '<i class="fas fa-check"></i> DELIVERED!';
            btn.style.background = '#155724';

            // Remove task card after short delay
            setTimeout(() => {
                const taskCard = document.getElementById(`task-${deliveryId}`);
                if (taskCard) {
                    taskCard.style.transition = 'all 0.3s ease';
                    taskCard.style.opacity = '0';
                    taskCard.style.transform = 'translateX(100%)';
                    setTimeout(() => {
                        taskCard.remove();
                        updateTaskCount();

                        // Remove marker from map
                        const markerIndex = markers.findIndex(m => m.id == deliveryId);
                        if (markerIndex > -1) {
                            routeMap.removeLayer(markers[markerIndex].marker);
                            markers.splice(markerIndex, 1);
                        }
                    }, 300);
                }
            }, 1000);

            // Show alert
            if (typeof showAlert === 'function') {
                showAlert('success', 'Delivery marked as completed!');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle"></i> MARK DELIVERED';
            alert('Failed to update delivery status. Please try again.');
        });
    }

    function updateTaskCount() {
        const count = document.querySelectorAll('.task-card').length;
        document.getElementById('tasksCount').textContent = count;

        // Show empty state if no tasks
        if (count === 0) {
            document.getElementById('tasksList').innerHTML = `
                <div class="empty-tasks">
                    <i class="fas fa-check-circle d-block" style="color: #27ae60;"></i>
                    <p>All deliveries completed!</p>
                    <small>Go back to dashboard to accept more orders</small>
                </div>
            `;
        }
    }
</script>
@endsection
