@extends('layouts.rider')

@section('title', 'Profile')
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

<!-- Vehicle Information -->
@php
    $riderInfo = auth()->user()->rider;
@endphp
<div class="rider-card mb-3">
    <h6 class="fw-bold mb-3" style="color:var(--gasgo-blue);"><i class="fas fa-motorcycle me-2" style="color:var(--gasgo-orange);"></i>Vehicle & Delivery Info</h6>
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="text-muted" style="font-size:.78rem;">Vehicle Type</label>
            <div class="fw-bold" style="font-size:.9rem;color:#475569;">
                <i class="fas fa-truck-pickup me-1 text-primary"></i>{{ $riderInfo->vehicle_type ?? 'Motorcycle' }}
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <label class="text-muted" style="font-size:.78rem;">Plate Number</label>
            <div class="fw-bold" style="font-size:.9rem;color:#475569;">{{ $riderInfo->plate_number ?? 'Not provided' }}</div>
        </div>
        <div class="col-md-4 mb-3">
            <label class="text-muted" style="font-size:.78rem;">License Number</label>
            <div class="fw-bold" style="font-size:.9rem;color:#475569;">{{ $riderInfo->license_number ?? 'Not provided' }}</div>
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
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header" style="border-bottom:none;">
                <h6 class="modal-title fw-bold" style="color:var(--gasgo-blue);"><i class="fas fa-user-edit me-2"></i>Edit Profile & Vehicle Information</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST" action="{{ route('rider.profile.update') }}">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Full Name</label>
                            <input type="text" name="name" class="form-control" value="{{ auth()->user()->name }}" required style="border-radius:10px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control" value="{{ auth()->user()->email }}" required style="border-radius:10px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="{{ auth()->user()->phone ?? '' }}" placeholder="09XX-XXX-XXXX" style="border-radius:10px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Vehicle Type</label>
                            <select name="vehicle_type" class="form-select" style="border-radius:10px;">
                                @php $currentVehicle = $riderInfo->vehicle_type ?? 'Motorcycle'; @endphp
                                <option value="Motorcycle" {{ $currentVehicle === 'Motorcycle' ? 'selected' : '' }}>Motorcycle</option>
                                <option value="Motorcycle with Sidecar (Tricycle)" {{ in_array($currentVehicle, ['Motorcycle with Sidecar (Tricycle)', 'Tricycle', 'Motorcycle with Sidecar']) ? 'selected' : '' }}>Motorcycle with Sidecar (Tricycle)</option>
                                <option value="E-Bike" {{ $currentVehicle === 'E-Bike' ? 'selected' : '' }}>E-Bike</option>
                                <option value="Multicab" {{ $currentVehicle === 'Multicab' ? 'selected' : '' }}>Multicab</option>
                                <option value="Delivery Van" {{ in_array($currentVehicle, ['Delivery Van', 'Van']) ? 'selected' : '' }}>Delivery Van</option>
                                <option value="Truck" {{ $currentVehicle === 'Truck' ? 'selected' : '' }}>Truck</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Plate Number</label>
                            <input type="text" name="plate_number" class="form-control" value="{{ $riderInfo->plate_number ?? '' }}" placeholder="e.g. ABC 1234" style="border-radius:10px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Driver's License Number</label>
                            <input type="text" name="license_number" class="form-control" value="{{ $riderInfo->license_number ?? '' }}" placeholder="e.g. N01-12-345678" style="border-radius:10px;">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Address</label>
                            <textarea name="address" class="form-control" rows="2" placeholder="Enter your address" style="border-radius:10px;">{{ auth()->user()->address ?? '' }}</textarea>
                        </div>
                        <div class="col-12">
                            <hr class="my-2">
                            <h6 class="fw-bold mb-1" style="color:var(--gasgo-blue);"><i class="fas fa-lock me-2 text-warning"></i>Change Password (Optional)</h6>
                            <p class="text-muted small mb-3">Leave blank if not changing. Must have 8+ characters with uppercase, lowercase, numbers, and symbols.</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">New Password</label>
                            <div class="position-relative">
                                <input type="password" name="password" id="riderPassword" class="form-control" minlength="8" autocomplete="new-password" placeholder="Enter new strong password" style="border-radius:10px;padding-right:40px;" oninput="checkRiderPasswordStrength(this.value)">
                                <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted text-decoration-none pe-3" onclick="toggleRiderPassword('riderPassword', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div id="riderPwStrengthBar" style="height:5px;border-radius:3px;background:#e9ecef;margin-top:6px;overflow:hidden;display:none;">
                                <div id="riderPwStrengthFill" style="height:100%;width:0%;background:#dc3545;transition:width .3s ease, background .3s ease;"></div>
                            </div>
                            <small id="riderPwStrengthText" style="font-size:.75rem;display:block;margin-top:3px;font-weight:600;"></small>
                            <div id="riderPwRules" class="password-rules-box mt-2 p-2 rounded" style="background:#f8fafc;border:1px solid #e2e8f0;font-size:0.75rem;display:none;">
                                <div class="row g-1">
                                    <div class="col-6" id="rider-rule-length"><i class="fas fa-circle text-muted me-1" style="font-size:.5rem;"></i>8+ characters</div>
                                    <div class="col-6" id="rider-rule-case"><i class="fas fa-circle text-muted me-1" style="font-size:.5rem;"></i>Upper & lowercase</div>
                                    <div class="col-6" id="rider-rule-number"><i class="fas fa-circle text-muted me-1" style="font-size:.5rem;"></i>At least 1 number</div>
                                    <div class="col-6" id="rider-rule-symbol"><i class="fas fa-circle text-muted me-1" style="font-size:.5rem;"></i>At least 1 symbol</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Confirm New Password</label>
                            <div class="position-relative">
                                <input type="password" name="password_confirmation" id="riderPasswordConfirm" class="form-control" minlength="8" autocomplete="new-password" placeholder="Confirm new password" style="border-radius:10px;padding-right:40px;" oninput="checkRiderPasswordMatch()">
                                <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted text-decoration-none pe-3" onclick="toggleRiderPassword('riderPasswordConfirm', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small id="riderPwMatchText" style="font-size:.75rem;display:none;margin-top:4px;"></small>
                        </div>
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

<script>
function toggleRiderPassword(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (!input || !icon) return;
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

function checkRiderPasswordStrength(value) {
    const bar = document.getElementById('riderPwStrengthBar');
    const fill = document.getElementById('riderPwStrengthFill');
    const text = document.getElementById('riderPwStrengthText');
    const rules = document.getElementById('riderPwRules');

    if (!value) {
        if (bar) bar.style.display = 'none';
        if (rules) rules.style.display = 'none';
        if (text) text.textContent = '';
        checkRiderPasswordMatch();
        return;
    }

    if (bar) bar.style.display = 'block';
    if (rules) rules.style.display = 'block';

    const hasLength = value.length >= 8;
    const hasCase = /[a-z]/.test(value) && /[A-Z]/.test(value);
    const hasNumber = /\d/.test(value);
    const hasSymbol = /[^A-Za-z0-9]/.test(value);

    updateRiderRule('rider-rule-length', hasLength);
    updateRiderRule('rider-rule-case', hasCase);
    updateRiderRule('rider-rule-number', hasNumber);
    updateRiderRule('rider-rule-symbol', hasSymbol);

    let score = 0;
    if (hasLength) score++;
    if (hasCase) score++;
    if (hasNumber) score++;
    if (hasSymbol) score++;

    const allPassed = hasLength && hasCase && hasNumber && hasSymbol;
    if (allPassed) {
        fill.style.width = '100%';
        fill.style.background = '#28a745';
        text.textContent = 'Strong password (All requirements met)';
        text.style.color = '#28a745';
    } else {
        const percent = Math.max(25, score * 25);
        fill.style.width = percent + '%';
        fill.style.background = score <= 2 ? '#dc3545' : '#f7941d';
        text.textContent = score <= 2 ? 'Weak password (requirements missing)' : 'Moderate password';
        text.style.color = score <= 2 ? '#dc3545' : '#f7941d';
    }

    checkRiderPasswordMatch();
}

function updateRiderRule(id, passed) {
    const el = document.getElementById(id);
    if (!el) return;
    const text = el.textContent.trim().replace(/^✔\s*|^\s*/, '');
    if (passed) {
        el.innerHTML = `<i class="fas fa-check-circle text-success me-1"></i><span class="text-success fw-semibold">${text}</span>`;
    } else {
        el.innerHTML = `<i class="fas fa-circle text-muted me-1" style="font-size:.5rem;"></i>${text}`;
    }
}

function checkRiderPasswordMatch() {
    const pw = document.getElementById('riderPassword')?.value || '';
    const confirm = document.getElementById('riderPasswordConfirm')?.value || '';
    const matchText = document.getElementById('riderPwMatchText');
    if (!matchText) return;

    if (!confirm) {
        matchText.style.display = 'none';
        return;
    }

    matchText.style.display = 'block';
    if (pw === confirm) {
        matchText.innerHTML = '<i class="fas fa-check-circle text-success me-1"></i>Passwords match';
        matchText.style.color = '#28a745';
    } else {
        matchText.innerHTML = '<i class="fas fa-times-circle text-danger me-1"></i>Passwords do not match';
        matchText.style.color = '#dc3545';
    }
}
</script>
@endsection
