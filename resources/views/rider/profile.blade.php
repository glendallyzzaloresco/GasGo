@extends('layouts.rider')

@section('title', 'GasGo Rider - Profile')
@section('nav-profile', 'active')

@section('content')
<!-- Profile Header -->
<div class="r-card text-center">
    <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,var(--gasgo-blue),#2196f3);color:#fff;display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:700;margin:0 auto 12px;">M</div>
    <h5 class="fw-bold mb-0">Mark Reyes</h5>
    <p class="text-muted mb-2" style="font-size:.85rem;">GasGo Rider</p>
    <span class="badge bg-success" style="font-size:.78rem;"><i class="fas fa-check-circle me-1"></i>Verified</span>
</div>

<!-- Stats -->
<div class="row g-3 mb-3">
    <div class="col-3">
        <div class="r-card text-center" style="padding:12px;">
            <div style="font-size:1.2rem;font-weight:800;color:var(--gasgo-blue);">128</div>
            <div style="font-size:.65rem;color:#888;">Total</div>
        </div>
    </div>
    <div class="col-3">
        <div class="r-card text-center" style="padding:12px;">
            <div style="font-size:1.2rem;font-weight:800;color:var(--gasgo-orange);">4.8</div>
            <div style="font-size:.65rem;color:#888;">Rating</div>
        </div>
    </div>
    <div class="col-3">
        <div class="r-card text-center" style="padding:12px;">
            <div style="font-size:1.2rem;font-weight:800;color:#27ae60;">98%</div>
            <div style="font-size:.65rem;color:#888;">On Time</div>
        </div>
    </div>
    <div class="col-3">
        <div class="r-card text-center" style="padding:12px;">
            <div style="font-size:1.2rem;font-weight:800;color:#9b59b6;">32m</div>
            <div style="font-size:.65rem;color:#888;">Avg Time</div>
        </div>
    </div>
</div>

<!-- Details -->
<div class="r-card">
    <h6><i class="fas fa-info-circle me-2" style="color:var(--gasgo-orange);"></i>Personal Information</h6>
    <div class="mb-3">
        <label class="text-muted" style="font-size:.78rem;">Email</label>
        <div class="fw-bold" style="font-size:.9rem;">mark.reyes@email.com</div>
    </div>
    <div class="mb-3">
        <label class="text-muted" style="font-size:.78rem;">Phone</label>
        <div class="fw-bold" style="font-size:.9rem;">09171234567</div>
    </div>
    <div class="mb-3">
        <label class="text-muted" style="font-size:.78rem;">Address</label>
        <div class="fw-bold" style="font-size:.9rem;">456 Sampaguita St, Brgy Rosario, Tanauan City</div>
    </div>
</div>

<div class="r-card">
    <h6><i class="fas fa-motorcycle me-2" style="color:var(--gasgo-orange);"></i>Vehicle Information</h6>
    <div class="mb-3">
        <label class="text-muted" style="font-size:.78rem;">Vehicle Type</label>
        <div class="fw-bold" style="font-size:.9rem;">Motorcycle</div>
    </div>
    <div class="mb-3">
        <label class="text-muted" style="font-size:.78rem;">Plate Number</label>
        <div class="fw-bold" style="font-size:.9rem;">ABC-1234</div>
    </div>
    <div>
        <label class="text-muted" style="font-size:.78rem;">License Number</label>
        <div class="fw-bold" style="font-size:.9rem;">N04-12-345678</div>
    </div>
</div>

<!-- Actions -->
<div class="d-flex flex-column gap-2 mt-3">
    <button class="btn" style="background:var(--gasgo-blue);color:#fff;border-radius:12px;font-weight:600;padding:12px;">
        <i class="fas fa-edit me-2"></i>Edit Profile
    </button>
    <button class="btn" style="background:#f8d7da;color:#dc3545;border-radius:12px;font-weight:600;padding:12px;" onclick="event.preventDefault(); document.getElementById('rider-logout-form').submit();">
        <i class="fas fa-sign-out-alt me-2"></i>Logout
    </button>
    <form id="rider-logout-form" action="{{ url('/logout') }}" method="POST" style="display:none;">@csrf</form>
</div>
@endsection
