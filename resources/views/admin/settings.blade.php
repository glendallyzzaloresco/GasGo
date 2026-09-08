@extends('layouts.admin')

@section('title', 'Settings')
@section('page-title', 'Settings')
@section('nav-settings', 'active')

@section('admin-styles')
<style>
    .settings-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
        border: 1px solid #edf2f7;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .settings-card-header {
        background: #fff;
        border-bottom: 1px solid #f1f5f9;
        padding: 20px 24px 16px;
        border-radius: 16px 16px 0 0;
    }
    .settings-card-body {
        padding: 24px;
        flex-grow: 1;
    }
    .settings-icon-box {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    .spec-item {
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 12px;
        padding: 14px 16px;
    }
    .payment-method-row {
        background: #fafbfc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        transition: border-color 0.2s;
    }
    .payment-method-row:hover {
        border-color: #cbd5e1;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-0">

    {{-- Flash Alerts --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm" role="alert" style="border-radius:12px;">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm" role="alert" style="border-radius:12px;">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm" role="alert" style="border-radius:12px;">
        <div class="fw-bold mb-1"><i class="fas fa-exclamation-triangle me-2"></i>Please check the following errors:</div>
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Action Header --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color:var(--gasgo-blue);"><i class="fas fa-sliders me-2"></i>Store & System Settings</h4>
            <p class="text-muted mb-0" style="font-size:0.88rem;">Configure delivery rates, payment gateways, storefront branding, admin staff, and maintenance</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button class="btn btn-sm shadow-sm" style="background:var(--gasgo-blue);color:#fff;border-radius:10px;padding:9px 18px;font-weight:600;" data-bs-toggle="modal" data-bs-target="#adminModal">
                <i class="fas fa-user-shield me-1"></i>Add Admin Account
            </button>
            <a href="{{ route('admin.settings.homepage') }}" class="btn btn-sm shadow-sm" style="background:var(--gasgo-orange);color:#fff;border-radius:10px;padding:9px 18px;font-weight:600;">
                <i class="fas fa-palette me-1"></i>Homepage Controls
            </a>
            <a href="{{ route('admin.activity-logs') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:10px;padding:9px 16px;font-weight:600;">
                <i class="fas fa-history me-1"></i>Activity Logs
            </a>
        </div>
    </div>

    {{-- SECTION 1: STORE & ORDER CONFIGURATION --}}
    <div class="row g-4 mb-4">
        {{-- Delivery Fee Configuration --}}
        <div class="col-lg-6">
            <div class="settings-card">
                <div class="settings-card-header d-flex align-items-center gap-3">
                    <div class="settings-icon-box" style="background:#e0f2fe;color:#0284c7;">
                        <i class="fas fa-truck-fast"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Delivery Fee Configuration</h6>
                        <small class="text-muted">Standard rate applied automatically during checkout</small>
                    </div>
                </div>
                <div class="settings-card-body d-flex flex-column justify-content-between">
                    <div>
                        <p class="text-muted small mb-3">
                            Set the default delivery fee charged on customer orders. Changing this fee takes effect immediately on all newly placed orders.
                        </p>
                        <form action="{{ route('admin.settings.update-delivery-fee') }}" method="POST" id="deliveryFeeForm">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold small text-dark">Default Delivery Fee (PHP)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light fw-bold text-muted">₱</span>
                                    <input type="number" name="delivery_fee" step="0.01" min="0" class="form-control @error('delivery_fee') is-invalid @enderror" value="{{ old('delivery_fee', number_format($homepageSettings->delivery_fee ?? 50, 2, '.', '')) }}" required style="border-radius:0 10px 10px 0;">
                                    @error('delivery_fee')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="pt-3 border-top d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Current rate: <strong>₱{{ number_format($homepageSettings->delivery_fee ?? 50, 2) }}</strong></span>
                        <button type="submit" form="deliveryFeeForm" class="btn btn-sm" style="background:var(--gasgo-blue);color:#fff;border-radius:8px;font-weight:600;padding:8px 20px;">
                            <i class="fas fa-save me-1"></i>Save Delivery Fee
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Storefront & Homepage Controls --}}
        <div class="col-lg-6">
            <div class="settings-card">
                <div class="settings-card-header d-flex align-items-center gap-3">
                    <div class="settings-icon-box" style="background:#fff7ed;color:#ea580c;">
                        <i class="fas fa-store"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Storefront & Homepage Appearance</h6>
                        <small class="text-muted">Branding, promotional banners, and hero section</small>
                    </div>
                </div>
                <div class="settings-card-body d-flex flex-column justify-content-between">
                    <div>
                        <p class="text-muted small mb-3">
                            Customize what customers and guests see across the storefront: company logo, brand name, hero banners, and promotional campaign artwork.
                        </p>
                        <div class="p-3 rounded-3 mb-3" style="background:#f8fafc;border:1px solid #f1f5f9;">
                            <div class="d-flex align-items-center gap-3">
                                @if(!empty($homepageSettings->logo_path))
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($homepageSettings->logo_path) }}" alt="Logo" style="width:40px;height:40px;object-fit:contain;border-radius:8px;">
                                @else
                                    <div style="width:40px;height:40px;border-radius:8px;background:var(--gasgo-blue);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;">
                                        G
                                    </div>
                                @endif
                                <div>
                                    <div class="fw-bold text-dark" style="font-size:0.95rem;">{{ $homepageSettings->brand_name ?? 'GasGo' }}</div>
                                    <small class="text-muted">{{ $homepageSettings->industry_noun ?? 'LPG Tanks & Accessories' }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="pt-3 border-top d-flex justify-content-between align-items-center">
                        <span class="text-muted small"><i class="fas fa-check-circle text-success me-1"></i>Live on Homepage</span>
                        <a href="{{ route('admin.settings.homepage') }}" class="btn btn-sm" style="background:var(--gasgo-orange);color:#fff;border-radius:8px;font-weight:600;padding:8px 20px;">
                            <i class="fas fa-palette me-1"></i>Customize Storefront
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION 2: PAYMENT GATEWAYS & FINANCIAL ACCOUNTS --}}
    <div class="row g-4 mb-4">
        {{-- GCash Account Settings --}}
        <div class="col-lg-5">
            <div class="settings-card">
                <div class="settings-card-header d-flex align-items-center gap-3">
                    <div class="settings-icon-box" style="background:#ecfdf5;color:#059669;">
                        <i class="fas fa-mobile-screen-button"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">GCash Payment Account</h6>
                        <small class="text-muted">Primary mobile payment account & QR code</small>
                    </div>
                </div>
                <div class="settings-card-body">
                    <form action="{{ route('admin.settings.update-gcash') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-dark">GCash Account Number</label>
                            <input type="text" name="gcash_account_number" class="form-control form-control-sm @error('gcash_account_number') is-invalid @enderror" placeholder="09XX XXX XXXX" value="{{ old('gcash_account_number', $homepageSettings->gcash_account_number ?? '') }}" style="border-radius:8px;">
                            @error('gcash_account_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-dark">Account Name / Receiver Name</label>
                            <input type="text" name="gcash_account_name" class="form-control form-control-sm @error('gcash_account_name') is-invalid @enderror" placeholder="e.g. GasGo Enterprises" value="{{ old('gcash_account_name', $homepageSettings->gcash_account_name ?? '') }}" style="border-radius:8px;">
                            @error('gcash_account_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-dark">GCash QR Code Image</label>
                            <input type="file" name="gcash_image" class="form-control form-control-sm @error('gcash_image') is-invalid @enderror" accept="image/*" style="border-radius:8px;">
                            @if(!empty($homepageSettings->gcash_image_path))
                                <div class="mt-2 d-flex align-items-center gap-2 p-2 bg-light rounded-3">
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($homepageSettings->gcash_image_path) }}" alt="GCash QR Code" style="width:60px;height:60px;object-fit:contain;border:1px solid #ddd;border-radius:8px;background:#fff;padding:2px;">
                                    <small class="text-muted">Current QR code on file. Upload a new image to replace it.</small>
                                </div>
                            @endif
                            @error('gcash_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="pt-3 border-top text-end">
                            <button type="submit" class="btn btn-sm" style="background:#059669;color:#fff;border-radius:8px;font-weight:600;padding:8px 22px;">
                                <i class="fas fa-save me-1"></i>Save GCash Details
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Additional Checkout Payment Methods --}}
        <div class="col-lg-7">
            <div class="settings-card">
                <div class="settings-card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <div class="settings-icon-box" style="background:#f5f3ff;color:#7c3aed;">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">Additional Payment Methods</h6>
                            <small class="text-muted">Bank Transfer, Maya, or other accepted payment options</small>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="addPaymentMethodBtn" style="border-radius:8px;font-weight:600;">
                        <i class="fas fa-plus me-1"></i>Add Method
                    </button>
                </div>
                <div class="settings-card-body">
                    @php
                        $customPaymentMethods = old('payment_methods', $homepageSettings->payment_methods ?? []);
                    @endphp

                    <form action="{{ route('admin.settings.update-payment-methods') }}" method="POST" id="paymentMethodsForm" enctype="multipart/form-data">
                        @csrf
                        <div id="paymentMethodsContainer" class="d-grid gap-3">
                            <div id="noPaymentMethodsAlert" class="alert alert-light border text-muted small text-center mb-0 py-3" style="{{ empty($customPaymentMethods) ? '' : 'display:none;' }}">
                                <i class="fas fa-info-circle me-1"></i>No additional payment methods configured. Click <strong>+ Add Method</strong> to add Bank Transfer, Maya, or others.
                            </div>
                            @foreach ($customPaymentMethods as $index => $method)
                                <div class="p-3 payment-method-row" data-index="{{ $index }}">
                                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                        <strong class="small text-uppercase text-primary"><i class="fas fa-credit-card me-1"></i>Method {{ $loop->iteration }}</strong>
                                        <button type="button" class="btn btn-link text-danger p-0 remove-payment-method-btn" title="Remove method">
                                            <i class="fas fa-trash-can"></i>
                                        </button>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <label class="form-label small mb-1 fw-semibold">Method Name</label>
                                            <input type="text" name="payment_methods[{{ $index }}][label]" class="form-control form-control-sm payment-method-label" value="{{ old('payment_methods.' . $index . '.label', $method['label'] ?? '') }}" placeholder="e.g. Bank Transfer" required style="border-radius:6px;">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small mb-1 fw-semibold">Account Name</label>
                                            <input type="text" name="payment_methods[{{ $index }}][account_name]" class="form-control form-control-sm" value="{{ old('payment_methods.' . $index . '.account_name', $method['account_name'] ?? '') }}" placeholder="Optional" style="border-radius:6px;">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small mb-1 fw-semibold">Account Number</label>
                                            <input type="text" name="payment_methods[{{ $index }}][account_number]" class="form-control form-control-sm" value="{{ old('payment_methods.' . $index . '.account_number', $method['account_number'] ?? '') }}" placeholder="Optional" style="border-radius:6px;">
                                        </div>
                                        <div class="col-12 mt-2">
                                            <label class="form-label small mb-1 fw-semibold">Payment QR / Image (Optional)</label>
                                            <input type="file" name="payment_methods[{{ $index }}][image]" class="form-control form-control-sm" accept="image/*" style="border-radius:6px;">
                                            <input type="hidden" name="payment_methods[{{ $index }}][existing_image]" value="{{ $method['image_path'] ?? '' }}">
                                            @if(!empty($method['image_path']))
                                                <div class="mt-2 d-flex align-items-center gap-2">
                                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($method['image_path']) }}" alt="Payment QR" style="max-width:60px;max-height:60px;object-fit:contain;border:1px solid #ddd;border-radius:6px;background:#fff;padding:2px;">
                                                    <small class="text-muted">Saved QR/Image</small>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="pt-3 border-top mt-3 text-end">
                            <button type="submit" class="btn btn-sm" style="background:var(--gasgo-blue);color:#fff;border-radius:8px;font-weight:600;padding:8px 22px;">
                                <i class="fas fa-save me-1"></i>Save Payment Methods
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION 3: STAFF & SYSTEM MAINTENANCE --}}
    <div class="row g-4 mb-4">
        {{-- Admin Staff Management Card --}}
        <div class="col-lg-4">
            <div class="settings-card">
                <div class="settings-card-header d-flex align-items-center gap-3">
                    <div class="settings-icon-box" style="background:#f3e8ff;color:#7e22ce;">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Admin Staff Access</h6>
                        <small class="text-muted">Manage system administrators</small>
                    </div>
                </div>
                <div class="settings-card-body d-flex flex-column justify-content-between">
                    <div>
                        <p class="text-muted small mb-3">
                            Authorize staff members to access the administrator backoffice. Created admin accounts can log in and manage the store immediately.
                        </p>
                        @php
                            $adminUsersCount = \App\Models\User::where('role', 'admin')->count();
                        @endphp
                        <div class="p-3 rounded-3 mb-3" style="background:#faf5ff;border:1px solid #e9d5ff;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted small">Active Administrators</div>
                                    <h4 class="fw-bold mb-0" style="color:#7e22ce;">{{ $adminUsersCount }}</h4>
                                </div>
                                <span class="badge" style="background:#7e22ce;color:#fff;">Full Access</span>
                            </div>
                        </div>
                    </div>
                    <div class="pt-3 border-top d-flex gap-2">
                        <button class="btn btn-sm flex-grow-1" style="background:var(--gasgo-blue);color:#fff;border-radius:8px;font-weight:600;" data-bs-toggle="modal" data-bs-target="#adminModal">
                            <i class="fas fa-user-plus me-1"></i>Add Admin
                        </button>
                        <a href="{{ route('admin.users') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;font-weight:600;">
                            <i class="fas fa-users me-1"></i>View Users
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Activity Logs & Audit Trail --}}
        <div class="col-lg-4">
            <div class="settings-card">
                <div class="settings-card-header d-flex align-items-center gap-3">
                    <div class="settings-icon-box" style="background:#e0f2fe;color:#0284c7;">
                        <i class="fas fa-history"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Audit Trail & Activity Logs</h6>
                        <small class="text-muted">System changes, logins, and audit logs</small>
                    </div>
                </div>
                <div class="settings-card-body d-flex flex-column justify-content-between">
                    <div>
                        <p class="text-muted small mb-3">
                            Real-time audit trails recording user authentication, orders, inventory restocking, deliveries, and settings updates.
                        </p>
                        <div class="p-3 rounded-3 mb-3" style="background:#f0f9ff;border:1px solid #bae6fd;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted small">Recorded Audit Events</div>
                                    <h4 class="fw-bold mb-0" style="color:#0284c7;">{{ number_format($recentLogsCount ?? 0) }}</h4>
                                </div>
                                <span class="badge bg-primary">Audited</span>
                            </div>
                        </div>
                    </div>
                    <div class="pt-3 border-top d-flex gap-2">
                        <a href="{{ route('admin.activity-logs') }}" class="btn btn-sm flex-grow-1" style="background:#0284c7;color:#fff;border-radius:8px;font-weight:600;">
                            <i class="fas fa-external-link-alt me-1"></i>Open Logs
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-danger" style="border-radius:8px;" data-bs-toggle="modal" data-bs-target="#clearLogsModal" title="Clear logs">
                            <i class="fas fa-trash-can"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Storage & Error Log Health --}}
        <div class="col-lg-4">
            <div class="settings-card">
                <div class="settings-card-header d-flex align-items-center gap-3">
                    <div class="settings-icon-box" style="background:#ecfdf5;color:#059669;">
                        <i class="fas fa-hard-drive"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Storage & Server Log</h6>
                        <small class="text-muted">Diagnostics and error log file status</small>
                    </div>
                </div>
                <div class="settings-card-body d-flex flex-column justify-content-between">
                    <div>
                        <p class="text-muted small mb-3">
                            Monitors server disk storage and application error log sizes for operational diagnostics and health monitoring.
                        </p>
                        @php
                            $logPath = storage_path('logs/laravel.log');
                            $logSize = file_exists($logPath) ? round(filesize($logPath) / 1024, 2) : 0;
                        @endphp
                        <div class="p-3 rounded-3 mb-3" style="background:#f8fafc;border:1px solid #f1f5f9;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted small">Application Log Size</span>
                                <span class="badge {{ $logSize > 1024 ? 'bg-danger' : ($logSize > 100 ? 'bg-warning text-dark' : 'bg-success') }}">
                                    {{ $logSize }} KB
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small">Storage Status</span>
                                <span class="text-success small fw-semibold"><i class="fas fa-circle-check me-1"></i>Healthy</span>
                            </div>
                        </div>
                    </div>
                    <div class="pt-3 border-top text-muted small">
                        <i class="fas fa-circle-info me-1 text-primary"></i>Log files rotate automatically during maintenance.
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION 4: TECHNICAL SPECIFICATIONS & ENVIRONMENT --}}
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="settings-card">
                <div class="settings-card-header d-flex align-items-center gap-3">
                    <div class="settings-icon-box" style="background:#f1f5f9;color:#475569;">
                        <i class="fas fa-server"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Technical Environment & Infrastructure</h6>
                        <small class="text-muted">Framework versions, database connection, and drivers</small>
                    </div>
                </div>
                <div class="settings-card-body">
                    <div class="row g-3">
                        <div class="col-lg-3 col-md-6">
                            <div class="spec-item">
                                <div class="text-muted small mb-1">Application Name</div>
                                <div class="fw-semibold text-dark">{{ $appName }}</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="spec-item">
                                <div class="text-muted small mb-1">Environment</div>
                                <div class="fw-semibold">
                                    <span class="badge {{ $appEnv === 'production' ? 'bg-success' : 'bg-warning text-dark' }}">
                                        {{ ucfirst($appEnv) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="spec-item">
                                <div class="text-muted small mb-1">Debug Mode</div>
                                <div class="fw-semibold">
                                    <span class="badge {{ $appDebug ? 'bg-danger' : 'bg-success' }}">
                                        {{ $appDebug ? 'ON' : 'OFF' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="spec-item">
                                <div class="text-muted small mb-1">Laravel Version</div>
                                <div class="fw-semibold text-dark">{{ $laravelVersion }}</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="spec-item">
                                <div class="text-muted small mb-1">PHP Runtime</div>
                                <div class="fw-semibold text-dark">{{ $phpVersion }}</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="spec-item">
                                <div class="text-muted small mb-1">Database Engine</div>
                                <div class="fw-semibold text-dark">{{ ucfirst($dbConnection) }}</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="spec-item">
                                <div class="text-muted small mb-1">Cache Driver</div>
                                <div class="fw-semibold text-dark">{{ ucfirst($cacheDriver) }}</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="spec-item">
                                <div class="text-muted small mb-1">Queue Driver</div>
                                <div class="fw-semibold text-dark">{{ ucfirst($queueDriver) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- MODALS --}}

{{-- Add Admin Account Modal --}}
<div class="modal fade" id="adminModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
            <form action="{{ route('admin.settings.admin-users.store') }}" method="POST">
                @csrf
                <div class="modal-header" style="border-bottom:1px solid #f1f5f9;padding:20px 24px;">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:42px;height:42px;border-radius:12px;background:#f3e8ff;color:#7e22ce;display:flex;align-items:center;justify-content:center;font-size:1.2rem;">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0">Add New Admin Account</h5>
                            <small class="text-muted">Create a staff account with administrative privileges</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="padding:24px;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" style="border-radius:10px;" placeholder="e.g. John Doe" value="{{ old('name') }}" required>
                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" style="border-radius:10px;" placeholder="admin@example.com" value="{{ old('email') }}" required>
                            @error('email')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold small text-dark">Phone Number <small class="text-muted">(Optional)</small></label>
                            <input type="text" name="phone" class="form-control" style="border-radius:10px;" placeholder="09XXXXXXXXX" value="{{ old('phone') }}">
                            @error('phone')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">Password <span class="text-danger">*</span></label>
                            <div class="input-group" style="border-radius:10px;overflow:hidden;">
                                <input type="password" name="password" id="adminPassword" class="form-control" style="border-radius:10px 0 0 10px;" placeholder="Min 6 characters" minlength="6" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility(this, 'adminPassword')" style="border-radius:0 10px 10px 0;">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">Confirm Password <span class="text-danger">*</span></label>
                            <div class="input-group" style="border-radius:10px;overflow:hidden;">
                                <input type="password" name="password_confirmation" id="adminPasswordConfirm" class="form-control" style="border-radius:10px 0 0 10px;" placeholder="Confirm password" minlength="6" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility(this, 'adminPasswordConfirm')" style="border-radius:0 10px 10px 0;">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('password_confirmation')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #f1f5f9;padding:16px 24px;">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal" style="border-radius:10px;font-weight:600;">Cancel</button>
                    <button type="submit" class="btn" style="background:var(--gasgo-blue);color:#fff;border-radius:10px;font-weight:600;padding:10px 28px;">
                        <i class="fas fa-user-plus me-1"></i>Create Admin Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Clear Activity Logs Confirmation Modal --}}
<div class="modal fade" id="clearLogsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header" style="border-bottom:none;padding:20px 24px 0;">
                <h5 class="modal-title fw-bold text-danger"><i class="fas fa-triangle-exclamation me-2"></i>Clear Activity Logs?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:16px 24px;">
                <p class="text-muted mb-0">
                    Are you sure you want to clear all system activity logs? This action will permanently remove audit history for past actions and cannot be undone.
                </p>
            </div>
            <div class="modal-footer" style="border-top:none;padding:0 24px 20px;">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius:8px;">Cancel</button>
                <form action="{{ route('admin.settings.clear-activity-logs') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger" style="border-radius:8px;font-weight:600;">
                        <i class="fas fa-trash-can me-1"></i>Yes, Clear All Logs
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Toggle password visibility
function togglePasswordVisibility(button, fieldId) {
    const input = document.getElementById(fieldId);
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

// Payment Methods Dynamic Rows
(function () {
    var paymentMethodsContainer = document.getElementById('paymentMethodsContainer');
    var addPaymentMethodBtn = document.getElementById('addPaymentMethodBtn');
    var paymentMethodTemplate = `
        <div class="p-3 payment-method-row" data-index="__INDEX__">
            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                <strong class="small text-uppercase text-primary"><i class="fas fa-credit-card me-1"></i>Method __NUMBER__</strong>
                <button type="button" class="btn btn-link text-danger p-0 remove-payment-method-btn" title="Remove method"><i class="fas fa-trash-can"></i></button>
            </div>
            <div class="row g-2">
                <div class="col-md-4">
                    <label class="form-label small mb-1 fw-semibold">Method Name</label>
                    <input type="text" name="payment_methods[__INDEX__][label]" class="form-control form-control-sm payment-method-label" placeholder="e.g. Bank Transfer" required style="border-radius:6px;">
                </div>
                <div class="col-md-4">
                    <label class="form-label small mb-1 fw-semibold">Account Name</label>
                    <input type="text" name="payment_methods[__INDEX__][account_name]" class="form-control form-control-sm" placeholder="Optional" style="border-radius:6px;">
                </div>
                <div class="col-md-4">
                    <label class="form-label small mb-1 fw-semibold">Account Number</label>
                    <input type="text" name="payment_methods[__INDEX__][account_number]" class="form-control form-control-sm" placeholder="Optional" style="border-radius:6px;">
                </div>
                <div class="col-12 mt-2">
                    <label class="form-label small mb-1 fw-semibold">Payment QR / Image (Optional)</label>
                    <input type="file" name="payment_methods[__INDEX__][image]" class="form-control form-control-sm" accept="image/*" style="border-radius:6px;">
                    <input type="hidden" name="payment_methods[__INDEX__][existing_image]" value="">
                </div>
            </div>
        </div>`;

    function reindexPaymentMethodRows() {
        if (!paymentMethodsContainer) {
            return;
        }

        paymentMethodsContainer.querySelectorAll('.payment-method-row').forEach(function (row, index) {
            row.dataset.index = index;
            var numEl = row.querySelector('.small.text-uppercase');
            if (numEl) {
                numEl.innerHTML = '<i class="fas fa-credit-card me-1"></i>Method ' + (index + 1);
            }

            row.querySelectorAll('input, textarea').forEach(function (field) {
                if (!field.name) {
                    return;
                }
                field.name = field.name.replace(/payment_methods\[\d+\]/, 'payment_methods[' + index + ']');
            });
        });

        var alertEl = document.getElementById('noPaymentMethodsAlert');
        var rowCount = paymentMethodsContainer.querySelectorAll('.payment-method-row').length;
        if (alertEl) {
            alertEl.style.display = rowCount === 0 ? 'block' : 'none';
        }
    }

    if (addPaymentMethodBtn && paymentMethodsContainer) {
        addPaymentMethodBtn.addEventListener('click', function () {
            var index = paymentMethodsContainer.querySelectorAll('.payment-method-row').length;
            var wrapper = document.createElement('div');
            wrapper.innerHTML = paymentMethodTemplate
                .replace(/__INDEX__/g, index)
                .replace(/__NUMBER__/g, index + 1);

            paymentMethodsContainer.appendChild(wrapper.firstElementChild);
            reindexPaymentMethodRows();
        });

        paymentMethodsContainer.addEventListener('click', function (event) {
            var button = event.target.closest('.remove-payment-method-btn');
            if (!button) {
                return;
            }

            var row = button.closest('.payment-method-row');
            if (row) {
                row.remove();
                reindexPaymentMethodRows();
            }
        });

        reindexPaymentMethodRows();
    }
}());

// If there were validation errors in adminModal, auto-reopen it
@if($errors->has('name') || $errors->has('email') || $errors->has('password') || $errors->has('password_confirmation'))
    document.addEventListener('DOMContentLoaded', function() {
        var adminModalEl = document.getElementById('adminModal');
        if (adminModalEl) {
            bootstrap.Modal.getOrCreateInstance(adminModalEl).show();
        }
    });
@endif
</script>
@endsection
