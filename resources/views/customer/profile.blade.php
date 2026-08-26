@extends('layouts.customer')

@section('title', 'My Account')

@section('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, var(--gasgo-blue) 0%, #2196f3 70%, #68b7ff 100%);
        color: white;
        padding: 54px 0 64px;
        margin-bottom: -28px;
        position: relative;
        overflow: hidden;
    }

    .page-header::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 60px;
        background: #ffffff;
        clip-path: ellipse(55% 100% at 50% 100%);
    }

    .profile-shell {
        position: relative;
        z-index: 2;
    }

    .profile-card,
    .profile-summary {
        background: white;
        border-radius: 22px;
        box-shadow: 0 14px 40px rgba(15, 23, 42, 0.08);
    }

    .profile-card {
        padding: 32px;
    }

    .profile-summary {
        padding: 28px;
        position: sticky;
        top: 110px;
    }

    .profile-avatar {
        width: 82px;
        height: 82px;
        border-radius: 24px;
        background: linear-gradient(135deg, var(--gasgo-blue), var(--gasgo-orange));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2rem;
        font-weight: 800;
        box-shadow: 0 14px 30px rgba(26, 109, 176, 0.2);
        margin-bottom: 18px;
    }

    .summary-title {
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--gasgo-blue);
        margin-bottom: 4px;
    }

    .summary-meta {
        color: #64748b;
        font-size: 0.9rem;
        margin-bottom: 18px;
    }

    .summary-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 14px;
        border-radius: 999px;
        background: var(--gasgo-orange-light);
        color: var(--gasgo-orange-dark);
        font-weight: 600;
        font-size: 0.85rem;
        margin-bottom: 18px;
    }

    .summary-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .summary-list li {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #eef2f7;
        color: #475569;
        font-size: 0.92rem;
    }

    .summary-list li:last-child {
        border-bottom: none;
    }

    .summary-list i {
        color: var(--gasgo-orange);
        margin-top: 3px;
    }

    .profile-card h3 {
        color: var(--gasgo-blue);
        font-weight: 800;
        margin-bottom: 6px;
    }

    .profile-subtitle {
        color: #6b7280;
        margin-bottom: 24px;
    }

    .field-group {
        margin-bottom: 18px;
    }

    .field-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        font-size: 0.9rem;
        color: #475569;
    }

    .field-note {
        font-size: 0.82rem;
        color: #94a3b8;
        margin-top: 4px;
    }

    .profile-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 10px;
    }

    .alert-profile {
        border: none;
        border-radius: 16px;
        padding: 14px 18px;
        margin-bottom: 20px;
    }

    .password-grid {
        padding-top: 12px;
        margin-top: 12px;
        border-top: 1px solid #eef2f7;
    }

    @media (max-width: 991px) {
        .profile-summary {
            position: static;
        }
    }

    @media (max-width: 768px) {
        .profile-card,
        .profile-summary {
            padding: 22px;
        }
    }
</style>
@endsection

@section('content')
<section class="page-header">
    <div class="container text-center">
        <h1 class="fw-bold"><i class="fas fa-user-circle me-2"></i>My Account</h1>
        <p class="mb-0" style="opacity:.9;">Manage your customer profile and contact information</p>
    </div>
</section>

