@extends('layouts.admin')

@section('title', 'GasGo Admin - Rewards')
@section('nav-rewards', 'active')
@section('page-title', 'Loyalty & Rewards')

@section('admin-styles')
<style>
    .reward-card {
        background:#fff; border-radius:16px; box-shadow:0 4px 15px rgba(0,0,0,.06);
        padding:24px; transition:transform .3s;
    }
    .reward-card:hover { transform:translateY(-4px); }
    .reward-icon {
        width:52px; height:52px; border-radius:14px; display:flex; align-items:center;
        justify-content:center; font-size:1.4rem; color:#fff;
    }
    .loyalty-stat {
        background:#fff; border-radius:16px; padding:22px; box-shadow:0 4px 15px rgba(0,0,0,.06); text-align:center;
    }
    .loyalty-stat h3 { font-size:1.8rem; font-weight:800; margin:8px 0 4px; }
    .loyalty-stat p { font-size:.82rem; color:#888; margin:0; }
</style>
@endsection

@section('content')
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius:12px;">
        <i class="fas fa-exclamation-circle me-2"></i><strong>Validation Errors:</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius:12px;">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Loyalty Stats -->
<div class="row g-3 mb-5">
    <div class="col-md-3">
        <div class="loyalty-stat">
            <h3 style="font-size:2.2rem; color:var(--gasgo-orange);">{{ $loyaltyMembers }}</h3>
            <p>Loyalty Members</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="loyalty-stat">
            <h3 style="font-size:2.2rem; color:var(--gasgo-blue);">{{ $totalPointsEarned }}</h3>
            <p>Total Points Earned</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="loyalty-stat">
            <h3 style="font-size:2.2rem; color:#28a745;">{{ $totalPointsRedeemed }}</h3>
            <p>Points Redeemed</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="loyalty-stat">
            <h3 style="font-size:2.2rem; color:#6f42c1;">{{ $activePoints }}</h3>
            <p>Active Points</p>
        </div>
    </div>
</div>

<!-- Rewards List -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="fw-bold" style="color:var(--gasgo-blue);"><i class="fas fa-ticket-alt me-2" style="color:var(--gasgo-orange);"></i>Customer Redeemable Vouchers</h6>
    <button class="btn" style="background:var(--gasgo-orange);color:#fff;border-radius:12px;font-weight:600;padding:10px 22px;" data-bs-toggle="modal" data-bs-target="#voucherModal" onclick="openAddVoucher()">
        <i class="fas fa-plus me-2"></i>Add Voucher
    </button>
</div>

<div class="row g-4 mb-4">
    @forelse($vouchers as $voucher)
        <div class="col-lg-4 col-md-6">
            <div class="reward-card">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="reward-icon" style="background:linear-gradient(135deg,var(--gasgo-orange),#ff9800);"><i class="fas fa-tag"></i></div>
                    <div>
                        <h6 class="fw-bold mb-0">{{ $voucher->name }}</h6>
                        <small class="text-muted">Customer Voucher</small>
                    </div>
                </div>
                <p class="text-muted" style="font-size:0.92rem; margin-bottom: 12px;">{{ $voucher->description ?: 'No description provided.' }}</p>
                <div class="d-flex justify-content-between mb-2" style="font-size:.85rem;">
                    <span class="text-muted">Unlock at:</span>
                    <span class="fw-bold" style="color:var(--gasgo-orange);">{{ $voucher->reward_points_required }} points</span>
                </div>
                <div class="d-flex justify-content-between mb-2" style="font-size:.85rem;">
                    <span class="text-muted">Equivalent spend:</span>
                    <span class="fw-bold">₱{{ number_format($voucher->reward_points_required * 100, 0) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2" style="font-size:.85rem;">
                    <span class="text-muted">Discount:</span>
                    <span class="fw-bold">₱{{ $voucher->discount_amount }}</span>
                </div>
                @if($voucher->expires_at)
                <div class="d-flex justify-content-between mb-2" style="font-size:.85rem;">
                    <span class="text-muted">Expires at:</span>
                    <span class="fw-bold">{{ $voucher->expires_at->format('M d, Y') }}</span>
                </div>
                @endif
                <div class="d-flex justify-content-between mb-3" style="font-size:.85rem;">
                    <span class="text-muted">Status:</span>
                    <span class="badge {{ $voucher->is_active ? 'bg-success' : 'bg-secondary' }}">
                        {{ $voucher->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="d-flex gap-2">
                    <button
                        class="btn btn-sm flex-grow-1"
                        style="background:var(--gasgo-blue);color:#fff;border-radius:8px;font-weight:600;"
                        data-bs-toggle="modal"
                        data-bs-target="#voucherModal"
                        onclick="openEditVoucher(this)"
                        data-update-url="{{ route('admin.vouchers.update', $voucher) }}"
                        data-name="{{ $voucher->name }}"
                        data-description="{{ $voucher->description }}"
                        data-points="{{ $voucher->reward_points_required }}"
                        data-amount="{{ $voucher->discount_amount }}"
                        data-is-active="{{ $voucher->is_active ? '1' : '0' }}"
                        data-expires-at="{{ optional($voucher->expires_at)->format('Y-m-d') }}"
                    >Edit</button>
                    <form action="{{ route('admin.vouchers.destroy', $voucher) }}" method="POST" onsubmit="return confirm('Delete this voucher?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm" type="submit" style="background:#f8d7da;color:#dc3545;border-radius:8px;"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info" style="border-radius:12px;">
                <i class="fas fa-info-circle me-2"></i>No customer vouchers configured yet. Create vouchers that customers can claim on their loyalty dashboard!
            </div>
        </div>
    @endforelse
</div>

<!-- Recent Claims -->


<!-- Add/Edit Voucher Modal -->
<div class="modal fade" id="voucherModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header" style="border-bottom:none;padding:24px 24px 0;">
                <h5 class="modal-title fw-bold" style="color:var(--gasgo-blue);" id="voucherModalTitle">Add Voucher</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:24px;">
                <form id="voucherForm" method="POST" action="{{ route('admin.vouchers.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_method" id="voucherFormMethod" value="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size:.88rem;">Voucher Name</label>
                        <input type="text" class="form-control" name="name" id="voucherName" style="border-radius:10px;" placeholder="e.g. ₱50 OFF Voucher" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size:.88rem;">Description</label>
                        <textarea class="form-control" name="description" id="voucherDescription" rows="2" style="border-radius:10px;" placeholder="e.g. Get ₱50 discount on your next order"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size:.88rem;">Discount Amount (₱)</label>
                        <input type="number" class="form-control" name="discount_amount" id="voucherAmount" style="border-radius:10px;" placeholder="e.g. 50" min="0" step="0.01" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size:.88rem;">Unlock at Points</label>
                        <input type="number" class="form-control" name="reward_points_required" id="voucherPoints" style="border-radius:10px;" placeholder="e.g. 5000" min="0" required>
                        <small class="text-muted">Points are based on delivered order spend: ₱100 spend = 1 point. Customers unlock this voucher when they reach the required point total.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size:.88rem;">Expires At</label>
                        <input type="date" class="form-control" name="expires_at" id="voucherExpiresAt" style="border-radius:10px;" placeholder="Optional expiry date">
                        <small class="text-muted">Optional expiry date for this voucher.</small>
                    </div>
                    
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="voucherActive" name="is_active" value="1" checked>
                        <label class="form-check-label" for="voucherActive">Active - Show on customer dashboard</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="border-top:none;padding:0 24px 24px;">
                <button class="btn" data-bs-dismiss="modal" style="border-radius:10px;">Cancel</button>
                <button id="voucherFormSubmitBtn" class="btn" type="submit" form="voucherForm" style="background:var(--gasgo-orange);color:#fff;border-radius:10px;font-weight:600;padding:10px 28px;">Save Voucher</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function openAddVoucher() {
    document.getElementById('voucherModalTitle').textContent = 'Add Voucher';
    document.getElementById('voucherFormSubmitBtn').textContent = 'Save Voucher';
    document.getElementById('voucherForm').action = "{{ route('admin.vouchers.store') }}";
    document.getElementById('voucherFormMethod').value = 'POST';
    document.getElementById('voucherName').value = '';
    document.getElementById('voucherDescription').value = '';
    document.getElementById('voucherAmount').value = '';
    document.getElementById('voucherPoints').value = '';
    document.getElementById('voucherExpiresAt').value = '';
    document.getElementById('voucherActive').checked = true;
}

function openEditVoucher(button) {
    document.getElementById('voucherModalTitle').textContent = 'Edit Voucher';
    document.getElementById('voucherFormSubmitBtn').textContent = 'Update Voucher';
    document.getElementById('voucherForm').action = button.dataset.updateUrl;
    document.getElementById('voucherFormMethod').value = 'PUT';
    document.getElementById('voucherName').value = button.dataset.name || '';
    document.getElementById('voucherDescription').value = button.dataset.description || '';
    document.getElementById('voucherAmount').value = button.dataset.amount || '0';
    document.getElementById('voucherPoints').value = button.dataset.points || '0';
    document.getElementById('voucherExpiresAt').value = button.dataset.expiresAt || '';
    document.getElementById('voucherActive').checked = (button.dataset.isActive === '1');
}

// Form submit handler
document.getElementById('voucherForm').addEventListener('submit', function(e) {
    const name = document.getElementById('voucherName').value.trim();
    const amount = parseFloat(document.getElementById('voucherAmount').value);
    const points = parseInt(document.getElementById('voucherPoints').value);
    
    if (!name || amount < 0 || points < 0) {
        e.preventDefault();
        alert('Please fill in all required fields correctly');
        return false;
    }
    
    console.log('Submitting voucher form', {
        name: name,
        description: document.getElementById('voucherDescription').value,
        discount_amount: amount,
        reward_points_required: points,
        is_active: document.getElementById('voucherActive').checked,
        action: this.action,
        method: document.getElementById('voucherFormMethod').value
    });
});
</script>
@endsection
