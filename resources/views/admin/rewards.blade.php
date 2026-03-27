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

@php
    // Calculate loyalty statistics from transactions
    $loyaltyMembers = count($transactions->pluck('user_id')->unique());
    $totalPointsEarned = $transactions->where('type', 'earned')->sum('points');
    $totalPointsRedeemed = $transactions->where('type', 'redeemed')->sum('points');
    $activePoints = $totalPointsEarned - $totalPointsRedeemed;
@endphp

@section('content')
<!-- Loyalty Stats -->


<!-- Rewards List -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="fw-bold" style="color:var(--gasgo-blue);"><i class="fas fa-gift me-2" style="color:var(--gasgo-orange);"></i>Available Rewards</h6>
    <button class="btn" style="background:var(--gasgo-orange);color:#fff;border-radius:12px;font-weight:600;padding:10px 22px;" data-bs-toggle="modal" data-bs-target="#rewardModal" onclick="openAddReward()">
        <i class="fas fa-plus me-2"></i>Add Reward
    </button>
</div>

<div class="row g-4 mb-4">
    @forelse($rewards as $reward)
        <div class="col-lg-4 col-md-6">
            <div class="reward-card">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="reward-icon" style="background:linear-gradient(135deg,var(--gasgo-blue),#2196f3);"><i class="fas fa-gift"></i></div>
                    <div>
                        <h6 class="fw-bold mb-0">{{ $reward->name }}</h6>
                        <small class="text-muted">{{ $reward->description ?: 'No description' }}</small>
                    </div>
                </div>
                <div class="d-flex justify-content-between mb-2" style="font-size:.85rem;">
                    <span class="text-muted">Points Required:</span>
                    <span class="fw-bold" style="color:var(--gasgo-orange);">{{ $reward->reward_points_required }} pts</span>
                </div>
                <div class="d-flex justify-content-between mb-3" style="font-size:.85rem;">
                    <span class="text-muted">Stock:</span>
                    <span class="fw-bold">{{ $reward->stock }}</span>
                </div>
                <div class="d-flex gap-2">
                    <button
                        class="btn btn-sm flex-grow-1"
                        style="background:var(--gasgo-blue);color:#fff;border-radius:8px;font-weight:600;"
                        data-bs-toggle="modal"
                        data-bs-target="#rewardModal"
                        onclick="openEditReward(this)"
                        data-update-url="{{ route('admin.rewards.update', $reward) }}"
                        data-name="{{ $reward->name }}"
                        data-description="{{ $reward->description }}"
                        data-points="{{ $reward->reward_points_required }}"
                        data-stock="{{ $reward->stock }}"
                        data-category="{{ $reward->category }}"
                        data-is-active="{{ $reward->is_active ? '1' : '0' }}"
                    >Edit</button>
                    <form action="{{ route('admin.rewards.destroy', $reward) }}" method="POST" onsubmit="return confirm('Delete this reward?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm" type="submit" style="background:#f8d7da;color:#dc3545;border-radius:8px;"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="text-center text-muted py-4">No rewards configured yet.</div>
        </div>
    @endforelse
</div>

<!-- Recent Claims -->


<!-- Add Reward Modal -->
<div class="modal fade" id="rewardModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header" style="border-bottom:none;padding:24px 24px 0;">
                <h5 class="modal-title fw-bold" style="color:var(--gasgo-blue);" id="rewardModalTitle">Add Reward</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:24px;">
                <form id="rewardForm" method="POST" action="{{ route('admin.rewards.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_method" id="rewardFormMethod" value="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size:.88rem;">Reward Name</label>
                        <input type="text" class="form-control" name="name" id="rewardName" style="border-radius:10px;" placeholder="e.g. Free Delivery" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size:.88rem;">Description</label>
                        <textarea class="form-control" name="description" id="rewardDescription" rows="2" style="border-radius:10px;" placeholder="Reward description..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size:.88rem;">Points Required</label>
                        <input type="number" class="form-control" name="reward_points_required" id="rewardPoints" style="border-radius:10px;" placeholder="0" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size:.88rem;">Stock</label>
                        <input type="number" class="form-control" name="stock" id="rewardStock" style="border-radius:10px;" placeholder="0" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size:.88rem;">Category</label>
                        <input type="text" class="form-control" name="category" id="rewardCategory" style="border-radius:10px;" placeholder="Rewards">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size:.88rem;">Reward Image</label>
                        <input type="file" class="form-control" name="image" accept="image/*" style="border-radius:10px;">
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="rewardActive" name="is_active" value="1" checked>
                        <label class="form-check-label" for="rewardActive">Active</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="border-top:none;padding:0 24px 24px;">
                <button class="btn" data-bs-dismiss="modal" style="border-radius:10px;">Cancel</button>
                <button class="btn" type="submit" form="rewardForm" style="background:var(--gasgo-orange);color:#fff;border-radius:10px;font-weight:600;padding:10px 28px;">Save Reward</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function openAddReward() {
    document.getElementById('rewardModalTitle').textContent = 'Add Reward';
    document.getElementById('rewardForm').action = "{{ route('admin.rewards.store') }}";
    document.getElementById('rewardFormMethod').value = 'POST';
    document.getElementById('rewardName').value = '';
    document.getElementById('rewardDescription').value = '';
    document.getElementById('rewardPoints').value = '';
    document.getElementById('rewardStock').value = '';
    document.getElementById('rewardCategory').value = 'Rewards';
    document.getElementById('rewardActive').checked = true;
}

function openEditReward(button) {
    document.getElementById('rewardModalTitle').textContent = 'Edit Reward';
    document.getElementById('rewardForm').action = button.dataset.updateUrl;
    document.getElementById('rewardFormMethod').value = 'PUT';
    document.getElementById('rewardName').value = button.dataset.name || '';
    document.getElementById('rewardDescription').value = button.dataset.description || '';
    document.getElementById('rewardPoints').value = button.dataset.points || '0';
    document.getElementById('rewardStock').value = button.dataset.stock || '0';
    document.getElementById('rewardCategory').value = button.dataset.category || 'Rewards';
    document.getElementById('rewardActive').checked = (button.dataset.isActive === '1');
}
</script>
@endsection
