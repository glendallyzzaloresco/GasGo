@extends('layouts.admin')

@section('title', 'User Management')
@section('nav-users', 'active')
@section('page-title', 'User Management')

@section('admin-styles')
<style>
    .nav-tabs .nav-link {
        color: #64748b;
        border-radius: 8px 8px 0 0;
        border: none;
        font-weight: 600;
        padding: 12px 24px;
        margin-right: 4px;
    }
    .nav-tabs .nav-link:hover {
        background: #f1f5f9;
    }
    .nav-tabs .nav-link.active {
        background: var(--gasgo-blue);
        color: white;
        border: none;
    }

    @media (max-width: 768px) {
        .nav-tabs {
            display: flex !important;
            flex-wrap: nowrap !important;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
            padding-bottom: 4px;
            scrollbar-width: none;
        }
        .nav-tabs::-webkit-scrollbar { display: none; }
        .nav-tabs .nav-link {
            flex-shrink: 0;
            padding: 8px 14px;
            font-size: 0.8rem;
        }
    }
    .user-table {
        font-size: .88rem;
    }
    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: #fff;
        font-size: .85rem;
        flex-shrink: 0;
    }
    .status-badge {
        font-size: .75rem;
        padding: 4px 10px;
        border-radius: 12px;
    }
    .stat-badge {
        display: inline-block;
        padding: 8px 12px;
        border-radius: 8px;
        background: #f1f5f9;
        font-size: .8rem;
        margin-right: 8px;
        margin-bottom: 8px;
    }
</style>
@endsection

@section('content')
<!-- Overview Stats -->
<div class="row g-4 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div><p>Total Riders</p><h3>{{ $totalRiders }}</h3></div>
                <div class="stat-icon orange"><i class="fas fa-motorcycle"></i></div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div><p>Active Riders</p><h3>{{ $activeRiders }}</h3></div>
                <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div><p>Total Customers</p><h3>{{ $totalCustomers }}</h3></div>
                <div class="stat-icon blue"><i class="fas fa-users"></i></div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div><p>Admin Accounts</p><h3>{{ $totalAdmins }}</h3></div>
                <div class="stat-icon purple"><i class="fas fa-user-shield"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Nav Tabs -->
