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
 * Smoothly animates marker movement from current position to new position
 * @param {L.Marker} marker - Leaflet marker to animate
 * @param {number} newLat - Target latitude
 * @param {number} newLng - Target longitude
 * @param {number} duration - Animation duration in milliseconds (default: 2000)
 */
function smoothMoveMarker(marker, newLat, newLng, duration = 2000) {
    const startLatLng = marker.getLatLng();
    const startLat = startLatLng.lat;
    const startLng = startLatLng.lng;

    const startTime = Date.now();

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
    
    const rect = element.getBoundingClientRect();
    console.log(`Creating Leaflet map in element "${elementId}":`, {
        width: rect.width,
        height: rect.height,
        offsetWidth: element.offsetWidth,
        offsetHeight: element.offsetHeight
    });
    
    // Ensure element has visible dimensions
    if (element.offsetHeight === 0 || element.offsetWidth === 0) {
        console.warn('Warning: Map element has zero dimensions');
    }
    
    try {
        const map = L.map(elementId).setView([lat, lng], zoom);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19,
            minZoom: 3
        }).addTo(map);
        
        console.log('Leaflet map created successfully');
        return map;
    } catch (error) {
        console.error('Error creating Leaflet map:', error);
        return null;
    }
}
