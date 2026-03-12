@extends('layouts.rider')

@section('title', 'GasGo Rider - Dashboard')
@section('nav-dashboard', 'active')

@section('content')
<!-- Status Toggle -->
<div class="r-card text-center">
    <p class="text-muted mb-2" style="font-size:.85rem;">Your availability status</p>
    <div class="status-toggle">
        <button class="status-btn active-available" onclick="setStatus(this,'available')"><i class="fas fa-check-circle me-1"></i>Available</button>
        <button class="status-btn" onclick="setStatus(this,'busy')"><i class="fas fa-clock me-1"></i>Busy</button>
        <button class="status-btn" onclick="setStatus(this,'offline')"><i class="fas fa-moon me-1"></i>Offline</button>
    </div>
</div>

<!-- Today's Stats -->
<div class="row g-3 mb-3">
    <div class="col-4">
        <div class="r-card text-center" style="padding:14px;">
            <div style="font-size:1.5rem;font-weight:800;color:var(--gasgo-blue);">4</div>
            <div style="font-size:.72rem;color:#888;">Deliveries</div>
        </div>
    </div>
    <div class="col-4">
        <div class="r-card text-center" style="padding:14px;">
            <div style="font-size:1.5rem;font-weight:800;color:var(--gasgo-orange);">₱320</div>
            <div style="font-size:.72rem;color:#888;">Earnings</div>
        </div>
    </div>
    <div class="col-4">
        <div class="r-card text-center" style="padding:14px;">
            <div style="font-size:1.5rem;font-weight:800;color:#27ae60;"><i class="fas fa-star" style="font-size:1rem;"></i> 4.8</div>
            <div style="font-size:.72rem;color:#888;">Rating</div>
        </div>
    </div>
</div>

<!-- Assigned Deliveries -->
<h6 class="fw-bold mb-3" style="color:var(--gasgo-blue);"><i class="fas fa-list me-2" style="color:var(--gasgo-orange);"></i>Assigned Deliveries</h6>

<div class="r-card" style="border-left:4px solid var(--gasgo-orange);">
    <div class="d-flex justify-content-between align-items-start mb-2">
        <div>
            <div class="fw-bold" style="color:var(--gasgo-blue);">#GG-00009</div>
            <small class="text-muted">Assigned 15 mins ago</small>
        </div>
        <span class="badge-status badge-assigned">Assigned</span>
    </div>
    <div class="mb-2" style="font-size:.88rem;">
        <i class="fas fa-user me-2" style="color:var(--gasgo-blue);"></i><strong>Juan Cruz</strong>
    </div>
    <div class="mb-2" style="font-size:.85rem;">
        <i class="fas fa-map-marker-alt me-2" style="color:var(--gasgo-orange);"></i>123 Rizal St, Brgy San Jose
    </div>
    <div class="mb-2" style="font-size:.85rem;">
        <i class="fas fa-box me-2" style="color:#888;"></i>LPG 22kg &times;1 &bull; <strong style="color:var(--gasgo-orange);">₱1,650</strong>
    </div>
    <div class="mb-3" style="font-size:.85rem;">
        <i class="fas fa-money-bill me-2" style="color:#27ae60;"></i>Cash on Delivery
    </div>
    <div class="d-flex gap-2">
        <a href="{{ url('/rider/delivery') }}" class="btn flex-grow-1" style="background:var(--gasgo-orange);color:#fff;border-radius:12px;font-weight:600;">
            <i class="fas fa-motorcycle me-1"></i>Start Delivery
        </a>
        <button class="btn" style="background:var(--gasgo-blue-light);color:var(--gasgo-blue);border-radius:12px;font-weight:600;">
            <i class="fas fa-phone"></i>
        </button>
    </div>
</div>

<div class="r-card" style="border-left:4px solid var(--gasgo-blue);">
    <div class="d-flex justify-content-between align-items-start mb-2">
        <div>
            <div class="fw-bold" style="color:var(--gasgo-blue);">#GG-00010</div>
            <small class="text-muted">Assigned 5 mins ago</small>
        </div>
        <span class="badge-status badge-assigned">Assigned</span>
    </div>
    <div class="mb-2" style="font-size:.88rem;">
        <i class="fas fa-user me-2" style="color:var(--gasgo-blue);"></i><strong>Rico Mendoza</strong>
    </div>
    <div class="mb-2" style="font-size:.85rem;">
        <i class="fas fa-map-marker-alt me-2" style="color:var(--gasgo-orange);"></i>78 Bonifacio St, Brgy Poblacion
    </div>
    <div class="mb-2" style="font-size:.85rem;">
        <i class="fas fa-box me-2" style="color:#888;"></i>LPG 11kg &times;2, Hose &times;1 &bull; <strong style="color:var(--gasgo-orange);">₱2,150</strong>
    </div>
    <div class="mb-3" style="font-size:.85rem;">
        <i class="fas fa-mobile-alt me-2" style="color:#2196f3;"></i>GCash (Paid)
    </div>
    <div class="d-flex gap-2">
        <a href="{{ url('/rider/delivery') }}" class="btn flex-grow-1" style="background:var(--gasgo-blue);color:#fff;border-radius:12px;font-weight:600;">
            <i class="fas fa-motorcycle me-1"></i>Start Delivery
        </a>
        <button class="btn" style="background:var(--gasgo-blue-light);color:var(--gasgo-blue);border-radius:12px;font-weight:600;">
            <i class="fas fa-phone"></i>
        </button>
    </div>
</div>

<!-- No more deliveries -->
<div class="text-center text-muted mt-3" style="font-size:.85rem;">
    <i class="fas fa-check-circle me-1 text-success"></i>No more pending deliveries
</div>
@endsection

@section('scripts')
<script>
    function setStatus(btn, status) {
        document.querySelectorAll('.status-btn').forEach(b => {
            b.className = 'status-btn';
        });
        btn.classList.add('active-' + status);
    }
</script>
@endsection
