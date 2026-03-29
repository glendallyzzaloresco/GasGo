@extends('layouts.rider')

@section('title', 'GasGo Rider - Navigation')
@section('page-title', 'Turn-by-Turn Navigation')

@section('rider-styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
<style>
    body {
        margin: 0;
        padding: 0;
        overflow: hidden;
    }

    .navigation-container {
        display: grid;
        grid-template-columns: 1fr 420px;
        height: 100vh;
        gap: 0;
    }

    @media (max-width: 768px) {
        .navigation-container {
            grid-template-columns: 1fr;
        }
    }

    /* Full-screen Map */
    #navigationMap {
        width: 100%;
        height: 100%;
        z-index: 1;
    }

    /* Navigation Panel */
    .navigation-panel {
        background: linear-gradient(135deg, #1a2744 0%, #243656 100%);
        border-left: 1px solid rgba(255, 255, 255, 0.1);
        display: flex;
        flex-direction: column;
        padding: 20px;
        overflow-y: auto;
        color: white;
    }

    .nav-header {
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid var(--gasgo-orange);
    }

    .nav-header h3 {
        margin: 0;
        color: var(--gasgo-orange);
        font-size: 1.3rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .nav-header small {
        display: block;
        opacity: 0.7;
        font-size: 0.85rem;
        margin-top: 5px;
    }

    /* Current Location */
    .current-location {
        background: rgba(39, 174, 96, 0.15);
        border: 2px solid #27ae60;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 15px;
    }

    .current-location h5 {
        margin: 0 0 8px 0;
        color: #27ae60;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .current-location p {
        margin: 0;
        font-size: 0.95rem;
        color: rgba(255, 255, 255, 0.9);
    }

    /* Distance Info */
    .distance-info {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-bottom: 20px;
    }

    .distance-box {
        background: rgba(247, 148, 29, 0.1);
        border: 2px solid var(--gasgo-orange);
        border-radius: 8px;
        padding: 12px;
        text-align: center;
    }

    .distance-box .value {
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--gasgo-orange);
    }

    .distance-box .label {
        font-size: 0.75rem;
        opacity: 0.8;
        margin-top: 4px;
        text-transform: uppercase;
    }

    /* Next Turn/Directions */
    .next-turn {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 20px;
    }

    .next-turn h5 {
        margin: 0 0 10px 0;
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .turn-instruction {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .turn-icon {
        font-size: 2.5rem;
        color: var(--gasgo-orange);
        min-width: 50px;
        text-align: center;
    }

    .turn-text {
        flex: 1;
    }

    .turn-text .direction {
        font-size: 1.1rem;
        font-weight: 700;
        color: white;
        line-height: 1.3;
    }

    .turn-text .distance-to-turn {
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.7);
        margin-top: 4px;
    }

    /* Route Steps */
    .route-steps {
        background: rgba(255, 255, 255, 0.03);
        border-radius: 8px;
        padding: 10px 0;
        flex: 1;
        overflow-y: auto;
        margin-bottom: 15px;
    }

    .step {
        padding: 10px 12px;
        border-left: 3px solid rgba(247, 148, 29, 0.3);
        margin: 5px 0;
        font-size: 0.85rem;
        line-height: 1.4;
    }

    .step.active {
        background: rgba(247, 148, 29, 0.15);
        border-left-color: var(--gasgo-orange);
        color: var(--gasgo-orange);
        font-weight: 600;
    }

    .step.completed {
        opacity: 0.5;
        color: #27ae60;
    }

    /* Action Buttons */
    .nav-actions {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: auto;
    }

    .btn-arrival {
        background: #27ae60;
        color: white;
        border: none;
        padding: 14px 16px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-arrival:hover {
        background: #229954;
        transform: scale(1.02);
    }

    .btn-back {
        background: rgba(255, 255, 255, 0.1);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.2);
        padding: 12px 16px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-back:hover {
        background: rgba(255, 255, 255, 0.15);
    }

    /* Status Badge */
    .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        background: rgba(247, 148, 29, 0.2);
        color: var(--gasgo-orange);
    }

    /* Mobile adjustments */
    @media (max-width: 768px) {
        .navigation-container {
            grid-template-columns: 1fr;
        }

        .navigation-panel {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 45%;
            border-left: none;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            z-index: 100;
            border-radius: 20px 20px 0 0;
        }

        #navigationMap {
            height: 55%;
        }
    }
</style>
@endsection

@section('content')
<div class="navigation-container">
    <!-- Full-Screen Map -->
    <div id="navigationMap"></div>

    <!-- Navigation Panel -->
    <div class="navigation-panel">
        <!-- Header with Delivery Info -->
        <div class="nav-header">
            <h3>
                <i class="fas fa-truck-moving"></i>
                Navigating to #{{ str_pad($delivery->id, 3, '0', STR_PAD_LEFT) }}
            </h3>
            <small>
                <i class="fas fa-user me-1"></i>
                {{ $delivery->order->user->name }}
            </small>
        </div>

        <!-- Current Location (Customer Address) -->
        <div class="current-location">
            <h5><i class="fas fa-location-dot me-1"></i>Destination</h5>
            <p>{{ Str::limit($delivery->order->delivery_address, 50) }}</p>
        </div>

        <!-- Distance Information -->
        <div class="distance-info">
            <div class="distance-box">
                <div class="value" id="distanceToTurn">—</div>
                <div class="label">To Next Turn</div>
            </div>
            <div class="distance-box">
                <div class="value" id="distanceToDestination">—</div>
                <div class="label">To Destination</div>
            </div>
        </div>

        <!-- Next Turn Instructions -->
        <div class="next-turn">
            <h5>Next Instruction</h5>
            <div class="turn-instruction">
                <div class="turn-icon" id="turnIcon">
                    <i class="fas fa-arrow-right"></i>
                </div>
                <div class="turn-text">
                    <div class="direction" id="turnDirection">Calculating route...</div>
                    <div class="distance-to-turn" id="turnDistance"></div>
                </div>
            </div>
        </div>

        <!-- Route Steps List -->
        <div class="route-steps" id="routeSteps">
            <div class="step active">
                <i class="fas fa-spinner fa-spin me-2"></i>Loading route details...
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="nav-actions">
            <button type="button" class="btn-arrival" id="arrivedBtn">
                <i class="fas fa-flag-checkered"></i>
                I've Arrived!
            </button>
            <a href="{{ route('rider.route.map') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                Back to Live Map
            </a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.umd.js"></script>
<script>
    const deliveryId = {{ $delivery->id }};
    const destinationLat = {{ $delivery->order->latitude }};
    const destinationLng = {{ $delivery->order->longitude }};
    const customerName = "{{ $delivery->order->user->name }}";
    const address = "{{ $delivery->order->delivery_address }}";

    let navigationMap = null;
    let navigationControl = null;
    let riderPosition = null;
    let currentRoute = null;

    // Initialize navigation on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize map centered on destination
        navigationMap = L.map('navigationMap').setView([destinationLat, destinationLng], 16);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(navigationMap);

        // Get rider's location and start navigation
        getLocationAndNavigate();

        // Update location every 10 seconds
        setInterval(updateRiderLocation, 10000);

        // "I've Arrived" button handler
        document.getElementById('arrivedBtn').addEventListener('click', markAsArrived);
    });

    function getLocationAndNavigate() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    riderPosition = {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude
                    };
                    startNavigation();
                },
                function(error) {
                    console.error('Geolocation error:', error);
                    let errorMsg = 'Unable to get your location.';
                    
                    if (error.code === error.PERMISSION_DENIED) {
                        errorMsg = 'Location permission denied. Please:\n1. Click the location icon in your address bar\n2. Select "Allow" for location access\n3. Refresh the page';
                        showError(errorMsg);
                    } else if (error.code === error.POSITION_UNAVAILABLE) {
                        errorMsg = 'Location information is unavailable. Starting from destination instead.';
                        // Use destination as starting point
                        riderPosition = {
                            latitude: destinationLat,
                            longitude: destinationLng
                        };
                        startNavigation();
                    } else if (error.code === error.TIMEOUT) {
                        errorMsg = 'Location request timed out. Starting from destination instead.';
                        riderPosition = {
                            latitude: destinationLat,
                            longitude: destinationLng
                        };
                        startNavigation();
                    } else {
                        showError(errorMsg);
                    }
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        } else {
            showError('Geolocation is not supported by your browser.');
        }
    }

    function startNavigation() {
        if (!riderPosition) return;

        // Remove existing route if present
        if (navigationControl) {
            navigationMap.removeControl(navigationControl);
        }

        // Create routing control
        navigationControl = L.Routing.control({
            waypoints: [
                L.latLng(riderPosition.latitude, riderPosition.longitude),
                L.latLng(destinationLat, destinationLng)
            ],
            routeWhileDragging: true,
            showAlternatives: false,
            lineOptions: {
                styles: [
                    { color: '#f7941d', opacity: 0.85, weight: 7 }
                ]
            },
            createMarker: function(i, wp) {
                if (i === 0) {
                    return L.marker(wp.latLng, {
                        icon: L.icon({
                            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
                            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                            iconSize: [30, 45],
                            iconAnchor: [15, 45],
                            popupAnchor: [1, -34]
                        }),
                        title: 'Your Location'
                    }).bindPopup('📍 Your Location');
                } else {
                    return L.marker(wp.latLng, {
                        icon: L.icon({
                            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                            iconSize: [30, 45],
                            iconAnchor: [15, 45],
                            popupAnchor: [1, -34]
                        }),
                        title: 'Delivery Address'
                    }).bindPopup('📍 ' + customerName);
                }
            }
        }).addTo(navigationMap);

        navigationControl.on('routesfound', function(e) {
            currentRoute = e.routes[0];
            updateRouteDisplay();
            navigationMap.fitBounds(currentRoute.getBounds(), { padding: [100, 100] });
        });

        navigationControl.on('routingerror', function(e) {
            showError('Unable to calculate route. Please check your connection.');
        });
    }

    function updateRouteDisplay() {
        if (!currentRoute) return;

        const instructions = currentRoute.instructions;
        const summary = currentRoute.summary;

        // Update distances
        document.getElementById('distanceToDestination').textContent = 
            (summary.totalDistance / 1000).toFixed(1) + ' km';

        // Display first instruction
        if (instructions.length > 0) {
            const firstStep = instructions[0];
            updateTurnDisplay(firstStep);
        }

        // Display all steps
        const stepsHtml = instructions.map((instruction, index) => {
            const distance = instruction.distance;
            const distText = distance > 1000 ? 
                (distance / 1000).toFixed(1) + ' km' : 
                distance + ' m';
            
            return `
                <div class="step ${index === 0 ? 'active' : ''}">
                    <strong>${instruction.text}</strong>
                    <div style="font-size: 0.8rem; opacity: 0.8; margin-top: 4px;">${distText}</div>
                </div>
            `;
        }).join('');

        document.getElementById('routeSteps').innerHTML = stepsHtml;
    }

    function updateTurnDisplay(instruction) {
        const distText = instruction.distance > 1000 ? 
            (instruction.distance / 1000).toFixed(1) + ' km' : 
            instruction.distance + ' m';

        document.getElementById('turnDirection').textContent = instruction.text;
        document.getElementById('distanceToTurn').textContent = distText;
        
        // Update turn icon based on instruction type
        const iconElement = document.getElementById('turnIcon');
        const iconMap = {
            'TurnLeft': 'fa-turn-left',
            'TurnRight': 'fa-turn-right',
            'TurnBackLeft': 'fa-turn-left',
            'TurnBackRight': 'fa-turn-right',
            'Straight': 'fa-arrow-up',
            'SlightLeft': 'fa-arrow-left',
            'SlightRight': 'fa-arrow-right',
            'Merge': 'fa-arrow-right',
            'Roundabout': 'fa-circle'
        };

        const iconClass = iconMap[instruction.type] || 'fa-arrow-right';
        iconElement.innerHTML = `<i class="fas ${iconClass}"></i>`;
    }

    function updateRiderLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    riderPosition = {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude
                    };
                    // Optionally restart navigation with updated position
                    startNavigation();
                }
            );
        }
    }

    function markAsArrived() {
        const btn = document.getElementById('arrivedBtn');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        if (!csrfToken) {
            showError('Security token not found');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Confirming...';

        fetch(`/rider/delivery/${deliveryId}/status`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ status: 'delivered' })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess('Delivery marked as completed! ✓');
                setTimeout(() => {
                    window.location.href = '{{ route('rider.route.map') }}';
                }, 1500);
            } else {
                showError(data.message || 'Failed to mark as delivered');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-flag-checkered"></i> I\'ve Arrived!';
            }
        })
        .catch(error => {
            showError('An error occurred. Please try again.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-flag-checkered"></i> I\'ve Arrived!';
        });
    }

    function showError(message) {
        const alert = document.createElement('div');
        alert.style.cssText = `
            position: fixed;
            top: 20px;
            left: 20px;
            background: #e74c3c;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            z-index: 10000;
            font-weight: 600;
        `;
        alert.innerHTML = `<i class="fas fa-exclamation-circle me-2"></i>${message}`;
        document.body.appendChild(alert);
        setTimeout(() => alert.remove(), 4000);
    }

    function showSuccess(message) {
        const alert = document.createElement('div');
        alert.style.cssText = `
            position: fixed;
            top: 20px;
            left: 20px;
            background: #27ae60;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            z-index: 10000;
            font-weight: 600;
        `;
        alert.innerHTML = `<i class="fas fa-check-circle me-2"></i>${message}`;
        document.body.appendChild(alert);
        setTimeout(() => alert.remove(), 3000);
    }
</script>
@endsection