<section class="container section-padding profile-shell">
    <div class="row g-4">
        <div class="col-lg-4" data-aos="fade-right">
            <div class="profile-summary">
                <div class="profile-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                <div class="summary-title">{{ Auth::user()->name }}</div>
                <div class="summary-meta">{{ Auth::user()->email }}</div>
                <div class="summary-chip"><i class="fas fa-shield-heart"></i>Customer Account</div>

                <ul class="summary-list">
                    <li>
                        <i class="fas fa-phone"></i>
                        <span>{{ Auth::user()->phone ?: 'No phone number added yet.' }}</span>
                    </li>
                    <li>
                        <i class="fas fa-map-marker-alt"></i>
                        <span>{{ Auth::user()->address ?: 'No delivery address saved yet.' }}</span>
                    </li>
                    <li>
                        <i class="fas fa-clock"></i>
                        <span>Member since {{ Auth::user()->created_at->format('M Y') }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="col-lg-8" data-aos="fade-left">
            <div class="profile-card">
                @if (session('success'))
                    <div class="alert alert-success alert-profile">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-profile">
                        <i class="fas fa-exclamation-circle me-2"></i>Please review the highlighted account fields.
                    </div>
                @endif

                <h3>Profile Settings</h3>
                <p class="profile-subtitle">Keep your contact details updated so deliveries and account notifications stay accurate.</p>

                <form action="{{ route('customer.profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="field-group">
                                <label for="name">Full Name</label>
                                <input id="name" type="text" name="name" value="{{ old('name', Auth::user()->name) }}" class="form-control form-control-gasgo @error('name') is-invalid @enderror" required>
                                @error('name')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="field-group">
                                <label for="email">Email Address</label>
                                <input id="email" type="email" name="email" value="{{ old('email', Auth::user()->email) }}" class="form-control form-control-gasgo @error('email') is-invalid @enderror" required>
                                @error('email')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="field-group">
                                <label for="phone">Phone Number</label>
                                <input id="phone" type="text" name="phone" value="{{ old('phone', Auth::user()->phone) }}" class="form-control form-control-gasgo @error('phone') is-invalid @enderror" required>
                                @error('phone')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="field-group">
                                <label for="address">Saved Address</label>
                                <input id="address" type="text" name="address" value="{{ old('address', Auth::user()->address) }}" class="form-control form-control-gasgo @error('address') is-invalid @enderror">
                                @error('address')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="password-grid">
                        <h5 class="fw-bold mb-2" style="color:var(--gasgo-blue);"><i class="fas fa-lock me-2 text-warning"></i>Change Password</h5>
                        <p class="field-note">Leave blank if you do not want to change your password. Must be at least 8 characters with uppercase, lowercase, numbers, and symbols.</p>

                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <div class="field-group">
                                    <label for="profilePassword">New Password</label>
                                    <div class="position-relative">
                                        <input id="profilePassword" type="password" name="password" class="form-control form-control-gasgo @error('password') is-invalid @enderror" minlength="8" autocomplete="new-password" placeholder="Enter new strong password" oninput="checkProfilePasswordStrength(this.value)">
                                        <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted text-decoration-none pe-3" onclick="toggleFieldPassword('profilePassword', this)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div id="profPwStrengthBar" style="height:5px;border-radius:3px;background:#e9ecef;margin-top:6px;overflow:hidden;display:none;">
                                        <div id="profPwStrengthFill" style="height:100%;width:0%;background:#dc3545;transition:width .3s ease, background .3s ease;"></div>
                                    </div>
                                    <small id="profPwStrengthText" style="font-size:.75rem;display:block;margin-top:3px;font-weight:600;"></small>
                                    <div id="profPwRules" class="password-rules-box mt-2 p-2 rounded" style="background:#f8fafc;border:1px solid #e2e8f0;font-size:0.75rem;display:none;">
                                        <div class="row g-1">
                                            <div class="col-6" id="prof-rule-length"><i class="fas fa-circle text-muted me-1" style="font-size:.5rem;"></i>8+ characters</div>
                                            <div class="col-6" id="prof-rule-case"><i class="fas fa-circle text-muted me-1" style="font-size:.5rem;"></i>Upper & lowercase</div>
                                            <div class="col-6" id="prof-rule-number"><i class="fas fa-circle text-muted me-1" style="font-size:.5rem;"></i>At least 1 number</div>
                                            <div class="col-6" id="prof-rule-symbol"><i class="fas fa-circle text-muted me-1" style="font-size:.5rem;"></i>At least 1 symbol</div>
                                        </div>
                                    </div>
                                    @error('password')
                                        <div class="field-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="field-group">
                                    <label for="profilePasswordConfirmation">Confirm New Password</label>
                                    <div class="position-relative">
                                        <input id="profilePasswordConfirmation" type="password" name="password_confirmation" class="form-control form-control-gasgo" minlength="8" autocomplete="new-password" placeholder="Confirm new password" oninput="checkProfilePasswordMatch()">
                                        <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted text-decoration-none pe-3" onclick="toggleFieldPassword('profilePasswordConfirmation', this)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <small id="profPwMatchText" style="font-size:.75rem;display:none;margin-top:4px;"></small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="profile-actions">
                        <button type="submit" class="btn btn-gasgo">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                        <a href="{{ route('customer.dashboard') }}" class="btn btn-gasgo-outline">
                            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
function toggleFieldPassword(inputId, btn) {
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

function checkProfilePasswordStrength(value) {
    const bar = document.getElementById('profPwStrengthBar');
    const fill = document.getElementById('profPwStrengthFill');
    const text = document.getElementById('profPwStrengthText');
    const rules = document.getElementById('profPwRules');

    if (!value) {
        if (bar) bar.style.display = 'none';
        if (rules) rules.style.display = 'none';
        if (text) text.textContent = '';
        checkProfilePasswordMatch();
        return;
    }

    if (bar) bar.style.display = 'block';
    if (rules) rules.style.display = 'block';

    const hasLength = value.length >= 8;
    const hasCase = /[a-z]/.test(value) && /[A-Z]/.test(value);
    const hasNumber = /\d/.test(value);
    const hasSymbol = /[^A-Za-z0-9]/.test(value);

    updateProfRule('prof-rule-length', hasLength);
    updateProfRule('prof-rule-case', hasCase);
    updateProfRule('prof-rule-number', hasNumber);
    updateProfRule('prof-rule-symbol', hasSymbol);

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

    checkProfilePasswordMatch();
}

function updateProfRule(id, passed) {
    const el = document.getElementById(id);
    if (!el) return;
    const text = el.textContent.trim().replace(/^✔\s*|^\s*/, '');
    if (passed) {
        el.innerHTML = `<i class="fas fa-check-circle text-success me-1"></i><span class="text-success fw-semibold">${text}</span>`;
    } else {
        el.innerHTML = `<i class="fas fa-circle text-muted me-1" style="font-size:.5rem;"></i>${text}`;
    }
}

function checkProfilePasswordMatch() {
    const pw = document.getElementById('profilePassword')?.value || '';
    const confirm = document.getElementById('profilePasswordConfirmation')?.value || '';
    const matchText = document.getElementById('profPwMatchText');
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