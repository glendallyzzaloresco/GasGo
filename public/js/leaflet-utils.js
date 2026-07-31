/**
 * Leaflet Utilities for GASGOProject
 * Reusable functions for map markers, animations, and routing
 */

/**
 * Creates a custom marker with specified options
 * @param {number} lat - Latitude
 * @param {number} lng - Longitude
 * @param {Object} options - { color, iconType, size }
 * @returns {L.Marker} Leaflet marker
 */
function createCustomMarker(lat, lng, options = {}) {
    const defaults = {
        color: '#2196f3',
        iconType: 'circle',
        size: 20
    };
    const opts = { ...defaults, ...options };

    const icon = L.divIcon({
        className: 'leaflet-custom-marker',
        html: `<div style="
            width: ${opts.size}px;
            height: ${opts.size}px;
            background-color: ${opts.color};
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.4);
        "></div>`,
        iconSize: [opts.size, opts.size],
        iconAnchor: [opts.size / 2, opts.size / 2]
    });

    return L.marker([lat, lng], { icon: icon });
}

/**
 * Creates an animated pulsing marker (for rider location)
 * @param {number} lat - Latitude
 * @param {number} lng - Longitude
 * @param {string} color - Hex color code
 * @returns {L.Marker} Leaflet marker with pulse animation
 */
function createPulsingMarker(lat, lng, color = '#f7941d') {
    const icon = L.divIcon({
        className: 'leaflet-pulsing-marker-container',
        html: `
            <div class="leaflet-pulsing-marker" style="
                width: 24px;
                height: 24px;
                background-color: ${color};
                border-radius: 50%;
                border: 3px solid white;
                box-shadow: 0 2px 10px rgba(0,0,0,0.4);
                position: relative;
                z-index: 1000;
            ">
                <div class="pulse-ring" style="
                    position: absolute;
                    width: 100%;
                    height: 100%;
                    border-radius: 50%;
                    background-color: ${color};
                    opacity: 0;
                    top: 0;
                    left: 0;
                    animation: pulse-animation 2s infinite;
                "></div>
            </div>
            <style>
                @keyframes pulse-animation {
                    0% {
                        transform: scale(1);
                        opacity: 0.7;
                    }
                    50% {
                        transform: scale(1.8);
                        opacity: 0.3;
                    }
                    100% {
                        transform: scale(2.5);
                        opacity: 0;
                    }
                }
            </style>
        `,
        iconSize: [24, 24],
        iconAnchor: [12, 12]
    });

    return L.marker([lat, lng], { icon: icon });
}

/**
 * Creates a numbered marker for waypoints
 * @param {number} lat - Latitude
 * @param {number} lng - Longitude
 * @param {number|string} number - Number or label to display
 * @param {string} color - Background color
 * @returns {L.Marker} Leaflet marker
 */
function createNumberedMarker(lat, lng, number, color = '#2196f3') {
    const icon = L.divIcon({
        className: 'leaflet-numbered-marker-container',
        html: `
            <div class="leaflet-numbered-marker" style="
                width: 36px;
                height: 36px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-weight: bold;
                font-size: 14px;
                background-color: ${color};
                border: 3px solid white;
                box-shadow: 0 2px 8px rgba(0,0,0,0.3);
            ">${number}</div>
        `,
        iconSize: [36, 36],
        iconAnchor: [18, 18]
    });

    return L.marker([lat, lng], { icon: icon });
}

/**
 * Draws a polyline connecting multiple coordinates
 * @param {L.Map} map - Leaflet map instance
 * @param {Array} coordinates - Array of [lat, lng] pairs
 * @param {Object} options - { color, weight, opacity }
 * @returns {L.Polyline} Leaflet polyline
 */
function drawRouteLine(map, coordinates, options = {}) {
    const defaults = {
        color: '#2196f3',
        weight: 3,
        opacity: 0.6,
        smoothFactor: 1
    };
    const opts = { ...defaults, ...options };

    const polyline = L.polyline(coordinates, opts);
    polyline.addTo(map);

    return polyline;
}

