@extends('layouts.rider')

@section('title', 'GasGo Rider - Active Delivery')
@section('nav-delivery', 'active')

@section('rider-styles')
<style>
    .delivery-map {
        width:100%; height:220px; border-radius:16px; overflow:hidden;
        background:linear-gradient(135deg,var(--gasgo-blue-light),#d5e8f7);
        display:flex; align-items:center; justify-content:center; margin-bottom:16px;
    }
    .delivery-map i { font-size:2.5rem; color:var(--gasgo-blue); opacity:.5; }
    .step-timeline { position:relative; padding-left:30px; }
    .step-timeline::before {
        content:''; position:absolute; left:11px; top:8px; bottom:8px;
        width:3px; background:#e0e0e0;
    }
    .step-item { position:relative; margin-bottom:18px; }
    .step-item .dot {
        width:24px; height:24px; border-radius:50%; position:absolute;
        left:-30px; top:0; display:flex; align-items:center; justify-content:center;
        font-size:.65rem; color:#fff; z-index:2;
    }
    .step-item .dot.done { background:var(--gasgo-orange); }
    .step-item .dot.current { background:var(--gasgo-blue); animation:pulse 1.5s infinite; }
    .step-item .dot.pending { background:#e0e0e0; }
    @keyframes pulse { 0%,100%{box-shadow:0 0 0 0 rgba(26,109,176,.4);} 50%{box-shadow:0 0 0 8px rgba(26,109,176,0);} }
    .step-item .step-label { font-weight:600; font-size:.88rem; color:#333; }
    .step-item .step-time { font-size:.75rem; color:#888; }
    .action-btn {
        width:100%; padding:14px; border:none; border-radius:14px;
        font-size:1rem; font-weight:700; color:#fff; cursor:pointer;
        transition:transform .2s;
    }
    .action-btn:hover { transform:scale(1.02); }
</style>
@endsection

@section('content')
<!-- Map -->
<div class="delivery-map">
    <div class="text-center">
        <i class="fas fa-map-marked-alt d-block"></i>
        <span style="font-size:.82rem;color:var(--gasgo-blue);">Live navigation map</span>
    </div>
</div>

<!-- Order Info -->
<div class="r-card">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h6 class="mb-0">#GG-00009</h6>
            <small class="text-muted">Assigned 15 mins ago</small>
        </div>
        <span class="badge-status badge-out_for_delivery">Out for Delivery</span>
    </div>

    <!-- Customer Info -->
    <div class="d-flex align-items-center gap-3 p-3 mb-3" style="background:#f8f9fa;border-radius:12px;">
        <div style="width:44px;height:44px;border-radius:50%;background:var(--gasgo-blue);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;">JC</div>
        <div class="flex-grow-1">
            <div class="fw-bold">Juan Cruz</div>
            <small class="text-muted">09191234567</small>
        </div>
        <a href="tel:09191234567" class="btn btn-sm" style="background:var(--gasgo-blue);color:#fff;border-radius:10px;"><i class="fas fa-phone"></i></a>
    </div>

    <!-- Delivery Address -->
    <div class="mb-3" style="font-size:.88rem;">
        <div class="fw-bold mb-1" style="color:var(--gasgo-blue);"><i class="fas fa-map-marker-alt me-1" style="color:var(--gasgo-orange);"></i>Delivery Address</div>
        <div>123 Rizal St, Brgy San Jose, Tanauan City</div>
        <button class="btn btn-sm mt-2" style="background:var(--gasgo-blue-light);color:var(--gasgo-blue);border-radius:8px;font-weight:600;font-size:.8rem;">
            <i class="fas fa-directions me-1"></i>Open in Google Maps
        </button>
    </div>

    <!-- Items -->
    <div class="mb-3" style="font-size:.88rem;">
        <div class="fw-bold mb-1" style="color:var(--gasgo-blue);"><i class="fas fa-box me-1" style="color:var(--gasgo-orange);"></i>Order Items</div>
        <div class="d-flex justify-content-between p-2" style="background:#f8f9fa;border-radius:8px;">
            <span>LPG 22kg &times;1</span>
            <span class="fw-bold">₱1,600</span>
        </div>
        <div class="d-flex justify-content-between p-2 mt-1" style="background:#f8f9fa;border-radius:8px;">
            <span>Delivery Fee</span>
            <span class="fw-bold">₱50</span>
        </div>
        <div class="d-flex justify-content-between p-2 mt-2" style="border-top:2px solid var(--gasgo-blue);">
            <span class="fw-bold" style="color:var(--gasgo-blue);">Total</span>
            <span class="fw-bold" style="color:var(--gasgo-orange);font-size:1.1rem;">₱1,650</span>
        </div>
    </div>

    <!-- Payment -->
    <div class="p-2 mb-3" style="background:var(--gasgo-orange-light);border-radius:8px;font-size:.88rem;">
        <i class="fas fa-money-bill me-1" style="color:var(--gasgo-orange);"></i>
        <strong>Cash on Delivery</strong> — Collect ₱1,650
    </div>
</div>

<!-- Delivery Progress -->
<div class="r-card">
    <h6><i class="fas fa-route me-2" style="color:var(--gasgo-orange);"></i>Delivery Progress</h6>
    <div class="step-timeline mt-3">
        <div class="step-item">
            <div class="dot done"><i class="fas fa-check"></i></div>
            <div class="step-label">Order Picked Up</div>
            <div class="step-time">10:45 AM</div>
        </div>
        <div class="step-item">
            <div class="dot done"><i class="fas fa-check"></i></div>
            <div class="step-label">On the Way</div>
            <div class="step-time">10:50 AM</div>
        </div>
        <div class="step-item">
            <div class="dot current"><i class="fas fa-motorcycle" style="font-size:.55rem;"></i></div>
            <div class="step-label">Arriving Soon</div>
            <div class="step-time">Est. 5 mins</div>
        </div>
        <div class="step-item">
            <div class="dot pending"></div>
            <div class="step-label" style="color:#aaa;">Delivered</div>
            <div class="step-time">—</div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="d-flex flex-column gap-2 mt-3">
    <button class="action-btn" style="background:linear-gradient(135deg,#27ae60,#2ecc71);" onclick="markDelivered()">
        <i class="fas fa-check-circle me-2"></i>Mark as Delivered
    </button>
    <button class="action-btn" style="background:linear-gradient(135deg,var(--gasgo-blue),#2196f3);" data-bs-toggle="modal" data-bs-target="#proofModal">
        <i class="fas fa-camera me-2"></i>Upload Proof of Delivery
    </button>
</div>

<!-- Proof of Delivery Modal -->
<div class="modal fade" id="proofModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header" style="border-bottom:none;">
                <h6 class="modal-title fw-bold" style="color:var(--gasgo-blue);">Proof of Delivery</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div class="mb-3">
                    <label class="form-label fw-bold" style="font-size:.88rem;">Take a photo</label>
                    <input type="file" class="form-control" accept="image/*" capture="environment" style="border-radius:10px;">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold" style="font-size:.88rem;">Customer Signature (optional)</label>
                    <div style="width:100%;height:120px;border:2px dashed #ccc;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#aaa;">
                        <span>Tap to sign</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top:none;">
                <button class="btn" data-bs-dismiss="modal" style="border-radius:10px;">Cancel</button>
                <button class="btn" style="background:var(--gasgo-orange);color:#fff;border-radius:10px;font-weight:600;">Submit Proof</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function markDelivered() {
        if (confirm('Mark this order as delivered?')) {
            alert('Order #GG-00009 marked as delivered!');
        }
    }
</script>
@endsection
