@extends('layouts.rider')

@section('title', 'GasGo Rider - Profile')
@section('page-title', 'My Profile')
@section('nav-profile', 'active')

@section('content')
<!-- Profile Header -->
<div class="rider-card text-center mb-4">
    <div style="width:90px;height:90px;border-radius:50%;background:linear-gradient(135deg,var(--gasgo-blue),#2196f3);color:#fff;display:flex;align-items:center;justify-content:center;font-size:2.5rem;font-weight:700;margin:0 auto 16px;">
        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
    </div>
    <h5 class="fw-bold mb-1">{{ auth()->user()->name }}</h5>
    <p class="text-muted mb-3" style="font-size:.85rem;">Professional Rider</p>
    <span class="badge bg-success" style="font-size:.78rem;padding:6px 12px;"><i class="fas fa-check-circle me-1"></i>Verified Account</span>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="rider-card text-center">
            <div style="font-size:1.8rem;font-weight:800;color:var(--gasgo-blue);">
                {{ \App\Models\Delivery::where('rider_id', auth()->id())->count() }}
            </div>
            <p style="font-size:.78rem;color:#888;margin-top:4px;">Total Deliveries</p>
        </div>
    </div>
</div>

<!-- Personal Information -->
<div class="rider-card mb-3">
    <h6 class="fw-bold mb-3" style="color:var(--gasgo-blue);"><i class="fas fa-user-circle me-2" style="color:var(--gasgo-orange);"></i>Personal Information</h6>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="text-muted" style="font-size:.78rem;">Email Address</label>
            <div class="fw-bold" style="font-size:.9rem;color:#475569;">{{ auth()->user()->email }}</div>
        </div>
        <div class="col-md-6 mb-3">
            <label class="text-muted" style="font-size:.78rem;">Phone Number</label>
            <div class="fw-bold" style="font-size:.9rem;color:#475569;">{{ auth()->user()->phone ?? 'Not set' }}</div>
        </div>
        <div class="col-md-12">
            <label class="text-muted" style="font-size:.78rem;">Address</label>
            <div class="fw-bold" style="font-size:.9rem;color:#475569;">{{ auth()->user()->address ?? 'Not provided' }}</div>
        </div>
    </div>
</div>

<!-- Actions -->
<div class="d-flex flex-column gap-2 mb-4">
    <button class="btn btn-lg" style="background:var(--gasgo-blue);color:#fff;border-radius:12px;font-weight:600;" data-bs-toggle="modal" data-bs-target="#editProfileModal">
        <i class="fas fa-edit me-2"></i>Edit Profile Information
    </button>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header" style="border-bottom:none;">
                <h6 class="modal-title fw-bold" style="color:var(--gasgo-blue);">Edit Profile</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST" action="{{ route('rider.profile.update') }}">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Availability Status</label>
                        <select name="availability" class="form-control" required>
                            <option value="available" {{ $rider?->availability === 'available' ? 'selected' : '' }}>Available</option>
                            <option value="busy" {{ $rider?->availability === 'busy' ? 'selected' : '' }}>Busy</option>
                            <option value="offline" {{ $rider?->availability === 'offline' ? 'selected' : '' }}>Offline</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:none;">
                    <button type="button" class="btn" data-bs-dismiss="modal" style="border-radius:10px;">Cancel</button>
                    <button type="submit" class="btn" style="background:var(--gasgo-orange);color:#fff;border-radius:10px;font-weight:600;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