<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="riders-tab" data-bs-toggle="tab" data-bs-target="#riders-content" type="button" role="tab">
            <i class="fas fa-motorcycle me-2"></i>Riders ({{ $totalRiders }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="customers-tab" data-bs-toggle="tab" data-bs-target="#customers-content" type="button" role="tab">
            <i class="fas fa-users me-2"></i>Customers ({{ $totalCustomers }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="admins-tab" data-bs-toggle="tab" data-bs-target="#admins-content" type="button" role="tab">
            <i class="fas fa-user-shield me-2"></i>Admin Accounts ({{ $totalAdmins }})
        </button>
    </li>
</ul>

<!-- Tab Content -->
<div class="tab-content">
    <!-- RIDERS TAB -->
    <div class="tab-pane fade show active" id="riders-content" role="tabpanel">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">All Riders</h6>
                    <button class="btn btn-sm" style="background:var(--gasgo-orange);color:#fff;" data-bs-toggle="modal" data-bs-target="#riderModal">
                        <i class="fas fa-plus me-1"></i>Add Rider
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table user-table mb-0">
                    <thead style="background:#f8f9fa;">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Deliveries</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($riderStats as $item)
                            @php $rider = $item['rider']; @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="user-avatar" style="background:linear-gradient(135deg,#1a6db0,#2196f3);">
                                            {{ strtoupper(substr($rider->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $rider->name }}</div>
                                            <small class="text-muted">{{ $rider->created_at->format('M d, Y') }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $rider->email }}</td>
                                <td>{{ $rider->phone ?? '—' }}</td>
                                <td>
                                    @if($item['availability'] === 'available')
                                        <span class="status-badge" style="background:#d1fae5;color:#047857;"><i class="fas fa-check-circle me-1"></i>Available</span>
                                    @elseif($item['availability'] === 'busy')
                                        <span class="status-badge" style="background:#fed7aa;color:#b45309;"><i class="fas fa-spinner me-1"></i>Busy</span>
                                    @elseif($item['availability'] === 'returning')
                                        <span class="status-badge" style="background:#bfdbfe;color:#1e40af;"><i class="fas fa-arrow-left me-1"></i>Returning</span>
                                    @else
                                        <span class="status-badge" style="background:#e5e7eb;color:#374151;"><i class="fas fa-circle me-1"></i>Offline</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="stat-badge">{{ $item['totalDeliveries'] }} Total</div>
                                    <div class="stat-badge" style="background:#dcfce7;color:#166534;">{{ $item['completedDeliveries'] }} ✓</div>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" 
                                        onclick="openEditRiderModal(event)"
                                        data-rider-id="{{ $item['rider']->rider->id ?? '' }}"
                                        data-rider-name="{{ $rider->name }}"
                                        data-rider-email="{{ $rider->email }}"
                                        data-rider-phone="{{ $rider->phone ?? '' }}"
                                        data-rider-vehicle="{{ $item['rider']->rider->vehicle_type ?? 'Motorcycle' }}"
                                        data-rider-plate="{{ $item['rider']->rider->plate_number ?? '' }}"
                                        style="border-radius:6px;">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fas fa-motorcycle" style="font-size:2rem;opacity:0.3;display:block;margin-bottom:8px;"></i>
                                    No riders found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- CUSTOMERS TAB -->
    <div class="tab-pane fade" id="customers-content" role="tabpanel">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-bold">All Customers</h6>
            </div>
            <div class="table-responsive">
                <table class="table user-table mb-0">
                    <thead style="background:#f8f9fa;">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Orders</th>
                            <th>Product / Delivery</th>
                            <th>Loyalty Tier</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customerStats as $item)
                            @php $customer = $item['customer']; @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="user-avatar" style="background:linear-gradient(135deg,#065f46,#10b981);">
                                            {{ strtoupper(substr($customer->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $customer->name }}</div>
                                            <small class="text-muted">{{ $customer->created_at->format('M d, Y') }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $customer->email }}</td>
                                <td>{{ $customer->phone ?? '—' }}</td>
                                <td><span class="stat-badge">{{ $item['totalOrders'] }} Orders</span></td>
                                <td>
                                    <div class="stat-badge">₱{{ number_format($item['productTotal'], 2) }}</div>
                                    <div class="stat-badge" style="background:#e0f2fe;color:#0369a1;">₱{{ number_format($item['deliveryTotal'], 2) }} Del</div>
                                </td>
                                <td>
                                    @if($item['loyaltyTier'])
                                        <span class="badge bg-{{ $item['loyaltyBadge'] }}" style="padding:6px 12px;border-radius:20px;">
                                            <i class="fas fa-crown me-1"></i>{{ $item['loyaltyTier'] }} ({{ $item['loyaltyPoints'] }} pts)
                                        </span>
                                    @else
                                        <span class="text-muted" style="font-size:.85rem;">No Tier</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fas fa-users" style="font-size:2rem;opacity:0.3;display:block;margin-bottom:8px;"></i>
                                    No customers found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ADMINS TAB -->
    <div class="tab-pane fade" id="admins-content" role="tabpanel">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">Admin Accounts</h6>
                    <button class="btn btn-sm" style="background:var(--gasgo-blue);color:#fff;" data-bs-toggle="modal" data-bs-target="#adminModal">
                        <i class="fas fa-plus me-1"></i>Add Admin
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table user-table mb-0">
                    <thead style="background:#f8f9fa;">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($admins as $admin)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="user-avatar" style="background:linear-gradient(135deg,#7c3aed,#a855f7);">
                                            {{ strtoupper(substr($admin->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $admin->name }}</div>
                                            @if($admin->id === Auth::id())
                                                <span class="badge bg-primary" style="font-size:.7rem;">You</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $admin->email }}</td>
                                <td><span class="status-badge" style="background:#f3e8ff;color:#7e22ce;"><i class="fas fa-shield-alt me-1"></i>Administrator</span></td>
                                <td>{{ $admin->created_at->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No admin accounts found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
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
                            <label class="form-label fw-bold" style="font-size:.88rem;">Vehicle Type</label>
                            <select name="vehicle_type" class="form-select" style="border-radius:10px;">
                                <option value="Motorcycle">Motorcycle</option>
                                <option value="Motorcycle with Sidecar (Tricycle)">Motorcycle with Sidecar (Tricycle)</option>
                                <option value="E-Bike">E-Bike</option>
                                <option value="Multicab">Multicab</option>
                                <option value="Delivery Van">Delivery Van</option>
                                <option value="Truck">Truck</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="font-size:.88rem;">Plate Number (optional)</label>
                            <input type="text" name="plate_number" class="form-control" style="border-radius:10px;" placeholder="e.g. ABC 1234">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="font-size:.88rem;">Password <span class="text-danger">*</span></label>
                            <div class="input-group" style="border-radius:10px;overflow:hidden;">
                                <input type="password" name="password" id="riderPassword" class="form-control" style="border-radius:10px 0 0 10px;" placeholder="Min 8 chars, letters & numbers" minlength="8" pattern="(?=.*[A-Za-z])(?=.*\d).{8,}" title="Password must be at least 8 characters and contain both letters and numbers." required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility(this, 'riderPassword')" style="border-radius:0 10px 10px 0;">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="font-size:.88rem;">Confirm Password <span class="text-danger">*</span></label>
                            <div class="input-group" style="border-radius:10px;overflow:hidden;">
                                <input type="password" name="password_confirmation" id="riderPasswordConfirm" class="form-control" style="border-radius:10px 0 0 10px;" placeholder="Min 8 chars, letters & numbers" minlength="8" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility(this, 'riderPasswordConfirm')" style="border-radius:0 10px 10px 0;">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
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

<!-- Edit Rider Modal -->
<div class="modal fade" id="editRiderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:16px;">
            <form id="editRiderForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" id="editRiderId" name="rider_id">
                <div class="modal-header" style="border-bottom:none;padding:24px 24px 0;">
                    <h5 class="modal-title fw-bold" style="color:var(--gasgo-blue);"><i class="fas fa-edit me-2" style="color:var(--gasgo-orange);"></i>Edit Rider Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="padding:24px;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="font-size:.88rem;">Full Name <span class="text-danger">*</span></label>
                            <input type="text" id="editName" name="name" class="form-control" style="border-radius:10px;" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="font-size:.88rem;">Email <span class="text-danger">*</span></label>
                            <input type="email" id="editEmail" name="email" class="form-control" style="border-radius:10px;" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="font-size:.88rem;">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" id="editPhone" name="phone" class="form-control" style="border-radius:10px;" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="font-size:.88rem;">Vehicle Type</label>
                            <select id="editVehicleType" name="vehicle_type" class="form-select" style="border-radius:10px;">
                                <option value="Motorcycle">Motorcycle</option>
                                <option value="Motorcycle with Sidecar (Tricycle)">Motorcycle with Sidecar (Tricycle)</option>
                                <option value="E-Bike">E-Bike</option>
                                <option value="Multicab">Multicab</option>
                                <option value="Delivery Van">Delivery Van</option>
                                <option value="Truck">Truck</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="font-size:.88rem;">Plate Number (optional)</label>
                            <input type="text" id="editPlateNumber" name="plate_number" class="form-control" style="border-radius:10px;" placeholder="e.g. ABC 1234">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold" style="font-size:.88rem;">Password</label>
                            <p class="text-muted" style="font-size:.8rem;margin-bottom:6px;">Leave empty to keep current password (min 8 chars with letters & numbers)</p>
                            <div class="input-group" style="border-radius:10px;overflow:hidden;">
                                <input type="password" id="editPassword" name="password" class="form-control" style="border-radius:10px 0 0 10px;" placeholder="Min 8 chars, letters & numbers" minlength="8" pattern="(?=.*[A-Za-z])(?=.*\d).{8,}" title="Password must be at least 8 characters and contain both letters and numbers.">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility(this, 'editPassword')" style="border-radius:0 10px 10px 0;">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:none;padding:0 24px 24px;">
                    <button type="button" class="btn" data-bs-dismiss="modal" style="border-radius:10px;">Cancel</button>
                    <button type="submit" class="btn" style="background:var(--gasgo-orange);color:#fff;border-radius:10px;font-weight:600;padding:10px 28px;">
                        <i class="fas fa-save me-1"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Admin Modal -->
<div class="modal fade" id="adminModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:16px;">
            <form action="{{ route('admin.settings.admin-users.store') }}" method="POST">
                @csrf
                <div class="modal-header" style="border-bottom:none;padding:24px 24px 0;">
                    <h5 class="modal-title fw-bold" style="color:var(--gasgo-blue);"><i class="fas fa-user-shield me-2" style="color:#1a6db0;"></i>Add New Admin Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="padding:24px;">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold" style="font-size:.88rem;">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" style="border-radius:10px;" placeholder="Full name" value="{{ old('name') }}" required>
                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold" style="font-size:.88rem;">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" style="border-radius:10px;" placeholder="Email address" value="{{ old('email') }}" required>
                            @error('email')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="font-size:.88rem;">Password <span class="text-danger">*</span></label>
                            <div class="input-group" style="border-radius:10px;overflow:hidden;">
                                <input type="password" name="password" id="adminPassword" class="form-control" style="border-radius:10px 0 0 10px;" placeholder="Min 8 chars, letters & numbers" minlength="8" pattern="(?=.*[A-Za-z])(?=.*\d).{8,}" title="Password must be at least 8 characters and contain both letters and numbers." required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility(this, 'adminPassword')" style="border-radius:0 10px 10px 0;">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="font-size:.88rem;">Confirm Password <span class="text-danger">*</span></label>
                            <div class="input-group" style="border-radius:10px;overflow:hidden;">
                                <input type="password" name="password_confirmation" id="adminPasswordConfirm" class="form-control" style="border-radius:10px 0 0 10px;" placeholder="Confirm password" minlength="8" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility(this, 'adminPasswordConfirm')" style="border-radius:0 10px 10px 0;">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:none;padding:0 24px 24px;">
                    <button type="button" class="btn" data-bs-dismiss="modal" style="border-radius:10px;">Cancel</button>
                    <button type="submit" class="btn" style="background:var(--gasgo-blue);color:#fff;border-radius:10px;font-weight:600;padding:10px 28px;">
                        <i class="fas fa-user-plus me-1"></i>Create Admin
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

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

    // Open edit rider modal
    function openEditRiderModal(event) {
        const button = event.target.closest('button');
        const riderId = button.dataset.riderId;
        
        if (!riderId) {
            alert('Rider ID not found');
            return;
        }
        
        document.getElementById('editRiderId').value = riderId;
        document.getElementById('editName').value = button.dataset.riderName;
        document.getElementById('editEmail').value = button.dataset.riderEmail;
        document.getElementById('editPhone').value = button.dataset.riderPhone;
        document.getElementById('editVehicleType').value = button.dataset.riderVehicle || 'Motorcycle';
        document.getElementById('editPlateNumber').value = button.dataset.riderPlate || '';
        document.getElementById('editPassword').value = '';  // Clear password field for security
        
        // Update the form action
        document.getElementById('editRiderForm').action = `/admin/riders/${riderId}`;
        
        // Show the modal
        const modal = new bootstrap.Modal(document.getElementById('editRiderModal'));
        modal.show();
    }

    // Handle edit rider form submission
    document.getElementById('editRiderForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const riderId = document.getElementById('editRiderId').value;
        const name = document.getElementById('editName').value;
        const email = document.getElementById('editEmail').value;
        const phone = document.getElementById('editPhone').value;
        const vehicleType = document.getElementById('editVehicleType').value;
        const plateNumber = document.getElementById('editPlateNumber').value;
        const password = document.getElementById('editPassword').value;
        
        // Build FormData manually
        const formData = new FormData();
        formData.append('_method', 'PUT');
        formData.append('name', name);
        formData.append('email', email);
        formData.append('phone', phone);
        formData.append('vehicle_type', vehicleType);
        formData.append('plate_number', plateNumber);
        if (password) {
            formData.append('password', password);
        }
        
        fetch(`/admin/riders/${riderId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('editRiderModal')).hide();
                
                // Show success message
                showSuccessMessage('Rider information updated successfully!');
                
                // Reload page after a brief delay
                setTimeout(() => location.reload(), 1500);
            } else {
                alert('Error updating rider: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to update rider information');
        });
    });

    // Helper function to show success message
    function showSuccessMessage(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success alert-dismissible fade show';
        alertDiv.style.cssText = 'border-radius:12px;position:fixed;top:20px;right:20px;z-index:9999;max-width:400px;box-shadow:0 4px 12px rgba(0,0,0,0.15);';
        alertDiv.innerHTML = `
            <i class="fas fa-check-circle me-2" style="color:#28a745;"></i>
            <strong>${message}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alertDiv);
        
        // Auto-hide after 4 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 4000);
    }
</script>
@endsection
