@extends('layouts.admin')

@section('title', 'Riders')
@section('nav-riders', 'active')
@section('page-title', 'Rider Management')

@section('admin-styles')
<style>
    .rider-card {
        background:#fff; border-radius:16px; box-shadow:0 4px 15px rgba(0,0,0,.06);
        padding:24px; transition:transform .3s; position:relative;
    }
    .rider-card:hover { transform:translateY(-4px); }
    .rider-avatar {
        width:64px; height:64px; border-radius:50%; display:flex; align-items:center; justify-content:center;
        font-size:1.5rem; color:#fff; font-weight:700;
    }
    .rider-stat { text-align:center; }
    .rider-stat .value { font-size:1.3rem; font-weight:700; color:var(--gasgo-blue); }
    .avail-returning { background:#0dcaf0; }
    .avail-offline { background:#999; }

    @media (max-width: 768px) {
        .rider-card {
            padding: 14px 12px;
            border-radius: 14px;
        }
        .rider-avatar {
            width: 48px;
            height: 48px;
            font-size: 1.2rem;
        }
        .rider-stat .value {
            font-size: 1.05rem;
        }
    }
</style>
@endsection

@php
    $availableCount = $riders->filter(fn($r) => $r->rider && $r->rider->availability === 'available')->count();
    $busyCount = $riders->filter(fn($r) => $r->rider && $r->rider->availability === 'busy')->count();
    $offlineCount = $riders->filter(fn($r) => !$r->rider || $r->rider->availability === 'offline')->count();
    $avatarColors = [
        'available' => 'linear-gradient(135deg,#27ae60,#2ecc71)',
        'busy' => 'linear-gradient(135deg,#f7941d,#ff6b35)',
        'offline' => 'linear-gradient(135deg,#999,#bbb)',
    ];
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <span class="badge bg-success me-2"><i class="fas fa-circle me-1" style="font-size:.5rem;"></i>{{ $availableCount }} Available</span>
        <span class="badge bg-warning text-dark me-2"><i class="fas fa-circle me-1" style="font-size:.5rem;"></i>{{ $busyCount }} Busy</span>
        <span class="badge bg-secondary"><i class="fas fa-circle me-1" style="font-size:.5rem;"></i>{{ $offlineCount }} Offline</span>
    </div>
    <button class="btn" style="background:var(--gasgo-orange);color:#fff;border-radius:12px;font-weight:600;padding:10px 22px;" data-bs-toggle="modal" data-bs-target="#riderModal">
        <i class="fas fa-plus me-2"></i>Add Rider
    </button>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" style="border-radius:12px;">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" style="border-radius:12px;">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    @forelse($riders as $riderUser)
        @php
            $rider = $riderUser->rider;
            $availability = $rider->availability ?? 'offline';
            $totalDel = $deliveryStats[$riderUser->id] ?? 0;
            $completedDel = $completedStats[$riderUser->id] ?? 0;
            $todayDel = $todayDeliveries[$riderUser->id] ?? 0;
            $activeDel = $activeDeliveries[$riderUser->id] ?? null;
            $initial = strtoupper(substr($riderUser->name, 0, 1));
            $avatarBg = $avatarColors[$availability] ?? $avatarColors['offline'];
        @endphp
        <div class="col-lg-4 col-md-6">
            <div class="rider-card" data-rider-id="{{ $rider->id }}" data-user-id="{{ $riderUser->id }}">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rider-avatar" style="background:linear-gradient(135deg,#1a6db0,#2196f3);">{{ $initial }}</div>
                    <div>
                        <h6 class="fw-bold mb-0">{{ $riderUser->name }}</h6>
                        <small class="text-muted">{{ $riderUser->phone ?? '—' }}</small><br>
                        <small data-status="{{ $availability }}" data-status-text>
                            @if($availability === 'available')
                                <span class="avail-dot avail-available"></span> <span class="text-success fw-bold">Available</span>
                            @elseif($availability === 'busy')
                                <span class="avail-dot avail-busy"></span> <small class="fw-bold" style="color:var(--gasgo-orange);">Busy</small>
                            @elseif($availability === 'returning')
                                <span class="avail-dot avail-returning"></span> <small class="fw-bold" style="color:#0dcaf0;">Returning to Store</small>
                            @else
                                <span class="avail-dot avail-offline"></span> <small class="text-muted fw-bold">Offline</small>
                            @endif
                        </small>
                    </div>
                </div>
                <div class="d-flex justify-content-around mb-3 p-2" style="background:#f8f9fa;border-radius:12px;">
                    <div class="rider-stat"><div class="value" data-total-deliveries>{{ $totalDel }}</div><div class="label">Total Deliveries</div></div>
                    <div class="rider-stat"><div class="value" data-completed-deliveries>{{ $completedDel }}</div><div class="label">Completed</div></div>
                    <div class="rider-stat"><div class="value" data-today-deliveries>{{ $todayDel }}</div><div class="label">Today</div></div>
                </div>
                @if($rider && $rider->vehicle_type)
                    <div class="mb-2" style="font-size:.82rem;">
                        <i class="fas fa-motorcycle me-1" style="color:var(--gasgo-orange);"></i>
                        <strong>{{ $rider->vehicle_type }}</strong>
                        @if($rider->plate_number) — {{ $rider->plate_number }} @endif
                    </div>
                    @if($rider->license_number)
                        <div class="mb-2" style="font-size:.82rem;">
                            <i class="fas fa-id-card me-1" style="color:var(--gasgo-blue);"></i> License: {{ $rider->license_number }}
                        </div>
                    @endif
                @elseif($rider)
                @endif
                @if($activeDel)
                    <div class="mb-2" style="font-size:.82rem;color:var(--gasgo-orange);">
                        <i class="fas fa-shipping-fast me-1"></i> Currently delivering <strong>#{{ $activeDel->order->order_number ?? '—' }}</strong>
                    </div>
                @endif
                <div class="d-flex gap-2 mt-3 flex-wrap align-items-center" data-button-container>
                    @if($rider)
                        <button class="btn btn-sm btn-outline-primary" 
                            onclick="openEditRiderModal(event)"
                            data-rider-id="{{ $rider->id }}"
                            data-rider-name="{{ $riderUser->name }}"
                            data-rider-email="{{ $riderUser->email }}"
                            data-rider-phone="{{ $riderUser->phone ?? '' }}"
                            style="border-radius:8px;font-weight:600;padding:6px 12px;">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteRider(event)" data-rider-id="{{ $rider->id }}" style="border-radius:8px;font-weight:600;padding:6px 14px;">
                            <i class="fas fa-trash"></i>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="text-center py-5 text-muted">
                <i class="fas fa-motorcycle fa-3x mb-3" style="color:#ddd;"></i>
                <p>No riders registered yet. Click <strong>Add Rider</strong> to create one.</p>
            </div>
        </div>
    @endforelse
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
                            <label class="form-label fw-bold" style="font-size:.88rem;">Password <span class="text-danger">*</span></label>
                            <div class="input-group" style="border-radius:10px;overflow:hidden;">
                                <input type="password" name="password" id="riderPassword" class="form-control" style="border-radius:10px 0 0 10px;" placeholder="Min 6 characters" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility(this, 'riderPassword')" style="border-radius:0 10px 10px 0;">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="font-size:.88rem;">Confirm Password <span class="text-danger">*</span></label>
                            <div class="input-group" style="border-radius:10px;overflow:hidden;">
                                <input type="password" name="password_confirmation" id="riderPasswordConfirm" class="form-control" style="border-radius:10px 0 0 10px;" placeholder="Min 6 characters" required>
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
                        <div class="col-md-12">
                            <label class="form-label fw-bold" style="font-size:.88rem;">Password</label>
                            <p class="text-muted" style="font-size:.8rem;">Leave empty to keep current password</p>
                            <div class="input-group" style="border-radius:10px;overflow:hidden;">
                                <input type="password" id="editPassword" name="password" class="form-control" style="border-radius:10px 0 0 10px;" placeholder="Min 6 characters">
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
        
        document.getElementById('editRiderId').value = riderId;
        document.getElementById('editName').value = button.dataset.riderName;
        document.getElementById('editEmail').value = button.dataset.riderEmail;
        document.getElementById('editPhone').value = button.dataset.riderPhone;
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
        const password = document.getElementById('editPassword').value;
        
        // Build FormData manually
        const formData = new FormData();
        formData.append('_method', 'PUT');
        formData.append('name', name);
        formData.append('email', email);
        formData.append('phone', phone);
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

    // Refresh rider stats only when status is manually updated
    function refreshRiderStats(cardElement, riderId, userId) {
        fetch(`/admin/riders/${riderId}/stats`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update availability status
                const statusElement = cardElement.querySelector('[data-status]');
                if (statusElement && data.availability !== statusElement.dataset.status) {
                    statusElement.dataset.status = data.availability;
                    const statusDot = cardElement.querySelector('.avail-dot');
                    const statusText = cardElement.querySelector('[data-status-text]');
                    
                    statusDot.className = 'avail-dot';
                    if (data.availability === 'available') {
                        statusDot.classList.add('avail-available');
                        statusText.innerHTML = '<span class="text-success fw-bold">Available</span>';
                    } else if (data.availability === 'busy') {
                        statusDot.classList.add('avail-busy');
                        statusText.innerHTML = '<small class="fw-bold" style="color:var(--gasgo-orange);">Busy</small>';
                    } else if (data.availability === 'returning') {
                        statusDot.classList.add('avail-returning');
                        statusText.innerHTML = '<small class="fw-bold" style="color:#0dcaf0;">Returning to Store</small>';
                    } else {
                        statusDot.classList.add('avail-offline');
                        statusText.innerHTML = '<small class="text-muted fw-bold">Offline</small>';
                    }
                    
                    // Update buttons visibility
                    updateStatusButtons(cardElement, data.availability, riderId);
                }
                
                // Update stats
                const totalEl = cardElement.querySelector('[data-total-deliveries]');
                const completedEl = cardElement.querySelector('[data-completed-deliveries]');
                const todayEl = cardElement.querySelector('[data-today-deliveries]');
                
                if (totalEl) totalEl.textContent = data.total_deliveries || 0;
                if (completedEl) completedEl.textContent = data.completed_deliveries || 0;
                if (todayEl) todayEl.textContent = data.today_deliveries || 0;
            }
        })
        .catch(error => console.error('Error fetching rider stats:', error));
    }

    function updateStatusButtons(cardElement, currentStatus, riderId) {
        const buttonContainer = cardElement.querySelector('[data-button-container]');
        if (!buttonContainer) return;
        
        // Remove all status buttons
        buttonContainer.querySelectorAll('[data-status-btn]').forEach(btn => btn.remove());
        
        // Add available button if not already available
        if (currentStatus !== 'available') {
            const btn = document.createElement('button');
            btn.className = 'btn btn-sm btn-primary';
            btn.style.cssText = 'border-radius:8px;font-weight:600;padding:6px 12px;';
            btn.textContent = 'Set Available';
            btn.setAttribute('data-status-btn', 'true');
            btn.onclick = () => setRiderStatus(riderId, 'available');
            buttonContainer.appendChild(btn);
        }
        
        // Add busy button if not already busy
        if (currentStatus !== 'busy') {
            const btn = document.createElement('button');
            btn.className = 'btn btn-sm btn-warning';
            btn.style.cssText = 'border-radius:8px;font-weight:600;padding:6px 12px;';
            btn.textContent = 'Set Busy';
            btn.setAttribute('data-status-btn', 'true');
            btn.onclick = () => setRiderStatus(riderId, 'busy');
            buttonContainer.appendChild(btn);
        }
        
        // Add returning button if not already returning
        if (currentStatus !== 'returning') {
            const btn = document.createElement('button');
            btn.className = 'btn btn-sm btn-info';
            btn.style.cssText = 'border-radius:8px;font-weight:600;padding:6px 12px;';
            btn.textContent = 'Set Returning';
            btn.setAttribute('data-status-btn', 'true');
            btn.onclick = () => setRiderStatus(riderId, 'returning');
            buttonContainer.appendChild(btn);
        }
        
        // Add offline button if not already offline
        if (currentStatus !== 'offline') {
            const btn = document.createElement('button');
            btn.className = 'btn btn-sm btn-secondary';
            btn.style.cssText = 'border-radius:8px;font-weight:600;padding:6px 12px;';
            btn.textContent = 'Set Offline';
            btn.setAttribute('data-status-btn', 'true');
            btn.onclick = () => setRiderStatus(riderId, 'offline');
            buttonContainer.appendChild(btn);
        }
    }

    function setRiderStatus(buttonOrRiderId, statusParam) {
        // Handle both calling conventions:
        // 1. From inline onclick: setRiderStatus(this) - button element
        // 2. From dynamic buttons: setRiderStatus(riderId, status) - riderId number
        let riderId, status, card;
        
        if (typeof buttonOrRiderId === 'object' && buttonOrRiderId.dataset) {
            // Called from inline onclick with button element (this)
            const button = buttonOrRiderId;
            riderId = button.dataset.riderId;
            status = button.dataset.status;
            // Find the rider card by going up the DOM tree
            card = button.closest('[data-rider-id]');
        } else {
            // Called from dynamic button with riderId and status
            riderId = buttonOrRiderId;
            status = statusParam;
            card = document.querySelector(`[data-rider-id="${riderId}"]`);
        }
        
        if (!riderId || !status) {
            console.error('Error: Could not determine rider or status', { riderId, status });
            return;
        }
        
        fetch(`/admin/riders/${riderId}/availability`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ availability: status })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccessMessage('Rider status updated successfully!');
                // Refresh stats instead of full page reload
                if (card) {
                    const userId = card.dataset.userId;
                    refreshRiderStats(card, riderId, userId);
                }
            } else {
                alert('Error updating rider status: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to update rider status');
        });
    }

    async function deleteRider(event) {
        const button = event.target.closest('button');
        const riderId = button.dataset.riderId;
        
        const confirmed = await window.gasgoConfirm({
            title: 'Delete Rider',
            text: 'Are you sure you want to delete this rider account? This action cannot be undone.',
            isDanger: true,
            confirmButtonText: '<i class="fas fa-trash-alt me-1"></i>Yes, Delete Rider'
        });

        if (confirmed) {
            fetch(`/admin/riders/${riderId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessMessage('Rider account deleted successfully!');
                    setTimeout(() => {
                        document.querySelector(`[data-rider-id="${riderId}"]`).closest('.col-lg-4')?.remove();
                    }, 500);
                } else {
                    alert('Error deleting rider: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to delete rider');
            });
        }
    }
</script>
@endsection
