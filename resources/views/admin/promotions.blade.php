@extends('layouts.admin')

@section('title', 'Promotions')
@section('nav-promotions', 'active')
@section('page-title', 'Promotions')

@section('admin-styles')
<style>
    .promo-card {
        background:#fff; border-radius:16px; box-shadow:0 4px 15px rgba(0,0,0,.06);
        overflow:hidden; transition:transform .3s;
    }
    .promo-card:hover { transform:translateY(-4px); }
    .promo-header {
        padding:20px 24px; color:#fff; position:relative;
    }
    .promo-header.active-promo { background:linear-gradient(135deg,var(--gasgo-orange),#ff6b35); }
    .promo-header.expired-promo { background:linear-gradient(135deg,#999,#bbb); }
    .promo-body { padding:20px 24px; }
    .promo-stat { font-size:.82rem; color:#888; }

    @media (max-width: 768px) {
        .promo-card {
            border-radius: 14px;
        }
        .promo-header {
            padding: 14px 16px;
        }
        .promo-body {
            padding: 14px 16px;
        }
    }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <span class="badge" style="background:var(--gasgo-orange);font-size:.8rem;">2 Active</span>
        <span class="badge bg-primary ms-1" style="font-size:.8rem;">1 Upcoming</span>
        <span class="badge bg-secondary ms-1" style="font-size:.8rem;">1 Expired</span>
    </div>
    <button class="btn" style="background:var(--gasgo-orange);color:#fff;border-radius:12px;font-weight:600;padding:10px 22px;" data-bs-toggle="modal" data-bs-target="#promoModal">
        <i class="fas fa-plus me-2"></i>Create Promotion
    </button>
</div>

<div class="row g-4">
    <!-- Active Promo 1 -->
    <div class="col-lg-4 col-md-6">
        <div class="promo-card">
            <div class="promo-header active-promo">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="fw-bold mb-1">Summer LPG Sale</h5>
                        <span style="font-size:.82rem;opacity:.9;">10% OFF on all LPG Tanks</span>
                    </div>
                    <span class="badge bg-light text-dark" style="font-size:.7rem;">Active</span>
                </div>
            </div>
            <div class="promo-body">
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="promo-stat">Discount</div>
                        <div class="fw-bold" style="color:var(--gasgo-orange);">10% OFF</div>
                    </div>
                    <div class="col-6">
                        <div class="promo-stat">Type</div>
                        <div class="fw-bold">Percentage</div>
                    </div>
                    <div class="col-6">
                        <div class="promo-stat">Start Date</div>
                        <div class="fw-bold">Jun 1, 2025</div>
                    </div>
                    <div class="col-6">
                        <div class="promo-stat">End Date</div>
                        <div class="fw-bold">Jun 30, 2025</div>
                    </div>
                </div>
                <div class="promo-stat mb-2">Applied to: <strong>LPG 11kg, LPG 22kg, LPG 2kg</strong></div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm flex-grow-1" style="background:var(--gasgo-blue);color:#fff;border-radius:8px;font-weight:600;"><i class="fas fa-edit me-1"></i>Edit</button>
                    <button class="btn btn-sm" style="background:#f8d7da;color:#dc3545;border-radius:8px;"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        </div>
    </div>
    <!-- Active Promo 2 -->
    <div class="col-lg-4 col-md-6">
        <div class="promo-card">
            <div class="promo-header active-promo">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="fw-bold mb-1">Free Delivery Week</h5>
                        <span style="font-size:.82rem;opacity:.9;">Free delivery on orders ₱1,000+</span>
                    </div>
                    <span class="badge bg-light text-dark" style="font-size:.7rem;">Active</span>
                </div>
            </div>
            <div class="promo-body">
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="promo-stat">Discount</div>
                        <div class="fw-bold" style="color:var(--gasgo-orange);">₱50 OFF</div>
                    </div>
                    <div class="col-6">
                        <div class="promo-stat">Type</div>
                        <div class="fw-bold">Fixed Amount</div>
                    </div>
                    <div class="col-6">
                        <div class="promo-stat">Start Date</div>
                        <div class="fw-bold">Jun 10, 2025</div>
                    </div>
                    <div class="col-6">
                        <div class="promo-stat">End Date</div>
                        <div class="fw-bold">Jun 17, 2025</div>
                    </div>
                </div>
                <div class="promo-stat mb-2">Min. order: <strong>₱1,000</strong></div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm flex-grow-1" style="background:var(--gasgo-blue);color:#fff;border-radius:8px;font-weight:600;"><i class="fas fa-edit me-1"></i>Edit</button>
                    <button class="btn btn-sm" style="background:#f8d7da;color:#dc3545;border-radius:8px;"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        </div>
    </div>
    <!-- Upcoming -->
    <div class="col-lg-4 col-md-6">
        <div class="promo-card">
            <div class="promo-header upcoming-promo">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="fw-bold mb-1">Independence Day Sale</h5>
                        <span style="font-size:.82rem;opacity:.9;">15% OFF sitewide</span>
                    </div>
                    <span class="badge bg-light text-primary" style="font-size:.7rem;">Upcoming</span>
                </div>
            </div>
            <div class="promo-body">
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="promo-stat">Discount</div>
                        <div class="fw-bold" style="color:var(--gasgo-blue);">15% OFF</div>
                    </div>
                    <div class="col-6">
                        <div class="promo-stat">Type</div>
                        <div class="fw-bold">Percentage</div>
                    </div>
                    <div class="col-6">
                        <div class="promo-stat">Start Date</div>
                        <div class="fw-bold">Jun 12, 2025</div>
                    </div>
                    <div class="col-6">
                        <div class="promo-stat">End Date</div>
                        <div class="fw-bold">Jun 13, 2025</div>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm flex-grow-1" style="background:var(--gasgo-blue);color:#fff;border-radius:8px;font-weight:600;"><i class="fas fa-edit me-1"></i>Edit</button>
                    <button class="btn btn-sm" style="background:#f8d7da;color:#dc3545;border-radius:8px;"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Promotion Modal -->
<div class="modal fade" id="promoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header" style="border-bottom:none;padding:24px 24px 0;">
                <h5 class="modal-title fw-bold" style="color:var(--gasgo-blue);">Create Promotion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:24px;">
                <form>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="font-size:.88rem;">Promotion Name</label>
                            <input type="text" class="form-control" style="border-radius:10px;" placeholder="e.g. Summer Sale">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="font-size:.88rem;">Discount Type</label>
                            <select class="form-select" style="border-radius:10px;">
                                <option>Percentage (%)</option>
                                <option>Fixed Amount (₱)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold" style="font-size:.88rem;">Discount Value</label>
                            <input type="number" class="form-control" style="border-radius:10px;" placeholder="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold" style="font-size:.88rem;">Start Date</label>
                            <input type="date" class="form-control" style="border-radius:10px;">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold" style="font-size:.88rem;">End Date</label>
                            <input type="date" class="form-control" style="border-radius:10px;">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold" style="font-size:.88rem;">Description</label>
                            <textarea class="form-control" rows="2" style="border-radius:10px;" placeholder="Promotion description..."></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="promoActive" checked>
                                <label class="form-check-label" for="promoActive">Active</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="border-top:none;padding:0 24px 24px;">
                <button class="btn" data-bs-dismiss="modal" style="border-radius:10px;">Cancel</button>
                <button class="btn" style="background:var(--gasgo-orange);color:#fff;border-radius:10px;font-weight:600;padding:10px 28px;">Save Promotion</button>
            </div>
        </div>
    </div>
</div>
@endsection
