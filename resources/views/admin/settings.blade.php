@extends('layouts.admin')

@section('title', 'Settings')
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

                        {{-- System Activity Logs & Audit Trail Portal Card --}}
                        <div class="col-md-6">
                            <div class="border rounded-3 p-4">
                                <div class="d-flex align-items-start gap-3">
                                    <div style="width:48px;height:48px;background:#e0f2fe;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <i class="fas fa-history" style="color:#0284c7;font-size:1.3rem;"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <h6 class="fw-bold mb-0">System Activity Logs & Audit Trail</h6>
                                            <span class="badge bg-primary rounded-pill">{{ number_format($recentLogsCount ?? 0) }} Records</span>
                                        </div>
                                        <p class="text-muted small mb-3">Audit products, orders, live deliveries, user logins, registrations, password resets, and store settings.</p>
                                        <a href="{{ route('admin.activity-logs') }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-external-link-alt me-1"></i>Open Activity Logs Portal
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Developer Error Log Viewer --}}
                        <div class="col-md-6">
                            <div class="border rounded-3 p-4">
                                <div class="d-flex align-items-start gap-3">
                                    <div style="width:48px;height:48px;background:#d1ecf1;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <i class="fas fa-terminal" style="color:#0c5460;font-size:1.3rem;"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-1">Developer Error Log Viewer</h6>
                                        <p class="text-muted small mb-3">View the last 50 lines of the Laravel error stack trace for debugging.</p>
                                        <button class="btn btn-info btn-sm text-white" type="button" data-bs-toggle="collapse" data-bs-target="#logViewer">
                                            <i class="fas fa-eye me-1"></i>View Raw Laravel Log
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

                                        <form action="{{ route('admin.settings.update-gcash') }}" method="POST" class="row g-2" enctype="multipart/form-data">
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
                                                <label class="form-label small">GCash Image</label>
                                                <input type="file" name="gcash_image" class="form-control form-control-sm @error('gcash_image') is-invalid @enderror" accept="image/*">
                                                @if(!empty($homepageSettings->gcash_image_path))
                                                    <div class="mt-2">
                                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($homepageSettings->gcash_image_path) }}" alt="GCash Image" style="max-width:80px;max-height:80px;object-fit:contain;border:1px solid #ddd;border-radius:8px;padding:4px;background:#fff;">
                                                    </div>
                                                @endif
                                                @error('gcash_image')
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

                        {{-- Additional Payment Methods --}}
                        <div class="col-12">
                            <div class="border rounded-3 p-4">
                                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
                                    <div>
                                        <h6 class="fw-bold mb-1">Checkout Payment Methods</h6>
                                        <p class="text-muted small mb-0">Add extra payment methods. Customers will see the method name, account name, account number, and proof upload like GCash.</p>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="addPaymentMethodBtn">
                                        <i class="fas fa-plus me-1"></i>Add Method
                                    </button>
                                </div>

                                @php
                                    $customPaymentMethods = old('payment_methods', $homepageSettings->payment_methods ?? []);
                                @endphp

                                <form action="{{ route('admin.settings.update-payment-methods') }}" method="POST" id="paymentMethodsForm" enctype="multipart/form-data">
                                    @csrf
                                    <div id="paymentMethodsContainer" class="d-grid gap-3">
                                        <div id="noPaymentMethodsAlert" class="alert alert-light border text-muted small text-center mb-0 py-3" style="{{ empty($customPaymentMethods) ? '' : 'display:none;' }}">
                                            <i class="fas fa-info-circle me-1"></i>No custom payment methods configured. Click <strong>+ Add Method</strong> to add one.
                                        </div>
                                        @foreach ($customPaymentMethods as $index => $method)
                                            <div class="border rounded-3 p-3 payment-method-row" data-index="{{ $index }}">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <strong class="small text-uppercase text-muted">Method {{ $loop->iteration }}</strong>
                                                    <button type="button" class="btn btn-link text-danger p-0 remove-payment-method-btn" title="Delete payment method">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                                <div class="row g-3">
                                                    <div class="col-md-4">
                                                        <label class="form-label small">Payment Method Name</label>
                                                        <input type="text" name="payment_methods[{{ $index }}][label]" class="form-control form-control-sm payment-method-label" value="{{ old('payment_methods.' . $index . '.label', $method['label'] ?? '') }}" placeholder="Bank Transfer">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small">Account Name</label>
                                                        <input type="text" name="payment_methods[{{ $index }}][account_name]" class="form-control form-control-sm" value="{{ old('payment_methods.' . $index . '.account_name', $method['account_name'] ?? '') }}" placeholder="Optional">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small">Account Number</label>
                                                        <input type="text" name="payment_methods[{{ $index }}][account_number]" class="form-control form-control-sm" value="{{ old('payment_methods.' . $index . '.account_number', $method['account_number'] ?? '') }}" placeholder="Optional">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small">Payment Image</label>
                                                        <input type="file" name="payment_methods[{{ $index }}][image]" class="form-control form-control-sm" accept="image/*">
                                                        <input type="hidden" name="payment_methods[{{ $index }}][existing_image]" value="{{ $method['image_path'] ?? '' }}">
                                                        @if(!empty($method['image_path']))
                                                            <div class="mt-2">
                                                                <img src="{{ \Illuminate\Support\Facades\Storage::url($method['image_path']) }}" alt="Payment Method Image" style="max-width:80px;max-height:80px;object-fit:contain;border:1px solid #ddd;border-radius:8px;padding:4px;background:#fff;">
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="mt-3 d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fas fa-save me-1"></i>Save Payment Methods
                                        </button>
                                    </div>
                                </form>

                                    <div class="mt-3">
                                        <div class="border rounded-3 p-4">
                                            <div class="d-flex align-items-start gap-3">
                                                <div style="width:48px;height:48px;background:#eaf7f9;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                                    <i class="fas fa-dollar-sign" style="color:#007d8f;font-size:1.3rem;"></i>
                                                </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-1">Delivery Fee</h6>
                                        <p class="text-muted small mb-3">Set the default delivery fee used for every new order. Existing orders keep their current fee.</p>

                                        <form action="{{ route('admin.settings.update-delivery-fee') }}" method="POST" class="row g-2">
                                            @csrf
                                            <div class="col-12">
                                                <label class="form-label small">Delivery Fee (PHP)</label>
                                                <input type="number" name="delivery_fee" step="0.01" min="0" class="form-control form-control-sm @error('delivery_fee') is-invalid @enderror" value="{{ old('delivery_fee', number_format($homepageSettings->delivery_fee ?? 50, 2, '.', '')) }}">
                                                @error('delivery_fee')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-save me-1"></i>Save Delivery Fee
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

