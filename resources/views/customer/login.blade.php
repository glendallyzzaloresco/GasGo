@extends('layouts.customer')

@section('title', 'GasGo - Login / Register')

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
    }
    .auth-card {
        background: white; border-radius: 24px; overflow: hidden;
        box-shadow: 0 20px 60px rgba(0,0,0,.1); max-width: 900px; width: 100%;
    }
    .auth-sidebar {
        background: linear-gradient(135deg, var(--gasgo-blue) 0%, #2196f3 60%, var(--gasgo-orange) 100%);
        color: white; padding: 50px 40px; display: flex; flex-direction: column;
        justify-content: center; align-items: center; text-align: center;
    }
    .auth-sidebar img { height: 70px; margin-bottom: 20px; }
    .auth-sidebar h2 { font-weight: 800; margin-bottom: 10px; }
    .auth-sidebar p { opacity: .85; font-size: .95rem; }
    .auth-features { list-style: none; padding: 0; margin-top: 24px; text-align: left; }
    .auth-features li {
        padding: 8px 0; font-size: .9rem; opacity: .9;
        display: flex; align-items: center; gap: 10px;
    }
    .auth-features li i { color: var(--gasgo-orange); }
    .auth-form { padding: 50px 40px; }
    .auth-form h3 { font-weight: 700; color: var(--gasgo-blue); margin-bottom: 6px; }
    .auth-form .sub { color: #888; margin-bottom: 28px; font-size: .92rem; }
    .tab-btns { display: flex; gap: 0; margin-bottom: 28px; border-radius: 12px; overflow: hidden; border: 2px solid #eee; }
    .tab-btns button {
        flex: 1; padding: 12px; border: none; font-weight: 600; font-size: .95rem;
        cursor: pointer; transition: all .25s; background: #fafafa; color: #888;
    }
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
    }
    .btn-auth:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(247,148,29,.35); }
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
    @media (max-width: 768px) {
        .auth-sidebar { padding: 30px 24px; }
        .auth-form { padding: 30px 24px; }
    }
</style>
@endsection

@section('content')
@php($activeTab = request()->get('tab') ?? old('auth_tab', 'login'))
<section class="auth-section">
    <div class="auth-card">
        <div class="row g-0">
            <!-- Left Side -->
            <div class="col-lg-5 d-none d-lg-flex">
                <div class="auth-sidebar w-100">
                    <img src="{{ asset('images/gasgo_logo-removebg-preview.png') }}" alt="GasGo">
                    <h2>Gas<span style="color:var(--gasgo-orange);">Go</span></h2>
                    <p>Your trusted LPG delivery partner with real-time tracking</p>
                    <ul class="auth-features">
                        <li><i class="fas fa-bolt"></i> Fast & Reliable Delivery</li>
                        <li><i class="fas fa-map-marker-alt"></i> Real-Time GPS Tracking</li>
                        <li><i class="fas fa-gift"></i> Earn Loyalty Rewards</li>
                        <li><i class="fas fa-shield-alt"></i> Safe & Secure Payments</li>
                        <li><i class="fas fa-headset"></i> 24/7 Customer Support</li>
                    </ul>
                </div>
            </div>
            <!-- Right Side (Form) -->
            <div class="col-lg-7">
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
                    <div id="loginForm" @if ($activeTab !== 'login') style="display:none;" @endif>
                        <h3>Welcome Back!</h3>
                        <p class="sub">Login to your GasGo account</p>
                        <form action="{{ route('customer.authenticate') }}" method="POST">
                            @csrf
                            <input type="hidden" name="auth_tab" value="login">
                            <div class="form-floating-gasgo">
                                <label>Email Address</label>
                                <input type="email" name="email" placeholder="you@email.com" required>
                            </div>
                            <div class="form-floating-gasgo">
                                <label>Password</label>
                                <div class="password-field-wrapper">
                                    <input type="password" name="password" placeholder="Enter password" class="password-input" required>
                                    <button type="button" class="password-toggle-btn" onclick="togglePassword(this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label style="font-size:.85rem;color:#888;cursor:pointer;">
                                    <input type="checkbox" name="remember" style="margin-right:6px;">Remember me
                                </label>
                                <a href="#" style="font-size:.85rem;color:var(--gasgo-orange);font-weight:600;text-decoration:none;">Forgot Password?</a>
                            </div>
                            <button type="submit" class="btn-auth"><i class="fas fa-sign-in-alt me-2"></i>Login</button>
                        </form>
                        <div class="divider">or</div>
                        <button class="btn-otp"><i class="fab fa-google me-2"></i>Continue with Google</button>
                    </div>

                    <!-- Register Form -->
                    <div id="registerForm" @if ($activeTab !== 'register') style="display:none;" @endif>
                        <h3>Create Account</h3>
                        <p class="sub">Register to start ordering and earn rewards</p>
                        <form action="{{ route('customer.register') }}" method="POST">
                            @csrf
                            <input type="hidden" name="auth_tab" value="register">
                            <div class="form-floating-gasgo">
                                <label>Full Name</label>
                                <input type="text" name="name" value="{{ old('name') }}" placeholder="Juan Dela Cruz" class="@error('name') is-invalid @enderror" required>
                                @error('name')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-floating-gasgo">
                                <label>Email Address</label>
                                <input type="email" name="email" value="{{ old('email') }}" placeholder="you@email.com" class="@error('email') is-invalid @enderror" required>
                                @error('email')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-floating-gasgo">
                                <label>Phone Number</label>
                                <input type="text" name="phone" value="{{ old('phone') }}" placeholder="09XX XXX XXXX" class="@error('phone') is-invalid @enderror" required>
                                @error('phone')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-floating-gasgo">
                                <label>Address</label>
                                <input type="text" name="address" value="{{ old('address') }}" placeholder="Complete delivery address" class="@error('address') is-invalid @enderror">
                                @error('address')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-floating-gasgo">
                                <label>Password</label>
                                <div class="password-field-wrapper">
                                    <input type="password" name="password" placeholder="Create a password" class="password-input @error('password') is-invalid @enderror" required>
                                    <button type="button" class="password-toggle-btn" onclick="togglePassword(this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-floating-gasgo">
                                <label>Confirm Password</label>
                                <div class="password-field-wrapper">
                                    <input type="password" name="password_confirmation" placeholder="Confirm your password" class="password-input" required>
                                    <button type="button" class="password-toggle-btn" onclick="togglePassword(this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="submit" class="btn-auth"><i class="fas fa-user-plus me-2"></i>Register</button>
                        </form>
                        <p class="text-center mt-3" style="font-size:.85rem;color:#888;">
                            Your <strong style="color:var(--gasgo-orange);">Digital Loyalty Card</strong> will be activated immediately!
                        </p>
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
    document.getElementById('loginForm').style.display = tab === 'login' ? '' : 'none';
    document.getElementById('registerForm').style.display = tab === 'register' ? '' : 'none';
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
</script>
@endsection
