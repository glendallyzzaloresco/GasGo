@extends('layouts.admin')

@section('title', 'Settings - GasGo Admin')
@section('page-title', 'Settings')
@section('nav-settings', 'active')

@section('content')
<div class="container-fluid px-0">

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- System Information -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2 text-primary"></i>System Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 rounded-3" style="background:#f8f9fa;">
                                <div class="text-muted small mb-1">Application Name</div>
                                <div class="fw-semibold">{{ $appName }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded-3" style="background:#f8f9fa;">
                                <div class="text-muted small mb-1">Environment</div>
                                <div class="fw-semibold">
                                    <span class="badge {{ $appEnv === 'production' ? 'bg-success' : 'bg-warning text-dark' }}">
                                        {{ ucfirst($appEnv) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded-3" style="background:#f8f9fa;">
                                <div class="text-muted small mb-1">Debug Mode</div>
                                <div class="fw-semibold">
                                    <span class="badge {{ $appDebug ? 'bg-danger' : 'bg-success' }}">
                                        {{ $appDebug ? 'ON' : 'OFF' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded-3" style="background:#f8f9fa;">
                                <div class="text-muted small mb-1">Laravel Version</div>
                                <div class="fw-semibold">{{ $laravelVersion }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded-3" style="background:#f8f9fa;">
                                <div class="text-muted small mb-1">PHP Version</div>
                                <div class="fw-semibold">{{ $phpVersion }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded-3" style="background:#f8f9fa;">
                                <div class="text-muted small mb-1">Database</div>
                                <div class="fw-semibold">{{ ucfirst($dbConnection) }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded-3" style="background:#f8f9fa;">
                                <div class="text-muted small mb-1">Cache Driver</div>
                                <div class="fw-semibold">{{ ucfirst($cacheDriver) }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded-3" style="background:#f8f9fa;">
                                <div class="text-muted small mb-1">Queue Driver</div>
                                <div class="fw-semibold">{{ ucfirst($queueDriver) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Maintenance Actions -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-tools me-2 text-warning"></i>Maintenance</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">

                        {{-- Clear Cache --}}
                        <div class="col-md-6">
                            <div class="border rounded-3 p-4">
                                <div class="d-flex align-items-start gap-3">
                                    <div style="width:48px;height:48px;background:#fff3cd;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <i class="fas fa-broom" style="color:#856404;font-size:1.3rem;"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-1">Clear Cache</h6>
                                        <p class="text-muted small mb-3">Clears application cache and compiled views. Use this after making configuration changes.</p>
                                        <form action="{{ route('admin.settings.clear-cache') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Clear application cache and views?')">
                                                <i class="fas fa-broom me-1"></i>Clear Cache & Views
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Clear Logs --}}
                        <div class="col-md-6">
                            <div class="border rounded-3 p-4">
                                <div class="d-flex align-items-start gap-3">
                                    <div style="width:48px;height:48px;background:#f8d7da;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <i class="fas fa-file-alt" style="color:#721c24;font-size:1.3rem;"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-1">Clear Log File</h6>
                                        <p class="text-muted small mb-3">Empties the Laravel log file. Use this to free up disk space when the log file is too large.</p>
                                        <form action="{{ route('admin.settings.clear-logs') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Clear the Laravel log file? This cannot be undone.')">
                                                <i class="fas fa-trash me-1"></i>Clear Logs
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Log Viewer --}}
                        <div class="col-md-6">
                            <div class="border rounded-3 p-4">
                                <div class="d-flex align-items-start gap-3">
                                    <div style="width:48px;height:48px;background:#d1ecf1;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <i class="fas fa-eye" style="color:#0c5460;font-size:1.3rem;"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-1">Log Viewer</h6>
                                        <p class="text-muted small mb-3">View the last 50 lines of the Laravel error log to diagnose issues.</p>
                                        <button class="btn btn-info btn-sm text-white" type="button" data-bs-toggle="collapse" data-bs-target="#logViewer">
                                            <i class="fas fa-eye me-1"></i>View Logs
                                        </button>
                                    </div>
                                </div>
                                <div class="collapse mt-3" id="logViewer">
                                    <pre id="logContent" class="bg-dark text-light p-3 rounded" style="font-size:.75rem;max-height:300px;overflow-y:auto;">Loading...</pre>
                                </div>
                            </div>
                        </div>

                        {{-- Storage Info --}}
                        <div class="col-md-6">
                            <div class="border rounded-3 p-4">
                                <div class="d-flex align-items-start gap-3">
                                    <div style="width:48px;height:48px;background:#d4edda;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <i class="fas fa-hdd" style="color:#155724;font-size:1.3rem;"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-1">Storage Info</h6>
                                        <p class="text-muted small mb-2">Current log file size and storage status.</p>
                                        @php
                                            $logPath = storage_path('logs/laravel.log');
                                            $logSize = file_exists($logPath) ? round(filesize($logPath) / 1024, 2) : 0;
                                        @endphp
                                        <div class="d-flex align-items-center gap-2 mt-2">
                                            <span class="text-muted small">Log file size:</span>
                                            <span class="badge {{ $logSize > 1024 ? 'bg-danger' : ($logSize > 100 ? 'bg-warning text-dark' : 'bg-success') }}">
                                                {{ $logSize }} KB
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Homepage Maintenance --}}
                        <div class="col-md-6">
                            <div class="border rounded-3 p-4">
                                <div class="d-flex align-items-start gap-3">
                                    <div style="width:48px;height:48px;background:#fff5e6;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <i class="fas fa-image" style="color:#e07d0a;font-size:1.3rem;"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-1">Homepage Maintenance</h6>
                                        <p class="text-muted small mb-3">Modify what customer and guest users see on the homepage: logo, brand name, hero image, and promo image.</p>
                                        <a href="{{ route('admin.settings.homepage') }}" class="btn btn-sm" style="background:var(--gasgo-orange);color:#fff;">
                                            <i class="fas fa-sliders me-1"></i>Open Homepage Controls
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- GCash Account Settings --}}
                        <div class="col-md-6">
                            <div class="border rounded-3 p-4">
                                <div class="d-flex align-items-start gap-3">
                                    <div style="width:48px;height:48px;background:#e7fff0;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <i class="fas fa-mobile-alt" style="color:#007dfe;font-size:1.3rem;"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-1">GCash Payment Account</h6>
                                        <p class="text-muted small mb-3">Configure GCash account details for customer payments.</p>

                                        <form action="{{ route('admin.settings.update-gcash') }}" method="POST" class="row g-2">
                                            @csrf
                                            <div class="col-12">
                                                <label class="form-label small">GCash Account Number</label>
                                                <input type="text" name="gcash_account_number" class="form-control form-control-sm @error('gcash_account_number') is-invalid @enderror" placeholder="09XX XXX XXXX" value="{{ old('gcash_account_number', $homepageSettings->gcash_account_number ?? '') }}">
                                                @error('gcash_account_number')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label small">Account Name</label>
                                                <input type="text" name="gcash_account_name" class="form-control form-control-sm @error('gcash_account_name') is-invalid @enderror" placeholder="Name on GCash account" value="{{ old('gcash_account_name', $homepageSettings->gcash_account_name ?? '') }}">
                                                @error('gcash_account_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-success btn-sm">
                                                    <i class="fas fa-save me-1"></i>Save GCash Details
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Add Admin Account --}}
                        <div class="col-md-6">
                            <div class="border rounded-3 p-4">
                                <div class="d-flex align-items-start gap-3">
                                    <div style="width:48px;height:48px;background:#e7f1ff;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <i class="fas fa-user-shield" style="color:#1a6db0;font-size:1.3rem;"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-1">Add Admin Account</h6>
                                        <p class="text-muted small mb-3">Create a new administrator account with full admin access.</p>

                                        <form action="{{ route('admin.settings.admin-users.store') }}" method="POST" class="row g-2">
                                            @csrf
                                            <div class="col-12">
                                                <input type="text" name="name" class="form-control form-control-sm @error('name') is-invalid @enderror" placeholder="Full name" value="{{ old('name') }}" required>
                                                @error('name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-12">
                                                <input type="email" name="email" class="form-control form-control-sm @error('email') is-invalid @enderror" placeholder="Email address" value="{{ old('email') }}" required>
                                                @error('email')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <input type="password" name="password" class="form-control form-control-sm @error('password') is-invalid @enderror" placeholder="Password" required>
                                            </div>
                                            <div class="col-md-6">
                                                <input type="password" name="password_confirmation" class="form-control form-control-sm" placeholder="Confirm password" required>
                                                @error('password')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-user-plus me-1"></i>Create Admin
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<div id="settingsData"
    data-log-tail-url="{{ url('/admin/settings/log-tail') }}"
    data-log-size="{{ $logSize ?? 0 }}"
    style="display:none;"
></div>
<script>
(function () {
    var el = document.getElementById('settingsData');
    var logTailUrl = el.dataset.logTailUrl;
    var logSize = parseFloat(el.dataset.logSize);

    var logViewer = document.getElementById('logViewer');
    if (logViewer) {
        logViewer.addEventListener('show.bs.collapse', function () {
            fetch(logTailUrl)
                .then(function (r) { return r.text(); })
                .then(function (text) {
                    document.getElementById('logContent').textContent = text || 'Log file is empty.';
                })
                .catch(function () {
                    document.getElementById('logContent').textContent =
                        logSize > 0
                            ? 'Log endpoint not available.\nLog file exists (' + logSize + ' KB).\nUse Clear Logs button to empty it.'
                            : 'Log file is empty.';
                });
        });
    }
}());
</script>
@endsection
