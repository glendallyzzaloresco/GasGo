@extends('layouts.customer')

@section('title', 'GasGo - Checkout')

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
    .checkout-card {
        background: white; border-radius: 20px; padding: 30px;
        box-shadow: 0 8px 30px rgba(0,0,0,.08); margin-bottom: 24px;
    }
    .checkout-card h5 { font-weight: 700; color: var(--gasgo-blue); margin-bottom: 20px; }
    .checkout-card h5 i { color: var(--gasgo-orange); margin-right: 8px; }
    .form-label { font-weight: 600; font-size: .9rem; color: #555; }
    .payment-option {
        border: 2px solid #eee; border-radius: 14px; padding: 16px 20px; cursor: pointer;
        display: flex; align-items: center; gap: 14px; transition: all .25s;
    }
    .payment-option:hover, .payment-option.selected {
        border-color: var(--gasgo-orange); background: var(--gasgo-orange-light);
    }
    .payment-option .pay-icon {
        width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center;
        font-size: 1.2rem; color: white;
    }
    .payment-option .pay-icon.cash { background: linear-gradient(135deg, #27ae60, #2ecc71); }
    .payment-option .pay-icon.gcash { background: linear-gradient(135deg, #007dfe, #00b0ff); }

    /* Summary sidebar */
    .order-summary {
        background: white; border-radius: 20px; padding: 28px;
        box-shadow: 0 8px 30px rgba(0,0,0,.08); position: sticky; top: 100px;
    }
    .order-summary h5 { font-weight: 700; color: var(--gasgo-blue); border-bottom: 2px solid #f0f0f0; padding-bottom: 14px; }
    .summary-item { display: flex; justify-content: space-between; padding: 8px 0; font-size: .9rem; }
    .summary-item.total {
        font-size: 1.15rem; font-weight: 700; border-top: 2px solid var(--gasgo-orange);
        margin-top: 8px; padding-top: 14px;
    }
    .summary-item.total .val { color: var(--gasgo-orange); }
    .order-item-mini { display: flex; gap: 10px; padding: 8px 0; border-bottom: 1px solid #f8f8f8; }
    .order-item-mini img { width: 46px; height: 46px; border-radius: 8px; object-fit: cover; background: var(--gasgo-blue-light); }
    .order-item-mini .name { font-weight: 600; font-size: .85rem; color: #333; }
    .order-item-mini .qty { font-size: .78rem; color: #888; }

    /* Map styles */
    #checkoutMap { height: 300px; border-radius: 14px; border: 2px solid #eee; z-index: 1; }
    .map-search-wrap { position: relative; margin-bottom: 12px; }
    .map-search-wrap input {
        width: 100%; padding: 10px 42px 10px 14px; border: 2px solid #eee;
        border-radius: 12px; font-size: .9rem; outline: none; transition: border-color .25s;
    }
    .map-search-wrap input:focus { border-color: var(--gasgo-orange); }
    .map-search-wrap .search-btn {
        position: absolute; right: 6px; top: 50%; transform: translateY(-50%);
        background: var(--gasgo-orange); color: white; border: none; border-radius: 8px;
        width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;
        cursor: pointer; font-size: .85rem;
    }
    .map-hint { font-size: .78rem; color: #888; margin-top: 8px; }
    .map-search-results {
        position: absolute; top: 100%; left: 0; right: 0; background: white;
        border: 1px solid #eee; border-radius: 10px; max-height: 200px; overflow-y: auto;
        z-index: 10; display: none; box-shadow: 0 4px 16px rgba(0,0,0,.1);
    }
    .map-search-results .result-item {
        padding: 10px 14px; cursor: pointer; font-size: .85rem; border-bottom: 1px solid #f5f5f5;
    }
    .map-search-results .result-item:hover { background: var(--gasgo-orange-light); }
</style>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endsection

@section('content')
<section class="page-header">
    <div class="container text-center">
        <h1 class="fw-bold"><i class="fas fa-clipboard-check me-2"></i>Checkout</h1>
        <p class="mb-0" style="opacity:.9;">Complete your order details</p>
    </div>
</section>

<section class="container section-padding" style="position:relative;z-index:2;">
    <form action="{{ route('customer.order.store') }}" method="POST" id="checkoutForm">
        @csrf
        <div class="row g-4">
            <div class="col-lg-8">
                <!-- Delivery Address -->
                <div class="checkout-card">
                    <h5><i class="fas fa-map-marker-alt"></i>Delivery Address</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control form-control-gasgo" value="{{ Auth::user()->name }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Number</label>
                            <input type="text" class="form-control form-control-gasgo" name="contact_number" value="{{ old('contact_number', Auth::user()->phone) }}" placeholder="09XX XXX XXXX" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Complete Address</label>
                            <textarea class="form-control form-control-gasgo" name="delivery_address" rows="3" placeholder="House/Unit No., Street, Barangay, City/Municipality" required>{{ old('delivery_address', Auth::user()->address) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Delivery Notes (optional)</label>
                            <input type="text" class="form-control form-control-gasgo" name="notes" value="{{ old('notes') }}" placeholder="Landmark, gate color, etc.">
                        </div>
                        <div class="col-12 mt-2">
                            <label class="form-label"><i class="fas fa-map me-1" style="color:var(--gasgo-orange)"></i>Pin Your Location</label>
                            <div class="map-search-wrap">
                                <input type="text" id="mapSearch" placeholder="Search address or place..." autocomplete="off">
                                <button type="button" class="search-btn" onclick="searchAddress()"><i class="fas fa-search"></i></button>
                                <div class="map-search-results" id="searchResults"></div>
                            </div>
                            <div id="checkoutMap"></div>
                            <div class="d-flex justify-content-between align-items-center">
                                <p class="map-hint mb-0"><i class="fas fa-info-circle me-1"></i>Click on the map or drag the pin to set your exact delivery location</p>
                                <button type="button" class="btn btn-sm mt-1" style="background:var(--gasgo-blue);color:white;border-radius:8px;font-size:.78rem;" onclick="useMyLocation()"><i class="fas fa-crosshairs me-1"></i>Use My Location</button>
                            </div>
                            <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                            <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="checkout-card">
                    <h5><i class="fas fa-credit-card"></i>Payment Method</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="payment-option selected" onclick="selectPayment(this,'cash')">
                                <div class="pay-icon cash"><i class="fas fa-money-bill-wave"></i></div>
                                <div>
                                    <div class="fw-bold">Cash on Delivery</div>
                                    <small class="text-muted">Pay when you receive your order</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="payment-option" onclick="selectPayment(this,'gcash')">
                                <div class="pay-icon gcash"><i class="fas fa-mobile-alt"></i></div>
                                <div>
                                    <div class="fw-bold">GCash</div>
                                    <small class="text-muted">Pay via GCash e-wallet</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="payment_method" id="paymentMethod" value="cash">
                </div>
            </div>

            <!-- Order Summary Sidebar -->
            <div class="col-lg-4">
                <div class="order-summary">
                    <h5><i class="fas fa-receipt me-2"></i>Order Summary</h5>
                    @foreach ($cartItems as $item)
                    <div class="order-item-mini">
                        <img src="{{ $item->product->image ? asset($item->product->image) : '' }}" alt="{{ $item->product->name }}">
                        <div class="flex-grow-1">
                            <div class="name">{{ $item->product->name }}</div>
                            <div class="qty">Qty: {{ $item->quantity }} &times; ₱{{ number_format($item->product->price, 2) }}</div>
                        </div>
                        <div class="fw-bold" style="font-size:.9rem;">₱{{ number_format($item->product->price * $item->quantity, 2) }}</div>
                    </div>
                    @endforeach
                    @if (($rewardPreview['free_count'] ?? 0) > 0)
                        <div class="summary-item mt-2" style="color:#1e7e34;">
                            <span><i class="fas fa-gift me-1"></i>Free Tanks Reward</span>
                            <span>+{{ $rewardPreview['free_count'] }} tank(s)</span>
                        </div>
                    @endif
                    @if (!empty($rewardPreview['free_lines'] ?? []))
                        <div class="text-muted" style="font-size:.78rem;line-height:1.35;">
                            {{ implode(', ', $rewardPreview['free_lines']) }}
                        </div>
                    @endif
                    @if (($rewardPreview['discount_amount'] ?? 0) > 0)
                        <div class="summary-item" style="color:#1e7e34;">
                            <span><i class="fas fa-tag me-1"></i>Reward Discount</span>
                            <span>-₱{{ number_format($rewardPreview['discount_amount'], 2) }}</span>
                        </div>
                        <div class="text-muted" style="font-size:.78rem;line-height:1.35;">
                            Bonus tank stock is limited. Equivalent discount applied for: {{ implode(', ', $rewardPreview['discount_lines']) }}
                        </div>
                    @endif
                    <div class="summary-item mt-2"><span>Subtotal</span><span>₱{{ number_format($subtotal, 2) }}</span></div>
                    <div class="summary-item"><span>Delivery Fee</span><span>₱50.00</span></div>
                    <div class="summary-item total"><span>Total</span><span class="val">₱{{ number_format(($subtotal + 50) - ($rewardPreview['discount_amount'] ?? 0), 2) }}</span></div>
                    <button type="submit" class="btn btn-gasgo w-100 mt-3">
                        <i class="fas fa-check-circle me-2"></i>Place Order
                    </button>
                    <a href="{{ route('customer.cart') }}" class="btn btn-gasgo-outline w-100 mt-2" style="padding:12px;">
                        <i class="fas fa-arrow-left me-2"></i>Back to Cart
                    </a>
                </div>
            </div>
        </div>
    </form>
</section>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
function selectPayment(el, method) {
    document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('paymentMethod').value = method;
}

// --- Leaflet Map ---
const defaultLat = 16.0433;  // Calasiao, Pangasinan
const defaultLng = 120.3654;
let map, marker;

function initMap() {
    map = L.map('checkoutMap').setView([defaultLat, defaultLng], 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

    marker.on('dragend', function () {
        const pos = marker.getLatLng();
        updateLatLng(pos.lat, pos.lng);
        reverseGeocode(pos.lat, pos.lng);
    });

    map.on('click', function (e) {
        marker.setLatLng(e.latlng);
        updateLatLng(e.latlng.lat, e.latlng.lng);
        reverseGeocode(e.latlng.lat, e.latlng.lng);
    });

    // If address already has coordinates, use them
    const existingLat = document.getElementById('latitude').value;
    const existingLng = document.getElementById('longitude').value;
    if (existingLat && existingLng) {
        const lat = parseFloat(existingLat);
        const lng = parseFloat(existingLng);
        map.setView([lat, lng], 16);
        marker.setLatLng([lat, lng]);
    }
}

function updateLatLng(lat, lng) {
    document.getElementById('latitude').value = lat.toFixed(7);
    document.getElementById('longitude').value = lng.toFixed(7);
}

function reverseGeocode(lat, lng) {
    fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lng + '&zoom=18&addressdetails=1', {
        headers: { 'Accept-Language': 'en' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.display_name) {
            document.querySelector('[name="delivery_address"]').value = data.display_name;
        }
    })
    .catch(() => {});
}

let searchTimeout;
function searchAddress() {
    const q = document.getElementById('mapSearch').value.trim();
    if (q.length < 3) return;
    fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(q) + '&countrycodes=ph&limit=5', {
        headers: { 'Accept-Language': 'en' }
    })
    .then(r => r.json())
    .then(results => {
        const container = document.getElementById('searchResults');
        container.innerHTML = '';
        if (results.length === 0) {
            container.innerHTML = '<div class="result-item text-muted">No results found</div>';
            container.style.display = 'block';
            return;
        }
        results.forEach(r => {
            const div = document.createElement('div');
            div.className = 'result-item';
            div.textContent = r.display_name;
            div.addEventListener('click', () => {
                const lat = parseFloat(r.lat);
                const lng = parseFloat(r.lon);
                map.setView([lat, lng], 17);
                marker.setLatLng([lat, lng]);
                updateLatLng(lat, lng);
                document.querySelector('[name="delivery_address"]').value = r.display_name;
                document.getElementById('mapSearch').value = '';
                container.style.display = 'none';
            });
            container.appendChild(div);
        });
        container.style.display = 'block';
    })
    .catch(() => {});
}

// Live search as user types
document.addEventListener('DOMContentLoaded', function () {
    initMap();
    const searchInput = document.getElementById('mapSearch');
    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        if (this.value.trim().length >= 3) {
            searchTimeout = setTimeout(searchAddress, 400);
        } else {
            document.getElementById('searchResults').style.display = 'none';
        }
    });
    searchInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); searchAddress(); }
    });
    // Close results when clicking outside
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.map-search-wrap')) {
            document.getElementById('searchResults').style.display = 'none';
        }
    });
});

function useMyLocation() {
    if (!navigator.geolocation) { alert('Geolocation is not supported by your browser.'); return; }
    navigator.geolocation.getCurrentPosition(
        function (pos) {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            map.setView([lat, lng], 17);
            marker.setLatLng([lat, lng]);
            updateLatLng(lat, lng);
            reverseGeocode(lat, lng);
        },
        function () { alert('Unable to get your location. Please allow location access.'); },
        { enableHighAccuracy: true }
    );
}
</script>

@endsection
