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
<div class="row g-4 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="loyalty-stat">
            <i class="fas fa-id-card" style="font-size:1.5rem;color:var(--gasgo-blue);"></i>
            <h3 style="color:var(--gasgo-blue);">{{ $loyaltyMembers }}</h3>
            <p>Total Loyalty Members</p>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="loyalty-stat">
            <i class="fas fa-stamp" style="font-size:1.5rem;color:var(--gasgo-orange);"></i>
            <h3 style="color:var(--gasgo-orange);">{{ number_format($totalPointsEarned) }}</h3>
            <p>Total Points Issued</p>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="loyalty-stat">
            <i class="fas fa-gift" style="font-size:1.5rem;color:#27ae60;"></i>
            <h3 style="color:#27ae60;">{{ number_format($totalPointsRedeemed) }}</h3>
            <p>Points Redeemed</p>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="loyalty-stat">
            <i class="fas fa-coins" style="font-size:1.5rem;color:#e74c3c;"></i>
            <h3 style="color:#e74c3c;">{{ number_format($activePoints) }}</h3>
            <p>Active Points</p>
        </div>
    </div>
</div>

<!-- Rewards List -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="fw-bold" style="color:var(--gasgo-blue);"><i class="fas fa-gift me-2" style="color:var(--gasgo-orange);"></i>Available Rewards</h6>
    <button class="btn" style="background:var(--gasgo-orange);color:#fff;border-radius:12px;font-weight:600;padding:10px 22px;" data-bs-toggle="modal" data-bs-target="#rewardModal">
        <i class="fas fa-plus me-2"></i>Add Reward
    </button>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-4 col-md-6">
        <div class="reward-card">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="reward-icon" style="background:linear-gradient(135deg,#27ae60,#2ecc71);"><i class="fas fa-truck"></i></div>
                <div>
                    <h6 class="fw-bold mb-0">Free Delivery</h6>
                    <small class="text-muted">Waive ₱50 delivery fee</small>
                </div>
            </div>
            <div class="d-flex justify-content-between mb-2" style="font-size:.85rem;">
                <span class="text-muted">Points Required:</span>
                <span class="fw-bold" style="color:var(--gasgo-orange);">50 pts</span>
            </div>
            <div class="d-flex justify-content-between mb-3" style="font-size:.85rem;">
                <span class="text-muted">Times Claimed:</span>
                <span class="fw-bold">12</span>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-sm flex-grow-1" style="background:var(--gasgo-blue);color:#fff;border-radius:8px;font-weight:600;">Edit</button>
                <button class="btn btn-sm" style="background:#f8d7da;color:#dc3545;border-radius:8px;"><i class="fas fa-trash"></i></button>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="reward-card">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="reward-icon" style="background:linear-gradient(135deg,var(--gasgo-orange),#ff6b35);"><i class="fas fa-percent"></i></div>
                <div>
                    <h6 class="fw-bold mb-0">₱100 Discount</h6>
                    <small class="text-muted">₱100 off your next order</small>
                </div>
            </div>
            <div class="d-flex justify-content-between mb-2" style="font-size:.85rem;">
                <span class="text-muted">Points Required:</span>
                <span class="fw-bold" style="color:var(--gasgo-orange);">100 pts</span>
            </div>
            <div class="d-flex justify-content-between mb-3" style="font-size:.85rem;">
                <span class="text-muted">Times Claimed:</span>
                <span class="fw-bold">18</span>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-sm flex-grow-1" style="background:var(--gasgo-blue);color:#fff;border-radius:8px;font-weight:600;">Edit</button>
                <button class="btn btn-sm" style="background:#f8d7da;color:#dc3545;border-radius:8px;"><i class="fas fa-trash"></i></button>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="reward-card">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="reward-icon" style="background:linear-gradient(135deg,var(--gasgo-blue),#2196f3);"><i class="fas fa-fire"></i></div>
                <div>
                    <h6 class="fw-bold mb-0">Free LPG 2kg</h6>
                    <small class="text-muted">Redeem a free 2kg LPG tank</small>
                </div>
            </div>
            <div class="d-flex justify-content-between mb-2" style="font-size:.85rem;">
                <span class="text-muted">Points Required:</span>
                <span class="fw-bold" style="color:var(--gasgo-orange);">200 pts</span>
            </div>
            <div class="d-flex justify-content-between mb-3" style="font-size:.85rem;">
                <span class="text-muted">Times Claimed:</span>
                <span class="fw-bold">4</span>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-sm flex-grow-1" style="background:var(--gasgo-blue);color:#fff;border-radius:8px;font-weight:600;">Edit</button>
                <button class="btn btn-sm" style="background:#f8d7da;color:#dc3545;border-radius:8px;"><i class="fas fa-trash"></i></button>
            </div>
        </div>
    </div>
</div>

<!-- Recent Claims -->
<div class="gasgo-table">
    <div class="px-3 pt-3 pb-2">
        <h6 class="fw-bold mb-0" style="color:var(--gasgo-blue);"><i class="fas fa-history me-2" style="color:var(--gasgo-orange);"></i>Recent Loyalty Transactions</h6>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>Customer</th>
                <th>Transaction Type</th>
                <th>Points</th>
                <th>Description</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $transaction)
                <tr>
                    <td class="fw-bold">{{ $transaction->user->name ?? 'Unknown' }}</td>
                    <td>
                        @if($transaction->type === 'earned')
                            <span class="badge bg-success" style="font-size:.75rem;">Earned</span>
                        @else
                            <span class="badge bg-warning text-dark" style="font-size:.75rem;">Redeemed</span>
                        @endif
                    </td>
                    <td>{{ $transaction->points }} pts</td>
                    <td style="font-size:.85rem;">{{ $transaction->description ?? 'N/A' }}</td>
                    <td style="font-size:.85rem;">{{ $transaction->created_at->format('M d, Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">No loyalty transactions yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Add Reward Modal -->
<div class="modal fade" id="rewardModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header" style="border-bottom:none;padding:24px 24px 0;">
                <h5 class="modal-title fw-bold" style="color:var(--gasgo-blue);">Add Reward</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:24px;">
                <form>
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size:.88rem;">Reward Name</label>
                        <input type="text" class="form-control" style="border-radius:10px;" placeholder="e.g. Free Delivery">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size:.88rem;">Description</label>
                        <textarea class="form-control" rows="2" style="border-radius:10px;" placeholder="Reward description..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size:.88rem;">Points Required</label>
                        <input type="number" class="form-control" style="border-radius:10px;" placeholder="0">
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="rewardActive" checked>
                        <label class="form-check-label" for="rewardActive">Active</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="border-top:none;padding:0 24px 24px;">
                <button class="btn" data-bs-dismiss="modal" style="border-radius:10px;">Cancel</button>
                <button class="btn" style="background:var(--gasgo-orange);color:#fff;border-radius:10px;font-weight:600;padding:10px 28px;">Save Reward</button>
            </div>
        </div>
    </div>
</div>
@endsection