/**
 * Fits map bounds to show all markers
 * @param {L.Map} map - Leaflet map instance
 * @param {Array} markers - Array of Leaflet markers
 * @param {number} padding - Padding in pixels (default: 50)
 */
function fitBoundsToMarkers(map, markers, padding = 50) {
    if (!markers || markers.length === 0) return;

    const group = L.featureGroup(markers);
    map.fitBounds(group.getBounds(), { padding: [padding, padding] });
}

/**
 * Calculate distance between two coordinates using Haversine formula
 * @param {number} lat1 - Latitude of point 1
 * @param {number} lng1 - Longitude of point 1
 * @param {number} lat2 - Latitude of point 2
 * @param {number} lng2 - Longitude of point 2
 * @returns {number} Distance in kilometers
 */
function calculateDistance(lat1, lng1, lat2, lng2) {
    const R = 6371; // Earth's radius in kilometers
    const dLat = toRadians(lat2 - lat1);
    const dLon = toRadians(lng2 - lng1);

    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
              Math.cos(toRadians(lat1)) * Math.cos(toRadians(lat2)) *
              Math.sin(dLon / 2) * Math.sin(dLon / 2);

    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    const distance = R * c;

    return distance;
}

/**
 * Convert degrees to radians
 * @param {number} degrees
 * @returns {number} Radians
 */
function toRadians(degrees) {
    return degrees * (Math.PI / 180);
}

/**
 * Calculate compass bearing angle in degrees (0-360) between two coordinates
 * @param {number} lat1 
 * @param {number} lng1 
 * @param {number} lat2 
 * @param {number} lng2 
 * @returns {number} Bearing angle in degrees
 */
function calculateBearing(lat1, lng1, lat2, lng2) {
    const dLng = toRadians(lng2 - lng1);
    const rLat1 = toRadians(lat1);
    const rLat2 = toRadians(lat2);
    const y = Math.sin(dLng) * Math.cos(rLat2);
    const x = Math.cos(rLat1) * Math.sin(rLat2) - Math.sin(rLat1) * Math.cos(rLat2) * Math.cos(dLng);
    let brng = Math.atan2(y, x);
    brng = (brng * 180 / Math.PI + 360) % 360;
    return brng;
}

/**
 * Creates a Foodpanda-style 3D motorbike rider marker with direction rotation
 * @param {number} lat - Latitude
 * @param {number} lng - Longitude
 * @param {number} heading - Heading angle in degrees (default: 0)
 * @returns {L.Marker} Leaflet marker with rotated motorbike icon
 */
function createRiderMotorbikeMarker(lat, lng, heading = 0) {
    const icon = L.divIcon({
        className: 'leaflet-rider-motorbike-container',
        html: `
            <div class="rider-marker-wrapper" style="position: relative; width: 46px; height: 46px;">
                <div class="pulse-ring" style="
                    position: absolute;
                    width: 100%;
                    height: 100%;
                    border-radius: 50%;
                    background: rgba(39, 174, 96, 0.35);
                    animation: rider-pulse 2s infinite;
                "></div>
                <div class="rider-bike-icon" style="
                    position: absolute;
                    top: 3px;
                    left: 3px;
                    width: 40px;
                    height: 40px;
                    background: linear-gradient(135deg, #27ae60, #1e8449);
                    border-radius: 50%;
                    border: 3px solid #ffffff;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.35);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: white;
                    font-size: 18px;
                    transform: rotate(${heading}deg);
                    transition: transform 0.4s ease-out;
                ">
                    <i class="fas fa-motorcycle"></i>
                </div>
            </div>
            <style>
                @keyframes rider-pulse {
                    0% { transform: scale(1); opacity: 0.8; }
                    100% { transform: scale(2.2); opacity: 0; }
                }
            </style>
        `,
        iconSize: [46, 46],
        iconAnchor: [23, 23]
    });

    return L.marker([lat, lng], { icon: icon });
}

