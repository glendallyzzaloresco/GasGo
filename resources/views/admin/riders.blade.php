@extends('layouts.admin')

@section('title', 'GasGo Admin - Riders')
@section('nav-riders', 'active')
@section('page-title', 'Rider Management')

@section('admin-styles')
<style>
    .rider-card {
        background:#fff; border-radius:16px; box-shadow:0 4px 15px rgba(0,0,0,.06);
        padding:24px; transition:transform .3s; position:relative;
    }
    .rider-card:hover { transform:translateY(-4px); }
    .rider-avatar {
        width:64px; height:64px; border-radius:50%; display:flex; align-items:center; justify-content:center;
        font-size:1.5rem; color:#fff; font-weight:700;
    }
    .rider-stat { text-align:center; }
    .rider-stat .value { font-size:1.3rem; font-weight:700; color:var(--gasgo-blue); }
    .rider-stat .label { font-size:.72rem; color:#888; }
    .avail-dot { width:12px;height:12px;border-radius:50%;display:inline-block; }
    .avail-available { background:#27ae60; }
    .avail-busy { background:#f7941d; }
    .avail-offline { background:#999; }
</style>
@endsection

@php
    $availableCount = $riders->filter(fn($r) => $r->rider && $r->rider->availability === 'available')->count();
    $busyCount = $riders->filter(fn($r) => $r->rider && $r->rider->availability === 'busy')->count();
    $offlineCount = $riders->filter(fn($r) => !$r->rider || $r->rider->availability === 'offline')->count();
    $avatarColors = [
        'available' => 'linear-gradient(135deg,#27ae60,#2ecc71)',
        'busy' => 'linear-gradient(135deg,#f7941d,#ff6b35)',
        'offline' => 'linear-gradient(135deg,#999,#bbb)',
    ];
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <span class="badge bg-success me-2"><i class="fas fa-circle me-1" style="font-size:.5rem;"></i>{{ $availableCount }} Available</span>
        <span class="badge bg-warning text-dark me-2"><i class="fas fa-circle me-1" style="font-size:.5rem;"></i>{{ $busyCount }} Busy</span>
        <span class="badge bg-secondary"><i class="fas fa-circle me-1" style="font-size:.5rem;"></i>{{ $offlineCount }} Offline</span>
    </div>
    <button class="btn" style="background:var(--gasgo-orange);color:#fff;border-radius:12px;font-weight:600;padding:10px 22px;" data-bs-toggle="modal" data-bs-target="#riderModal">
        <i class="fas fa-plus me-2"></i>Add Rider
    </button>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" style="border-radius:12px;">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" style="border-radius:12px;">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    @forelse($riders as $riderUser)
        @php
            $rider = $riderUser->rider;
            $availability = $rider->availability ?? 'offline';
            $totalDel = $deliveryStats[$riderUser->id] ?? 0;
            $completedDel = $completedStats[$riderUser->id] ?? 0;
            $todayDel = $todayDeliveries[$riderUser->id] ?? 0;
            $activeDel = $activeDeliveries[$riderUser->id] ?? null;
            $initial = strtoupper(substr($riderUser->name, 0, 1));
            $avatarBg = $avatarColors[$availability] ?? $avatarColors['offline'];
        @endphp
        <div class="col-lg-4 col-md-6">
            <div class="rider-card">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rider-avatar" style="background:linear-gradient(135deg,#1a6db0,#2196f3);">{{ $initial }}</div>
                    <div>
                        <h6 class="fw-bold mb-0">{{ $riderUser->name }}</h6>
                        <small class="text-muted">{{ $riderUser->phone ?? '—' }}</small><br>
                        @if($availability === 'available')
                            <span class="avail-dot avail-available"></span> <small class="text-success fw-bold">Available</small>
                        @elseif($availability === 'busy')
                            <span class="avail-dot avail-busy"></span> <small class="fw-bold" style="color:var(--gasgo-orange);">Busy</small>
                        @else
                            <span class="avail-dot avail-offline"></span> <small class="text-muted fw-bold">Offline</small>
                        @endif
                    </div>
                </div>
                <div class="d-flex justify-content-around mb-3 p-2" style="background:#f8f9fa;border-radius:12px;">
                    <div class="rider-stat"><div class="value">{{ $totalDel }}</div><div class="label">Total Deliveries</div></div>
                    <div class="rider-stat"><div class="value">{{ $completedDel }}</div><div class="label">Completed</div></div>
                    <div class="rider-stat"><div class="value">{{ $todayDel }}</div><div class="label">Today</div></div>
                </div>
                @if($rider)
                    <div class="mb-2" style="font-size:.82rem;">
                        <i class="fas fa-motorcycle me-1" style="color:var(--gasgo-orange);"></i>
                        <strong>{{ $rider->vehicle_type ?? 'No vehicle' }}</strong>
                        @if($rider->plate_number) — {{ $rider->plate_number }} @endif
                    </div>
                    @if($rider->license_number)
                        <div class="mb-2" style="font-size:.82rem;">
                            <i class="fas fa-id-card me-1" style="color:var(--gasgo-blue);"></i> License: {{ $rider->license_number }}
                        </div>
                    @endif
                @endif
                @if($activeDel)
                    <div class="mb-2" style="font-size:.82rem;color:var(--gasgo-orange);">
                        <i class="fas fa-shipping-fast me-1"></i> Currently delivering <strong>#{{ $activeDel->order->order_number ?? '—' }}</strong>
                    </div>
                @endif
                <div class="d-flex gap-2 mt-3">
                    @if($availability !== 'offline' && $rider)
                        <form action="{{ route('admin.riders.availability', $rider) }}" method="POST" class="flex-grow-1">
                            @csrf @method('PUT')
                            <input type="hidden" name="availability" value="offline">
                            <button class="btn btn-sm w-100" style="background:#f8f9fa;color:#666;border-radius:8px;font-weight:600;">Set Offline</button>
                        </form>
                    @elseif($rider)
                        <form action="{{ route('admin.riders.availability', $rider) }}" method="POST" class="flex-grow-1">
                            @csrf @method('PUT')
                            <input type="hidden" name="availability" value="available">
                            <button class="btn btn-sm w-100" style="background:var(--gasgo-blue);color:#fff;border-radius:8px;font-weight:600;">Set Available</button>
                        </form>
                    @endif
                    @if($rider)
                        <form action="{{ route('admin.riders.destroy', $rider) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this rider account? This action cannot be undone.');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm" style="background:#dc3545;color:#fff;border-radius:8px;font-weight:600;padding:8px 14px;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="text-center py-5 text-muted">
                <i class="fas fa-motorcycle fa-3x mb-3" style="color:#ddd;"></i>
                <p>No riders registered yet. Click <strong>Add Rider</strong> to create one.</p>
            </div>
        </div>
    @endforelse
</div>

<!-- Add Rider Modal -->
<div class="modal fade" id="riderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:16px;">
            <form action="{{ route('admin.riders.store') }}" method="POST">
                @csrf
                <div class="modal-header" style="border-bottom:none;padding:24px 24px 0;">
                    <h5 class="modal-title fw-bold" style="color:var(--gasgo-blue);"><i class="fas fa-user-plus me-2" style="color:var(--gasgo-orange);"></i>Add New Rider</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="padding:24px;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="font-size:.88rem;">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" style="border-radius:10px;" placeholder="e.g. Juan Dela Cruz" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="font-size:.88rem;">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" style="border-radius:10px;" placeholder="rider@email.com" value="{{ old('email') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="font-size:.88rem;">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control" style="border-radius:10px;" placeholder="09XX-XXX-XXXX" value="{{ old('phone') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="font-size:.88rem;">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" style="border-radius:10px;" placeholder="Min 6 characters" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:none;padding:0 24px 24px;">
                    <button type="button" class="btn" data-bs-dismiss="modal" style="border-radius:10px;">Cancel</button>
                    <button type="submit" class="btn" style="background:var(--gasgo-orange);color:#fff;border-radius:10px;font-weight:600;padding:10px 28px;">
                        <i class="fas fa-check me-1"></i>Save Rider
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
