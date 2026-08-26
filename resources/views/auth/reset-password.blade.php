@extends('layouts.customer')

@section('title', 'Verify Code & Reset Password - GasGo')

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
    }
    .auth-section::after {
        width: 320px;
        height: 320px;
        right: -120px;
        bottom: -40px;
        background: radial-gradient(circle at 30% 30%, rgba(247,148,29,.42), rgba(247,148,29,0));
    }
    .auth-card {
        background: white; border-radius: 24px; overflow: hidden;
        box-shadow: 0 20px 60px rgba(0,0,0,.1); max-width: 520px; width: 100%;
        position: relative;
        z-index: 1;
        animation: cardEnter .7s cubic-bezier(.2,.7,.25,1) both;
    }
    .auth-header {
        background: linear-gradient(135deg, var(--gasgo-blue) 0%, #2196f3 100%);
        color: white; padding: 32px 30px; text-align: center;
    }
    .auth-header img {
        height: 56px; margin-bottom: 12px;
    }
    .auth-header h3 { font-weight: 800; margin-bottom: 4px; }
    .auth-header p { opacity: .88; font-size: .88rem; margin: 0; }
    .auth-form {
        padding: 36px 32px;
    }
    .form-floating-gasgo {
        margin-bottom: 18px;
    }
    .form-floating-gasgo label {
        font-size: .82rem; font-weight: 700; color: #475569; margin-bottom: 6px; display: block;
    }
    .form-floating-gasgo input {
        width: 100%; padding: 12px 16px; border-radius: 12px; border: 2px solid #e2e8f0;
        font-size: .95rem; transition: border-color .25s, box-shadow .25s;
    }
    .form-floating-gasgo input:focus {
        border-color: var(--gasgo-blue); outline: none; box-shadow: 0 0 0 3px rgba(26,109,176,.15);
    }
    .form-floating-gasgo input.is-invalid {
        border-color: #e53e3e;
    }
    .code-input {
        font-family: monospace; font-size: 1.4rem !important; letter-spacing: 6px; text-align: center;
        font-weight: 700; color: var(--gasgo-blue);
    }
    .field-error {
        color: #e53e3e; font-size: .8rem; margin-top: 4px; font-weight: 600;
    }
    .password-field-wrapper {
        position: relative; display: flex; align-items: center;
    }
    .password-field-wrapper input {
        padding-right: 48px;
    }
    .password-toggle-btn {
        position: absolute; right: 14px; background: none; border: none; cursor: pointer;
        color: #94a3b8; font-size: 1.1rem; padding: 8px; display: flex; align-items: center; justify-content: center;
        transition: color 0.25s;
    }
    .password-toggle-btn:hover {
        color: var(--gasgo-orange);
    }
    .btn-auth {
        width: 100%; padding: 14px; border: none; border-radius: 14px;
        background: linear-gradient(135deg, var(--gasgo-orange) 0%, #ff9800 100%);
        color: white; font-weight: 700; font-size: 1rem; cursor: pointer;
        transition: transform .2s, box-shadow .2s;
        display: flex; align-items: center; justify-content: center;
    }
    .btn-auth:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(247,148,29,.35);
    }
    .resend-box {
        text-align: center; margin-top: 16px; font-size: .85rem; color: #64748b;
    }
    .resend-btn {
        background: none; border: none; color: var(--gasgo-blue); font-weight: 700; cursor: pointer; padding: 0;
        text-decoration: underline; font-size: .85rem;
    }
    .back-to-login {
        display: inline-flex; align-items: center; justify-content: center;
        color: #64748b; font-size: .88rem; font-weight: 600; text-decoration: none;
        margin-top: 16px; width: 100%; transition: color .2s;
    }
    .back-to-login:hover {
        color: var(--gasgo-blue);
    }
    @keyframes cardEnter {
        from { opacity: 0; transform: translateY(14px) scale(.985); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
</style>
@endsection

@section('content')
<section class="auth-section">
    <div class="auth-card">
        <div class="auth-header">
            <img src="{{ $settings->navbar_logo_url ?? asset('images/logo-gasgo.png') }}" alt="GasGo">
            <h3>Enter 6-Digit Code</h3>
            <p>Check your email for the verification code to reset your password</p>
        </div>

        <div class="auth-form">
            @if (session('status'))
                <div class="auth-alert auth-alert-success">
                    <i class="fas fa-check-circle me-2"></i>{{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="auth-alert auth-alert-error">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('password.update') }}" method="POST">
                @csrf
                <div class="form-floating-gasgo">
                    <label>Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $email) }}" placeholder="you@email.com" class="@error('email') is-invalid @enderror" autocomplete="email" required>
                    @error('email')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-floating-gasgo">
                    <label>6-Digit Verification Code</label>
                    <input type="text" name="code" value="{{ old('code') }}" placeholder="------" maxlength="6" class="code-input @error('code') is-invalid @enderror" autocomplete="one-time-code" required autofocus>
                    @error('code')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-floating-gasgo">
                    <label>New Password <span class="text-danger">*</span></label>
                    <div class="password-field-wrapper">
                        <input type="password" name="password" id="resetPasswordInput" placeholder="New strong password" class="@error('password') is-invalid @enderror" autocomplete="new-password" required minlength="8" oninput="checkResetPasswordStrength(this.value)">
                        <button type="button" class="password-toggle-btn" onclick="togglePassword(this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div id="resetPwStrengthBar" style="height:5px;border-radius:3px;background:#e9ecef;margin-top:6px;overflow:hidden;display:none;">
                        <div id="resetPwStrengthFill" style="height:100%;width:0%;background:#dc3545;transition:width .3s ease, background .3s ease;"></div>
                    </div>
                    <small id="resetPwStrengthText" style="font-size:.75rem;display:block;margin-top:3px;font-weight:600;"></small>
                    <div id="resetPwRules" class="password-rules-box mt-2 p-2 rounded" style="background:#f8fafc;border:1px solid #e2e8f0;font-size:0.75rem;display:none;">
                        <div class="row g-1">
                            <div class="col-6" id="reset-rule-length"><i class="fas fa-circle text-muted me-1" style="font-size:.5rem;"></i>8+ characters</div>
                            <div class="col-6" id="reset-rule-case"><i class="fas fa-circle text-muted me-1" style="font-size:.5rem;"></i>Upper & lowercase</div>
                            <div class="col-6" id="reset-rule-number"><i class="fas fa-circle text-muted me-1" style="font-size:.5rem;"></i>At least 1 number</div>
                            <div class="col-6" id="reset-rule-symbol"><i class="fas fa-circle text-muted me-1" style="font-size:.5rem;"></i>At least 1 symbol</div>
                        </div>
                    </div>
                    @error('password')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-floating-gasgo">
                    <label>Confirm New Password <span class="text-danger">*</span></label>
                    <div class="password-field-wrapper">
                        <input type="password" name="password_confirmation" id="resetPasswordConfirmInput" placeholder="Confirm your new password" autocomplete="new-password" required minlength="8" oninput="checkResetPasswordMatch()">
                        <button type="button" class="password-toggle-btn" onclick="togglePassword(this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <small id="resetPwMatchText" style="font-size:.75rem;display:none;margin-top:4px;"></small>
                </div>

                <button type="submit" class="btn-auth">
                    <i class="fas fa-key me-2"></i>Reset Password
                </button>
            </form>

            <form id="resendCodeForm" action="{{ route('password.resend') }}" method="POST" style="display:none;">
                @csrf
                <input type="hidden" name="email" value="{{ old('email', $email) }}">
            </form>

            <div class="resend-box">
                Didn't receive the code?
                <button type="button" class="resend-btn" onclick="document.getElementById('resendCodeForm').submit()">
                    Resend Code
                </button>
            </div>

            <a href="{{ route('customer.login') }}" class="back-to-login">
                <i class="fas fa-arrow-left me-2"></i>Back to Login
            </a>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
    function togglePassword(btn) {
        const input = btn.previousElementSibling;
        const icon = btn.querySelector('i');
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

    function checkResetPasswordStrength(value) {
        const bar = document.getElementById('resetPwStrengthBar');
        const fill = document.getElementById('resetPwStrengthFill');
        const text = document.getElementById('resetPwStrengthText');
        const rules = document.getElementById('resetPwRules');

        if (!value) {
            if (bar) bar.style.display = 'none';
            if (rules) rules.style.display = 'none';
            if (text) text.textContent = '';
            checkResetPasswordMatch();
            return;
        }

        if (bar) bar.style.display = 'block';
        if (rules) rules.style.display = 'block';

        const hasLength = value.length >= 8;
        const hasCase = /[a-z]/.test(value) && /[A-Z]/.test(value);
        const hasNumber = /\d/.test(value);
        const hasSymbol = /[^A-Za-z0-9]/.test(value);

        updateResetRule('reset-rule-length', hasLength);
        updateResetRule('reset-rule-case', hasCase);
        updateResetRule('reset-rule-number', hasNumber);
        updateResetRule('reset-rule-symbol', hasSymbol);

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

        checkResetPasswordMatch();
    }

    function updateResetRule(id, passed) {
        const el = document.getElementById(id);
        if (!el) return;
        const text = el.textContent.trim().replace(/^✔\s*|^\s*/, '');
        if (passed) {
            el.innerHTML = `<i class="fas fa-check-circle text-success me-1"></i><span class="text-success fw-semibold">${text}</span>`;
        } else {
            el.innerHTML = `<i class="fas fa-circle text-muted me-1" style="font-size:.5rem;"></i>${text}`;
        }
    }

    function checkResetPasswordMatch() {
        const pw = document.getElementById('resetPasswordInput')?.value || '';
        const confirm = document.getElementById('resetPasswordConfirmInput')?.value || '';
        const matchText = document.getElementById('resetPwMatchText');
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