/**
 * Smoothly animates marker movement from current position to new position with rotation
 * @param {L.Marker} marker - Leaflet marker to animate
 * @param {number} newLat - Target latitude
 * @param {number} newLng - Target longitude
 * @param {number} duration - Animation duration in milliseconds (default: 1200)
 * @param {number|null} newHeading - Optional target heading rotation angle in degrees
 */
function smoothMoveMarker(marker, newLat, newLng, duration = 1200, newHeading = null) {
    const startLatLng = marker.getLatLng();
    const startLat = startLatLng.lat;
    const startLng = startLatLng.lng;

    const startTime = Date.now();

    // Rotate motorbike icon if heading is provided
    const markerElem = marker.getElement();
    if (markerElem && newHeading !== null && newHeading !== undefined) {
        const bikeIcon = markerElem.querySelector('.rider-bike-icon');
        if (bikeIcon) {
            bikeIcon.style.transform = `rotate(${newHeading}deg)`;
        }
    }

    function animate() {
        const elapsed = Date.now() - startTime;
        const progress = Math.min(elapsed / duration, 1);

        // Easing function (ease-out cubic)
        const eased = 1 - Math.pow(1 - progress, 3);

        const currentLat = startLat + (newLat - startLat) * eased;
        const currentLng = startLng + (newLng - startLng) * eased;

        marker.setLatLng([currentLat, currentLng]);

        if (progress < 1) {
            requestAnimationFrame(animate);
        }
    }

    animate();
}

/**
 * Fetch route from OSRM routing service
 * @param {Array} waypoints - Array of [lng, lat] coordinate pairs (note: OSRM uses lng,lat order!)
 * @returns {Promise<Object>} Route data with coordinates and distance
 */
async function fetchOSRMRoute(waypoints) {
    if (!waypoints || waypoints.length < 2) {
        throw new Error('At least 2 waypoints required for routing');
    }

    // Build OSRM coordinates string: lng,lat;lng,lat;...
    const coords = waypoints.map(w => `${w[0]},${w[1]}`).join(';');
    const url = `https://router.project-osrm.org/route/v1/driving/${coords}?overview=full&geometries=geojson`;

    try {
        const response = await fetch(url);
        const data = await response.json();

        if (data.code !== 'Ok') {
            throw new Error('OSRM routing failed: ' + data.message);
        }

        const route = data.routes[0];

        // Convert coordinates from [lng, lat] to [lat, lng] for Leaflet
        const leafletCoordinates = route.geometry.coordinates.map(coord => [coord[1], coord[0]]);

        return {
            coordinates: leafletCoordinates,
            distance: route.distance / 1000, // Convert meters to kilometers
            duration: route.duration / 60 // Convert seconds to minutes
        };
    } catch (error) {
        console.error('OSRM fetch error:', error);
        throw error;
    }
}

/**
 * Initialize OpenStreetMap base layer
 * @param {string} elementId - ID of map container element
 * @param {number} lat - Initial center latitude
 * @param {number} lng - Initial center longitude
 * @param {number} zoom - Initial zoom level (default: 13)
 * @returns {L.Map} Leaflet map instance
 */
function initLeafletMap(elementId, lat, lng, zoom = 13) {
    const element = document.getElementById(elementId);

    if (!element) {
        console.error(`Map element with ID "${elementId}" not found`);
        return null;
    }

    // Force explicit pixel dimensions so Leaflet can always calculate tile positions
    element.style.width  = element.style.width  || '100%';
    element.style.height = '500px';
    element.style.display = 'block';

    console.log(`Creating Leaflet map in #${elementId}: ${element.offsetWidth}×${element.offsetHeight}px`);

    try {
        const map = L.map(elementId, { preferCanvas: false }).setView([lat, lng], zoom);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19,
            minZoom: 3
        }).addTo(map);

        // Force tile refresh after a short delay
        setTimeout(() => { map.invalidateSize(true); }, 300);

        console.log('Leaflet map created successfully');
        return map;
    } catch (error) {
        console.error('Error creating Leaflet map:', error);
        return null;
    }
}
