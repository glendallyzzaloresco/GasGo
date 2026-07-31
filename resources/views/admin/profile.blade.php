@extends('layouts.admin')

@section('title', 'My Profile')
@section('page-title', 'My Profile')
@section('nav-profile', 'active')

@section('admin-styles')
<style>
    .profile-header {
        background: linear-gradient(135deg, var(--gasgo-blue) 0%, #2196f3 70%, #68b7ff 100%);
        color: white;
        padding: 40px 0;
        margin-bottom: 30px;
        border-radius: 12px;
    }

    .profile-header h1 {
        font-size: 1.8rem;
        font-weight: 800;
        margin-bottom: 8px;
    }

    .profile-header p {
        opacity: 0.95;
        margin: 0;
        font-size: 0.95rem;
    }

    .profile-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        padding: 28px;
        margin-bottom: 24px;
    }

    .profile-card h3 {
        color: var(--gasgo-blue);
        font-weight: 800;
        margin-bottom: 6px;
        font-size: 1.3rem;
    }

    .profile-subtitle {
        color: #6b7280;
        margin-bottom: 24px;
        font-size: 0.9rem;
    }

    .profile-avatar {
        width: 100px;
        height: 100px;
        border-radius: 16px;
        background: linear-gradient(135deg, var(--gasgo-blue), var(--gasgo-orange));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2.5rem;
        font-weight: 800;
        box-shadow: 0 4px 12px rgba(26, 109, 176, 0.2);
        margin-bottom: 16px;
    }

    .profile-info {
        background: #f8f9fa;
        padding: 16px;
        border-radius: 8px;
        margin-bottom: 24px;
    }

    .info-item {
        margin-bottom: 12px;
        font-size: 0.9rem;
    }

    .info-item:last-child {
        margin-bottom: 0;
    }

    .info-label {
        color: #6b7280;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .info-value {
        color: #1f2937;
        font-weight: 600;
        font-size: 1rem;
    }

    .field-group {
        margin-bottom: 20px;
    }

    .field-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        font-size: 0.9rem;
        color: #475569;
    }

    .field-group input {
        border-radius: 6px;
        border: 1.5px solid #e0e0e0;
        padding: 10px 12px;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }

    .field-group input:focus {
        border-color: var(--gasgo-blue);
        box-shadow: 0 0 0 3px rgba(26, 109, 176, 0.1);
    }

    .password-toggle-btn {
        border-left: 0;
        background: #fff;
        color: #64748b;
    }

    .password-toggle-btn:hover {
        background: #f8fafc;
        color: var(--gasgo-blue);
    }

    .field-note {
        font-size: 0.8rem;
        color: #94a3b8;
        margin-top: 4px;
    }

    .password-section {
        padding-top: 20px;
        margin-top: 20px;
        border-top: 1.5px solid #e0e0e0;
    }

    .password-section h5 {
        color: var(--gasgo-blue);
        font-weight: 800;
        margin-bottom: 12px;
    }

    .profile-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 28px;
    }

    .profile-actions .btn {
        font-weight: 600;
        border-radius: 6px;
        padding: 10px 20px;
        transition: all 0.3s ease;
    }

    .btn-save {
        background: linear-gradient(135deg, var(--gasgo-blue) 0%, #1e5090 100%);
        border: none;
        color: white;
        box-shadow: 0 4px 12px rgba(26, 109, 176, 0.3);
    }

    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(26, 109, 176, 0.4);
        color: white;
    }

    .btn-back {
        background: #f3f4f6;
        border: 1.5px solid #e0e0e0;
        color: #475569;
    }

    .btn-back:hover {
        background: #e5e7eb;
        color: #375569;
    }

    .alert-profile {
        border: none;
        border-radius: 8px;
        padding: 12px 16px;
        margin-bottom: 20px;
        font-size: 0.9rem;
    }

    .is-invalid {
        border-color: #dc2626 !important;
    }

    .field-error {
        color: #dc2626;
        font-size: 0.8rem;
        margin-top: 4px;
        font-weight: 500;
    }

    @media (max-width: 768px) {
        .profile-card {
            padding: 20px;
        }

        .profile-actions {
            flex-direction: column;
        }

        .profile-actions .btn {
            width: 100%;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid p-4">
    <div class="profile-header">
        <div class="d-flex align-items-center gap-3">
            <div class="profile-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
            <div>
                <h1 class="mb-1">{{ Auth::user()->name }}</h1>
                <p class="mb-0"><i class="fas fa-envelope me-2"></i>{{ Auth::user()->email }}</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="profile-card">
                <h5 class="fw-bold mb-3" style="color:var(--gasgo-blue);">Account Details</h5>
                
                <div class="profile-info">
                    <div class="info-item">
                        <div class="info-label">Full Name</div>
                        <div class="info-value">{{ Auth::user()->name }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value">{{ Auth::user()->email }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Phone</div>
                        <div class="info-value">{{ Auth::user()->phone ?: 'Not provided' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Role</div>
                        <div class="info-value">
                            <span class="badge bg-primary" style="background:var(--gasgo-blue) !important;">Administrator</span>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Member Since</div>
                        <div class="info-value">{{ Auth::user()->created_at->format('M d, Y') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="profile-card">
                @if (session('success'))
                    <div class="alert alert-success alert-profile">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-profile">
                        <i class="fas fa-exclamation-circle me-2"></i>Please review the highlighted fields.
                    </div>
                @endif

                <h3>Update Profile</h3>
                <p class="profile-subtitle">Keep your information up to date for better account management and security.</p>

                <form action="{{ route('admin.profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="field-group">
                                <label for="name">Full Name</label>
                                <input id="name" type="text" name="name" value="{{ old('name', Auth::user()->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                                @error('name')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="field-group">
                                <label for="email">Email Address</label>
                                <input id="email" type="email" name="email" value="{{ old('email', Auth::user()->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                                @error('email')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="field-group">
                        <label for="phone">Phone Number</label>
                        <input id="phone" type="text" name="phone" value="{{ old('phone', Auth::user()->phone) }}" class="form-control @error('phone') is-invalid @enderror" placeholder="e.g. 09XX-XXX-XXXX">
                        @error('phone')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="password-section">
                        <h5>Change Password</h5>
                        <p class="field-note">Leave these blank if you do not want to change your password.</p>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="field-group">
                                    <label for="password">New Password</label>
                                    <div class="input-group">
                                        <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                                        <button class="btn password-toggle-btn" type="button" data-password-toggle="password" aria-label="Toggle password visibility">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    @error('password')
                                        <div class="field-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="field-group">
                                    <label for="password_confirmation">Confirm Password</label>
                                    <div class="input-group">
                                        <input id="password_confirmation" type="password" name="password_confirmation" class="form-control">
                                        <button class="btn password-toggle-btn" type="button" data-password-toggle="password_confirmation" aria-label="Toggle password confirmation visibility">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="profile-actions">
                        <button type="submit" class="btn btn-save">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-back">
                            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('[data-password-toggle]').forEach(function (toggleBtn) {
    toggleBtn.addEventListener('click', function () {
        var inputId = this.getAttribute('data-password-toggle');
        var input = document.getElementById(inputId);
        var icon = this.querySelector('i');

        if (!input || !icon) {
            return;
        }

        var isPassword = input.getAttribute('type') === 'password';
        input.setAttribute('type', isPassword ? 'text' : 'password');
        icon.classList.toggle('fa-eye', !isPassword);
        icon.classList.toggle('fa-eye-slash', isPassword);
    });
});
</script>
@endsection
