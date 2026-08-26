@extends('layouts.customer')

@section('title', 'Login / Register')

@section('styles')
<style>
    .auth-alert {
        border: none; border-radius: 14px; padding: 14px 16px;
        margin-bottom: 20px; font-size: .92rem;
    }
    .auth-alert-success { background: #e8f7ee; color: #1f7a45; }
    .auth-alert-error { background: #fdecec; color: #b42318; }
    .auth-section {
        min-height: calc(100vh - 100px);
        display: flex; align-items: center; justify-content: center;
        padding: 40px 20px;
        background: linear-gradient(135deg, #f8f9fa 0%, var(--gasgo-blue-light) 100%);
        position: relative;
        overflow: hidden;
    }
    .auth-section::before,
    .auth-section::after {
        content: '';
        position: absolute;
        border-radius: 50%;
        pointer-events: none;
        z-index: 0;
        filter: blur(2px);
    }
    .auth-section::before {
        width: 260px;
        height: 260px;
        left: -90px;
        top: 10%;
        background: radial-gradient(circle at 30% 30%, rgba(33,150,243,.35), rgba(33,150,243,0));
        animation: floatBlob 9s ease-in-out infinite;
    }
    .auth-section::after {
        width: 320px;
        height: 320px;
        right: -120px;
        bottom: -40px;
        background: radial-gradient(circle at 30% 30%, rgba(247,148,29,.42), rgba(247,148,29,0));
        animation: floatBlob 11s ease-in-out infinite reverse;
    }
    .auth-card {
        background: white; border-radius: 24px; overflow: hidden;
        box-shadow: 0 20px 60px rgba(0,0,0,.1); max-width: 900px; width: 100%;
        position: relative;
        z-index: 1;
        animation: cardEnter .7s cubic-bezier(.2,.7,.25,1) both;
    }
    .auth-sidebar {
        background: linear-gradient(135deg, var(--gasgo-blue) 0%, #2196f3 60%, var(--gasgo-orange) 100%);
        color: white; padding: 50px 40px; display: flex; flex-direction: column;
        justify-content: center; align-items: center; text-align: center;
        position: relative;
        overflow: hidden;
    }
    .auth-sidebar::before {
        content: '';
        position: absolute;
        top: -30%;
        left: -40%;
        width: 70%;
        height: 180%;
        background: linear-gradient(120deg, transparent, rgba(255,255,255,.22), transparent);
        transform: rotate(8deg);
        animation: sidebarSweep 7s ease-in-out infinite;
    }
    .auth-sidebar img {
        height: 70px;
        margin-bottom: 20px;
        animation: logoFloat 4.5s ease-in-out infinite;
    }
    .auth-sidebar h2 { font-weight: 800; margin-bottom: 10px; }
    .auth-sidebar p { opacity: .85; font-size: .95rem; }
    .auth-features { list-style: none; padding: 0; margin-top: 24px; text-align: left; }
    .auth-features li {
        padding: 8px 0; font-size: .9rem; opacity: .9;
        display: flex; align-items: center; gap: 10px;
        opacity: 0;
        transform: translateY(8px);
        animation: featureReveal .55s ease forwards;
    }
    .auth-features li:nth-child(1) { animation-delay: .15s; }
    .auth-features li:nth-child(2) { animation-delay: .25s; }
    .auth-features li:nth-child(3) { animation-delay: .35s; }
    .auth-features li:nth-child(4) { animation-delay: .45s; }
    .auth-features li:nth-child(5) { animation-delay: .55s; }
    .auth-features li i { color: var(--gasgo-orange); }
    .auth-form {
        padding: 50px 40px;
        animation: formEnter .55s ease .12s both;
    }
    .auth-form h3 { font-weight: 700; color: var(--gasgo-blue); margin-bottom: 6px; }
    .auth-form .sub { color: #888; margin-bottom: 28px; font-size: .92rem; }
    .tab-btns { display: flex; gap: 0; margin-bottom: 28px; border-radius: 12px; overflow: hidden; border: 2px solid #eee; }
    .tab-btns button {
        flex: 1; padding: 12px; border: none; font-weight: 600; font-size: .95rem;
        cursor: pointer; transition: all .25s; background: #fafafa; color: #888;
    }
    .tab-btns button:hover { background: #f1f5f9; color: #4a4a4a; }
    .tab-btns button.active { background: var(--gasgo-blue); color: white; }
    .form-floating-gasgo { margin-bottom: 16px; }
    .form-floating-gasgo label { font-weight: 600; font-size: .85rem; color: #555; margin-bottom: 4px; }
    .form-floating-gasgo input {
        border: 2px solid #eee; border-radius: 12px; padding: 14px 18px;
        font-size: .95rem; width: 100%; transition: border-color .25s;
    }
    .form-floating-gasgo input.is-invalid { border-color: #dc3545; }
    .form-floating-gasgo input:focus { border-color: var(--gasgo-orange); outline: none; box-shadow: 0 0 0 3px rgba(247,148,29,.12); }
    .field-error { color: #dc3545; font-size: .82rem; margin-top: 6px; }
    .btn-auth {
        background: linear-gradient(135deg, var(--gasgo-orange), #ff6b35);
        border: none; color: white; padding: 14px; border-radius: 12px;
        font-weight: 700; font-size: 1rem; width: 100%; cursor: pointer;
        transition: transform .2s, box-shadow .2s;
        position: relative;
        overflow: hidden;
    }
    .btn-auth::before {
        content: '';
        position: absolute;
        top: 0;
        left: -120%;
        width: 50%;
        height: 100%;
        background: linear-gradient(120deg, transparent, rgba(255,255,255,.4), transparent);
        transition: left .5s ease;
    }
    .btn-auth:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(247,148,29,.35); }
    .btn-auth:hover::before { left: 130%; }
    .divider { display: flex; align-items: center; gap: 12px; margin: 20px 0; color: #bbb; font-size: .85rem; }
    .divider::before,.divider::after { content: ''; flex: 1; height: 1px; background: #eee; }
    .btn-otp {
        border: 2px solid var(--gasgo-blue); background: white; color: var(--gasgo-blue);
        padding: 12px; border-radius: 12px; font-weight: 600; width: 100%; cursor: pointer;
        transition: all .25s;
    }
    .btn-otp:hover { background: var(--gasgo-blue); color: white; }
    .password-field-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }
    .password-field-wrapper input {
        padding-right: 45px;
    }
    .password-toggle-btn {
        position: absolute;
        right: 14px;
        background: none;
        border: none;
        cursor: pointer;
        color: #999;
        font-size: 1.1rem;
        padding: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.25s;
    }
    .password-toggle-btn:hover {
        color: var(--gasgo-orange);
    }
    .auth-pane {
        transition: opacity .28s ease, transform .28s ease;
    }
    .auth-pane.is-animating {
        opacity: 0;
        transform: translateY(8px);
    }

    @keyframes cardEnter {
        from { opacity: 0; transform: translateY(14px) scale(.985); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    @keyframes formEnter {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes featureReveal {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: .9; transform: translateY(0); }
    }
    @keyframes logoFloat {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-6px); }
    }
    @keyframes floatBlob {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-16px); }
    }
    @keyframes sidebarSweep {
        0%, 100% { transform: translateX(-10%) rotate(8deg); opacity: .25; }
        50% { transform: translateX(62%) rotate(8deg); opacity: .38; }
    }

    @media (prefers-reduced-motion: reduce) {
        * {
            animation-duration: .01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: .01ms !important;
        }
    }
    @media (max-width: 991.98px) {
        .auth-sidebar {
            padding: 32px 20px 24px;
        }
        .auth-sidebar img {
            height: 52px;
            margin-bottom: 10px;
        }
        .auth-sidebar h3 {
            font-size: 1.35rem;
            margin-top: 0 !important;
            margin-bottom: 4px;
        }
        .auth-sidebar p {
            font-size: 0.85rem;
            margin-bottom: 12px;
            max-width: 440px;
        }
        .auth-features {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 8px 12px;
            margin-top: 8px;
            text-align: center;
        }
        .auth-features li {
            padding: 5px 12px;
            font-size: 0.8rem;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(4px);
            border-radius: 50px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
        }
        .auth-features li i {
            color: #ffd166;
        }
        .auth-form {
            padding: 36px 28px;
        }
    }
    @media (max-width: 575.98px) {
        .auth-section {
            padding: 20px 12px;
        }
        .auth-card {
            border-radius: 20px;
        }
        .auth-sidebar {
            padding: 26px 16px 20px;
        }
        .auth-sidebar img {
            height: 46px;
        }
        .auth-sidebar h3 {
            font-size: 1.25rem;
        }
        .auth-sidebar p {
            font-size: 0.8rem;
            margin-bottom: 10px;
        }
        .auth-features {
            gap: 6px 8px;
        }
        .auth-features li {
            padding: 4px 10px;
            font-size: 0.75rem;
        }
        .auth-form {
            padding: 26px 18px;
        }
    }
</style>
@endsection

@section('content')
@php($activeTab = request()->input('tab') ?? old('auth_tab', 'login'))
<section class="auth-section">
    <div class="auth-card">
        <div class="row g-0">
            <!-- Left Side (Top Banner on Mobile) -->
            @php
                $industryNoun = $settings->industry_noun ?? 'LPG Tanks';
                $isWater = str_contains(strtolower($industryNoun), 'water');
                $isFood = str_contains(strtolower($industryNoun), 'food') || str_contains(strtolower($industryNoun), 'meal');
                $isAppliance = str_contains(strtolower($industryNoun), 'appliance');
                $nicheIcon = $isWater ? 'fas fa-tint' : ($isFood ? 'fas fa-utensils' : ($isAppliance ? 'fas fa-blender' : 'fas fa-fire'));
            @endphp
            <div class="col-12 col-lg-5 d-flex">
                <div class="auth-sidebar w-100 text-center">
                    @if(!empty($settings->navbar_logo_path))
                        <img src="{{ $settings->navbar_logo_url }}" alt="{{ trim($settings->brand_name_primary . ' ' . $settings->brand_name_accent) }}" style="max-height:80px;max-width:140px;object-fit:contain;margin:0 auto;" onerror="this.style.display='none';this.nextElementSibling.style.display='inline-flex';">
                        <div class="brand-avatar-badge" style="display:none;width:64px;height:64px;border-radius:16px;background:rgba(255,255,255,0.2);color:#fff;align-items:center;justify-content:center;font-size:2rem;margin:0 auto;">
                            <i class="{{ $nicheIcon }}"></i>
                        </div>
                    @else
                        <div class="brand-avatar-badge" style="display:inline-flex;width:64px;height:64px;border-radius:16px;background:rgba(255,255,255,0.2);color:#fff;align-items:center;justify-content:center;font-size:2rem;margin:0 auto;">
                            <i class="{{ $nicheIcon }}"></i>
                        </div>
                    @endif
                    <h3 class="mt-3 text-white fw-bold">{{ trim($settings->brand_name_primary . ' ' . $settings->brand_name_accent) }}</h3>
                    <p>{{ $settings->hero_subtitle ?? 'Your trusted delivery partner with real-time tracking' }}</p>
                    <ul class="auth-features text-start">
                        <li><i class="fas fa-bolt"></i> Fast & Reliable Delivery</li>
                        <li><i class="fas fa-map-marker-alt"></i> Real-Time GPS Tracking</li>
                        <li><i class="fas fa-gift"></i> Earn Loyalty Rewards</li>
                    </ul>
                </div>
            </div>
            <!-- Right Side (Form) -->
            <div class="col-12 col-lg-7">
                <div class="auth-form">
                    @if (session('success'))
                        <div class="auth-alert auth-alert-success">{{ session('success') }}</div>
                    @endif

                    @if (session('error'))
                        <div class="auth-alert auth-alert-error">{{ session('error') }}</div>
                    @endif

                    @if ($errors->any() && $activeTab === 'register')
                        <div class="auth-alert auth-alert-error">Please fix the highlighted registration fields and try again.</div>
                    @endif

                    <!-- Tab Buttons -->
                    <div class="tab-btns">
                        <button type="button" class="{{ $activeTab === 'login' ? 'active' : '' }}" id="loginTab" onclick="showTab('login')">Login</button>
                        <button type="button" class="{{ $activeTab === 'register' ? 'active' : '' }}" id="registerTab" onclick="showTab('register')">Register</button>
                    </div>

                    <!-- Login Form -->
                    <div id="loginForm" class="auth-pane" @if ($activeTab !== 'login') style="display:none;" @endif>
                        <h3>Welcome Back!</h3>
                        <p class="sub">Login to your {{ trim($settings->brand_name_primary . ' ' . $settings->brand_name_accent) }} account</p>
                        <form action="{{ route('customer.authenticate') }}" method="POST" autocomplete="off">
                            @csrf
                            <input type="hidden" name="auth_tab" value="login">
                            <div class="form-floating-gasgo">
                                <label>Email Address</label>
                                <input type="email" name="email" placeholder="you@email.com" autocomplete="username" value="{{ old('email') }}" class="@error('email') is-invalid @enderror" required>
                                @error('email')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-floating-gasgo">
                                <label>Password</label>
                                <div class="password-field-wrapper">
                                    <input type="password" name="password" placeholder="Enter password" class="password-input @error('password') is-invalid @enderror" autocomplete="current-password" required>
                                    <button type="button" class="password-toggle-btn" onclick="togglePassword(this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label style="font-size:.85rem;color:#888;cursor:pointer;">
                                    <input type="checkbox" name="remember" style="margin-right:6px;" {{ old('remember') ? 'checked' : '' }}>Remember me
                                </label>
                                <a href="{{ route('password.request') }}" style="font-size:.85rem;color:var(--gasgo-orange);font-weight:600;text-decoration:none;">Forgot Password?</a>
                            </div>
                            <button type="submit" class="btn-auth"><i class="fas fa-sign-in-alt me-2"></i>Login</button>
                        </form>
                        <div class="divider">or</div>
                        <a href="{{ route('auth.google') }}" class="btn-otp" style="display: inline-block; width: 100%; text-align: center; text-decoration: none; color: inherit;"><i class="fab fa-google me-2"></i>Continue with Google</a>
                    </div>

                    <!-- Register Form -->
                    <div id="registerForm" class="auth-pane" @if ($activeTab !== 'register') style="display:none;" @endif>
                        @if (request()->input('redirect') === 'checkout')
                            <div class="auth-alert auth-alert-error" style="background: #fff3cd; color: #856404; border-left: 4px solid #ff9800;">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>To complete your purchase:</strong> You need to register or login to checkout your order.
                            </div>
                        @endif
                        <h3>Create Account</h3>
                        <p class="sub">Register to start ordering and earn rewards</p>
                        <form action="{{ route('customer.register') }}" method="POST">
                            @csrf
                            <input type="hidden" name="auth_tab" value="register">
                            <div class="form-floating-gasgo">
                                <label>Full Name</label>
                                <input type="text" name="name" value="{{ old('name') }}" placeholder="Juan Dela Cruz" class="@error('name') is-invalid @enderror" minlength="2" required>
                                @error('name')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-floating-gasgo">
                                <label>Email Address</label>
                                <input type="email" name="email" value="{{ old('email') }}" placeholder="you@email.com" class="@error('email') is-invalid @enderror" autocomplete="email" required>
                                @error('email')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-floating-gasgo">
                                <label>Phone Number</label>
                                <input type="text" name="phone" value="{{ old('phone') }}" placeholder="09XX XXX XXXX" class="@error('phone') is-invalid @enderror" autocomplete="tel" required>
                                @error('phone')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-floating-gasgo">
                                <label>Address</label>
                                <input type="text" name="address" value="{{ old('address') }}" placeholder="Complete delivery address" class="@error('address') is-invalid @enderror" autocomplete="street-address">
                                @error('address')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-floating-gasgo">
                                <label>Password <span class="text-danger">*</span></label>
                                <div class="password-field-wrapper">
                                    <input type="password" name="password" id="registerPassword" placeholder="Create a strong password" class="password-input @error('password') is-invalid @enderror" minlength="8" autocomplete="new-password" required oninput="checkPasswordStrength(this.value)">
                                    <button type="button" class="password-toggle-btn" onclick="togglePassword(this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div id="passwordStrengthBar" style="height:6px;border-radius:3px;background:#e9ecef;margin-top:8px;overflow:hidden;">
                                    <div id="passwordStrengthFill" style="height:100%;width:0%;background:#dc3545;transition:width .3s ease, background .3s ease;"></div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <small id="passwordStrengthText" style="font-size:.78rem;font-weight:600;color:#6c757d;">Must meet all security requirements</small>
                                </div>
                                <div class="password-rules-box mt-2 p-2 rounded" style="background:#f8fafc;border:1px solid #e2e8f0;font-size:0.75rem;">
                                    <div class="row g-1">
                                        <div class="col-6" id="rule-length"><i class="fas fa-circle text-muted me-1" style="font-size:.5rem;"></i>8+ characters</div>
                                        <div class="col-6" id="rule-case"><i class="fas fa-circle text-muted me-1" style="font-size:.5rem;"></i>Upper & lowercase</div>
                                        <div class="col-6" id="rule-number"><i class="fas fa-circle text-muted me-1" style="font-size:.5rem;"></i>At least 1 number</div>
                                        <div class="col-6" id="rule-symbol"><i class="fas fa-circle text-muted me-1" style="font-size:.5rem;"></i>At least 1 symbol</div>
                                    </div>
                                </div>
                                @error('password')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-floating-gasgo">
                                <label>Confirm Password <span class="text-danger">*</span></label>
                                <div class="password-field-wrapper">
                                    <input type="password" name="password_confirmation" id="registerPasswordConfirm" placeholder="Confirm your password" class="password-input" minlength="8" autocomplete="new-password" required oninput="checkPasswordMatch()">
                                    <button type="button" class="password-toggle-btn" onclick="togglePassword(this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <small id="passwordMatchText" style="font-size:.75rem;display:none;margin-top:4px;"></small>
                                @error('password_confirmation')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn-auth"><i class="fas fa-user-plus me-2"></i>Register</button>
                        </form>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
function showTab(tab) {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');

    loginForm.classList.add('is-animating');
    registerForm.classList.add('is-animating');

    setTimeout(() => {
        loginForm.style.display = tab === 'login' ? '' : 'none';
        registerForm.style.display = tab === 'register' ? '' : 'none';

        requestAnimationFrame(() => {
            loginForm.classList.remove('is-animating');
            registerForm.classList.remove('is-animating');
        });
    }, 120);

    document.getElementById('loginTab').classList.toggle('active', tab === 'login');
    document.getElementById('registerTab').classList.toggle('active', tab === 'register');
}

function togglePassword(button) {
    const input = button.parentElement.querySelector('input');
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function checkPasswordStrength(value) {
    const fill = document.getElementById('passwordStrengthFill');
    const text = document.getElementById('passwordStrengthText');
    
    const hasLength = value.length >= 8;
    const hasCase = /[a-z]/.test(value) && /[A-Z]/.test(value);
    const hasNumber = /\d/.test(value);
    const hasSymbol = /[^A-Za-z0-9]/.test(value);

    updateRule('rule-length', hasLength);
    updateRule('rule-case', hasCase);
    updateRule('rule-number', hasNumber);
    updateRule('rule-symbol', hasSymbol);

    let score = 0;
    if (hasLength) score++;
    if (hasCase) score++;
    if (hasNumber) score++;
    if (hasSymbol) score++;
    if (value.length >= 12) score++;

    const levels = [
        { width: '0%', color: '#dc3545', label: 'Enter password to check strength' },
        { width: '25%', color: '#dc3545', label: 'Weak password' },
        { width: '50%', color: '#f7941d', label: 'Moderate password' },
        { width: '75%', color: '#17a2b8', label: 'Good password' },
        { width: '100%', color: '#28a745', label: 'Strong password (All requirements met!)' },
    ];

    const allPassed = hasLength && hasCase && hasNumber && hasSymbol;
    const idx = allPassed ? 4 : Math.min(score, 3);
    const level = levels[value ? idx : 0];
    
    fill.style.width = value ? level.width : '0%';
    fill.style.background = level.color;
    text.textContent = value ? level.label : 'Must meet all security requirements';
    text.style.color = level.color;

    checkPasswordMatch();
}

function updateRule(elementId, passed) {
    const el = document.getElementById(elementId);
    if (!el) return;
    if (passed) {
        el.innerHTML = `<i class="fas fa-check-circle text-success me-1"></i><span class="text-success fw-semibold">${el.textContent.trim()}</span>`;
    } else {
        const rawText = el.textContent.trim().replace(/^✔\s*|^\s*/, '');
        el.innerHTML = `<i class="fas fa-circle text-muted me-1" style="font-size:.5rem;"></i>${rawText}`;
    }
}

function checkPasswordMatch() {
    const pw = document.getElementById('registerPassword')?.value || '';
    const confirm = document.getElementById('registerPasswordConfirm')?.value || '';
    const matchText = document.getElementById('passwordMatchText');
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
