@extends('layouts.rider')

@section('title', 'GasGo Rider - History')
@section('nav-history', 'active')

@section('content')
<!-- Summary -->
<div class="r-card text-center">
    <h6 class="mb-3"><i class="fas fa-chart-bar me-2" style="color:var(--gasgo-orange);"></i>This Month</h6>
    <div class="row g-2">
        <div class="col-4">
            <div style="font-size:1.4rem;font-weight:800;color:var(--gasgo-blue);">42</div>
            <div style="font-size:.72rem;color:#888;">Deliveries</div>
        </div>
        <div class="col-4">
            <div style="font-size:1.4rem;font-weight:800;color:var(--gasgo-orange);">₱3,360</div>
            <div style="font-size:.72rem;color:#888;">Earnings</div>
        </div>
        <div class="col-4">
            <div style="font-size:1.4rem;font-weight:800;color:#27ae60;"><i class="fas fa-star" style="font-size:.9rem;"></i> 4.8</div>
            <div style="font-size:.72rem;color:#888;">Avg Rating</div>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="d-flex gap-2 mb-3 overflow-auto" style="white-space:nowrap;">
    <button class="btn btn-sm active" style="background:var(--gasgo-blue);color:#fff;border-radius:20px;font-weight:600;font-size:.82rem;">All</button>
    <button class="btn btn-sm" style="background:#fff;color:#666;border-radius:20px;font-weight:600;font-size:.82rem;border:1px solid #e0e0e0;">Today</button>
    <button class="btn btn-sm" style="background:#fff;color:#666;border-radius:20px;font-weight:600;font-size:.82rem;border:1px solid #e0e0e0;">This Week</button>
    <button class="btn btn-sm" style="background:#fff;color:#666;border-radius:20px;font-weight:600;font-size:.82rem;border:1px solid #e0e0e0;">This Month</button>
</div>

<!-- Delivery History -->
<h6 class="fw-bold mb-3" style="color:var(--gasgo-blue);font-size:.9rem;">Today</h6>

<div class="r-card" style="border-left:4px solid #27ae60;">
    <div class="d-flex justify-content-between align-items-start mb-2">
        <div>
            <div class="fw-bold" style="color:var(--gasgo-blue);">#GG-00007</div>
            <div style="font-size:.82rem;color:#888;">3:00 PM &bull; 35 min delivery</div>
        </div>
        <span class="badge-status badge-delivered">Delivered</span>
    </div>
    <div style="font-size:.85rem;">
        <i class="fas fa-user me-1" style="color:var(--gasgo-blue);"></i>Pedro Lim &bull;
        <span style="color:var(--gasgo-orange);font-weight:600;">₱1,750</span>
    </div>
    <div style="font-size:.82rem;color:#888;">
        <i class="fas fa-map-marker-alt me-1"></i>90 Luna St, Brgy Bayan
    </div>
    <div class="mt-2" style="font-size:.82rem;">
        <i class="fas fa-star text-warning"></i>
        <i class="fas fa-star text-warning"></i>
        <i class="fas fa-star text-warning"></i>
        <i class="fas fa-star text-warning"></i>
        <i class="fas fa-star text-warning"></i>
        <span class="text-muted ms-1">"Fast delivery, very polite!"</span>
    </div>
</div>

<div class="r-card" style="border-left:4px solid #27ae60;">
    <div class="d-flex justify-content-between align-items-start mb-2">
        <div>
            <div class="fw-bold" style="color:var(--gasgo-blue);">#GG-00005</div>
            <div style="font-size:.82rem;color:#888;">1:15 PM &bull; 28 min delivery</div>
        </div>
        <span class="badge-status badge-delivered">Delivered</span>
    </div>
    <div style="font-size:.85rem;">
        <i class="fas fa-user me-1" style="color:var(--gasgo-blue);"></i>Rosa Aquino &bull;
        <span style="color:var(--gasgo-orange);font-weight:600;">₱900</span>
    </div>
    <div style="font-size:.82rem;color:#888;">
        <i class="fas fa-map-marker-alt me-1"></i>56 Del Pilar St, Brgy Malvar
    </div>
    <div class="mt-2" style="font-size:.82rem;">
        <i class="fas fa-star text-warning"></i>
        <i class="fas fa-star text-warning"></i>
        <i class="fas fa-star text-warning"></i>
        <i class="fas fa-star text-warning"></i>
        <i class="far fa-star text-warning"></i>
        <span class="text-muted ms-1">"Good service"</span>
    </div>
</div>

<div class="r-card" style="border-left:4px solid #27ae60;">
    <div class="d-flex justify-content-between align-items-start mb-2">
        <div>
            <div class="fw-bold" style="color:var(--gasgo-blue);">#GG-00003</div>
            <div style="font-size:.82rem;color:#888;">10:30 AM &bull; 22 min delivery</div>
        </div>
        <span class="badge-status badge-delivered">Delivered</span>
    </div>
    <div style="font-size:.85rem;">
        <i class="fas fa-user me-1" style="color:var(--gasgo-blue);"></i>Carlo Santos &bull;
        <span style="color:var(--gasgo-orange);font-weight:600;">₱850</span>
    </div>
    <div style="font-size:.82rem;color:#888;">
        <i class="fas fa-map-marker-alt me-1"></i>34 Quezon Ave, Brgy Sampaguita
    </div>
    <div class="mt-2" style="font-size:.82rem;">
        <i class="fas fa-star text-warning"></i>
        <i class="fas fa-star text-warning"></i>
        <i class="fas fa-star text-warning"></i>
        <i class="fas fa-star text-warning"></i>
        <i class="fas fa-star text-warning"></i>
        <span class="text-muted ms-1">No comment</span>
    </div>
</div>

<h6 class="fw-bold mb-3 mt-4" style="color:var(--gasgo-blue);font-size:.9rem;">Yesterday</h6>

<div class="r-card" style="border-left:4px solid #27ae60;">
    <div class="d-flex justify-content-between align-items-start mb-2">
        <div>
            <div class="fw-bold" style="color:var(--gasgo-blue);">#GG-00001</div>
            <div style="font-size:.82rem;color:#888;">4:30 PM &bull; 42 min delivery</div>
        </div>
        <span class="badge-status badge-delivered">Delivered</span>
    </div>
    <div style="font-size:.85rem;">
        <i class="fas fa-user me-1" style="color:var(--gasgo-blue);"></i>Maria Santos &bull;
        <span style="color:var(--gasgo-orange);font-weight:600;">₱1,650</span>
    </div>
    <div style="font-size:.82rem;color:#888;">
        <i class="fas fa-map-marker-alt me-1"></i>123 Rizal St, Brgy San Jose
    </div>
</div>
@endsection
