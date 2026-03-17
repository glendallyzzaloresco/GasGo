@extends('layouts.rider')

@section('title', 'GasGo Rider - Live Route Map')
@section('page-title', 'Live Route Map')
@section('nav-route', 'active')

@section('rider-styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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
                     data-delivery-id="{{ $delivery->id }}"
                     onclick="focusOnTask({{ $delivery->id }}, {{ $delivery->order->latitude ?? 0 }}, {{ $delivery->order->longitude ?? 0 }})">
                    <div class="task-header">
                        <div class="task-order">
                            <span class="order-dot"></span>
                            #ORD-{{ $delivery->order->id }}
                        </div>
                        <span class="task-status {{ $delivery->status === 'out_for_delivery' ? 'delivering' : 'locating' }}">
                            <i class="fas fa-{{ $delivery->status === 'out_for_delivery' ? 'truck' : 'map-marker-alt' }} me-1"></i>
                            {{ $delivery->status === 'out_for_delivery' ? 'DELIVERING' : 'LOCATING' }}
                        </span>
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
                        <div class="task-amount">₱{{ number_format($delivery->order->total_amount, 2) }}</div>
                    </div>
                    <div class="task-actions">
                        <button class="btn-locate" onclick="event.stopPropagation(); locateOnMap({{ $delivery->order->latitude ?? 0 }}, {{ $delivery->order->longitude ?? 0 }})">
                            <i class="fas fa-crosshairs"></i> LOCATE
                        </button>
                        <button class="btn-navigate" onclick="event.stopPropagation(); navigateTo({{ $delivery->order->latitude ?? 0 }}, {{ $delivery->order->longitude ?? 0 }})">
                            <i class="fas fa-directions"></i>
                        </button>
                        <a href="tel:{{ $delivery->order->contact_number }}" class="btn-call" onclick="event.stopPropagation();">
                            <i class="fas fa-phone"></i>
                        </a>
                    </div>
                    <!-- Delivery Action Buttons -->
                    <div class="task-actions-row">
                        <button class="btn-delivered" onclick="event.stopPropagation(); markAsDelivered({{ $delivery->id }})" id="deliverBtn-{{ $delivery->id }}">
                            <i class="fas fa-check-circle"></i> MARK DELIVERED
                        </button>
                        <a href="{{ route('rider.delivery', $delivery->id) }}" class="btn-view-details" onclick="event.stopPropagation();">
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
    });

    function initRouteMap() {
        // Get all task coordinates
        const tasks = document.querySelectorAll('.task-card');
        let coordinates = [];

        tasks.forEach(task => {
            const lat = parseFloat(task.dataset.lat);
            const lng = parseFloat(task.dataset.lng);
            if (lat && lng) {
                coordinates.push({ lat, lng, id: task.dataset.deliveryId });
            }
        });

        // Default center (Philippines)
        let centerLat = 14.5995;
        let centerLng = 120.9842;

        if (coordinates.length > 0) {
            centerLat = coordinates[0].lat;
            centerLng = coordinates[0].lng;
        }

        // Initialize map
        routeMap = initLeafletMap('liveRouteMap', centerLat, centerLng, 12);

        // Add markers for each delivery
        coordinates.forEach((coord, index) => {
            const marker = createNumberedMarker(coord.lat, coord.lng, index + 1, '#f7941d');
            marker.addTo(routeMap);
            marker.bindPopup(`<div style="padding:8px;text-align:center;"><strong>Stop ${index + 1}</strong><br><small>Click to view details</small></div>`);
            marker.on('click', () => {
                focusOnTask(coord.id, coord.lat, coord.lng);
            });
            markers.push({ marker, id: coord.id, lat: coord.lat, lng: coord.lng });
        });

        // Draw route polyline if multiple points
        if (coordinates.length > 1) {
            const pathCoords = coordinates.map(c => [c.lat, c.lng]);
            polyline = drawRouteLine(routeMap, pathCoords, {
                color: '#3498db',
                weight: 4,
                opacity: 0.7
            });
        }

        // Fit bounds
        if (coordinates.length > 0) {
            const bounds = L.latLngBounds(coordinates.map(c => [c.lat, c.lng]));
            routeMap.fitBounds(bounds, { padding: [50, 50] });
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
            startTracking();
            btn.classList.remove('start');
            btn.classList.add('stop');
            btn.innerHTML = '<i class="fas fa-stop"></i><span>STOP TRACKING</span>';
        }
    }

    function startTracking() {
        if (!navigator.geolocation) {
            alert('Geolocation is not supported by your browser');
            return;
        }

        isTracking = true;

        watchId = navigator.geolocation.watchPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                riderPosition = { lat, lng };

                updateRiderMarker(lat, lng);
                sendLocationToServer(lat, lng);
            },
            function(error) {
                console.error('Geolocation error:', error);
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
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

        // Update polyline to include rider position
        if (markers.length > 0 && polyline) {
            const pathCoords = [[lat, lng]];
            markers.forEach(m => pathCoords.push([m.lat, m.lng]));
            polyline.setLatLngs(pathCoords);
        }
    }

    function sendLocationToServer(lat, lng) {
        // Get active delivery ID (first one)
        const activeTask = document.querySelector('.task-card.active');
        if (!activeTask) return;

        const deliveryId = activeTask.dataset.deliveryId;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        fetch(`/rider/delivery/${deliveryId}/location`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ latitude: lat, longitude: lng })
        }).catch(err => console.error('Location update failed:', err));
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
        // Update active state
        document.querySelectorAll('.task-card').forEach(card => {
            card.classList.remove('active');
        });
        document.getElementById(`task-${deliveryId}`).classList.add('active');

        // Pan map to location
        if (routeMap && lat && lng) {
            routeMap.setView([lat, lng], 15, { animate: true });
        }
    }

    function locateOnMap(lat, lng) {
        if (routeMap && lat && lng) {
            routeMap.setView([lat, lng], 16, { animate: true });

            // Flash effect on marker
            const marker = markers.find(m => m.lat === lat && m.lng === lng);
            if (marker) {
                marker.marker.openPopup();
            }
        }
    }

    function navigateTo(lat, lng) {
        // Open Google Maps navigation
        const url = `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}&travelmode=driving`;
        window.open(url, '_blank');
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