(function () {
    var el = document.getElementById('settingsData');
    var logTailUrl = el.dataset.logTailUrl;
    var logSize = parseFloat(el.dataset.logSize);

    var paymentMethodsContainer = document.getElementById('paymentMethodsContainer');
    var addPaymentMethodBtn = document.getElementById('addPaymentMethodBtn');
    var paymentMethodTemplate = `
        <div class="border rounded-3 p-3 payment-method-row" data-index="__INDEX__">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <strong class="small text-uppercase text-muted">Method __NUMBER__</strong>
                <button type="button" class="btn btn-link text-danger p-0 remove-payment-method-btn"><i class="fas fa-trash"></i></button>
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small">Payment Method Name</label>
                    <input type="text" name="payment_methods[__INDEX__][label]" class="form-control form-control-sm payment-method-label" placeholder="Bank Transfer">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Account Name</label>
                    <input type="text" name="payment_methods[__INDEX__][account_name]" class="form-control form-control-sm" placeholder="Optional">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Account Number</label>
                    <input type="text" name="payment_methods[__INDEX__][account_number]" class="form-control form-control-sm" placeholder="Optional">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Payment Image</label>
                    <input type="file" name="payment_methods[__INDEX__][image]" class="form-control form-control-sm" accept="image/*">
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
            row.querySelector('.small.text-uppercase.text-muted').textContent = 'Method ' + (index + 1);

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
