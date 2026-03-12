@extends('layouts.admin')

@section('title', 'GasGo Admin - Categories')
@section('nav-categories', 'active')
@section('page-title', 'Category Management')

@section('admin-styles')
<style>
    .cat-card {
        background:#fff; border-radius:16px; padding:24px;
        box-shadow:0 4px 15px rgba(0,0,0,.06); transition:transform .3s;
        display:flex; align-items:center; gap:18px;
    }
    .cat-card:hover { transform:translateY(-4px); }
    .cat-icon {
        width:56px; height:56px; border-radius:14px; display:flex;
        align-items:center; justify-content:center; font-size:1.5rem; color:#fff; flex-shrink:0;
    }
    .modal-form label { font-weight:600; font-size:.88rem; color:#555; }
    .modal-form .form-control {
        border-radius:10px; border:2px solid #e0e0e0; padding:10px 16px;
    }
    .modal-form .form-control:focus { border-color:var(--gasgo-blue); box-shadow:none; }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <p class="text-muted mb-0" style="font-size:.9rem;">Organize your products into categories.</p>
    <button class="btn" style="background:var(--gasgo-orange);color:#fff;border-radius:12px;font-weight:600;padding:10px 22px;" data-bs-toggle="modal" data-bs-target="#categoryModal">
        <i class="fas fa-plus me-2"></i>Add Category
    </button>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="cat-card">
            <div class="cat-icon" style="background:linear-gradient(135deg,#1a6db0,#2196f3);"><i class="fas fa-fire"></i></div>
            <div class="flex-grow-1">
                <h6 class="fw-bold mb-1" style="color:var(--gasgo-blue);">LPG Tanks</h6>
                <p class="text-muted mb-0" style="font-size:.82rem;">Main product line — 11kg, 22kg, 2kg tanks</p>
                <small class="text-muted"><strong>3</strong> products</small>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-sm" style="background:var(--gasgo-blue-light);color:var(--gasgo-blue);border-radius:8px;" data-bs-toggle="modal" data-bs-target="#categoryModal"><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm" style="background:#f8d7da;color:#dc3545;border-radius:8px;"><i class="fas fa-trash"></i></button>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="cat-card">
            <div class="cat-icon" style="background:linear-gradient(135deg,#f7941d,#ff6b35);"><i class="fas fa-tools"></i></div>
            <div class="flex-grow-1">
                <h6 class="fw-bold mb-1" style="color:var(--gasgo-blue);">Accessories</h6>
                <p class="text-muted mb-0" style="font-size:.82rem;">Regulators, hoses, clamps, and other accessories</p>
                <small class="text-muted"><strong>2</strong> products</small>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-sm" style="background:var(--gasgo-blue-light);color:var(--gasgo-blue);border-radius:8px;" data-bs-toggle="modal" data-bs-target="#categoryModal"><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm" style="background:#f8d7da;color:#dc3545;border-radius:8px;"><i class="fas fa-trash"></i></button>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="cat-card">
            <div class="cat-icon" style="background:linear-gradient(135deg,#27ae60,#2ecc71);"><i class="fas fa-shield-alt"></i></div>
            <div class="flex-grow-1">
                <h6 class="fw-bold mb-1" style="color:var(--gasgo-blue);">Safety Equipment</h6>
                <p class="text-muted mb-0" style="font-size:.82rem;">Fire extinguishers, gas detectors, safety valves</p>
                <small class="text-muted"><strong>0</strong> products</small>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-sm" style="background:var(--gasgo-blue-light);color:var(--gasgo-blue);border-radius:8px;" data-bs-toggle="modal" data-bs-target="#categoryModal"><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm" style="background:#f8d7da;color:#dc3545;border-radius:8px;"><i class="fas fa-trash"></i></button>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header" style="border-bottom:none;padding:24px 24px 0;">
                <h5 class="modal-title fw-bold" style="color:var(--gasgo-blue);">Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body modal-form" style="padding:24px;">
                <form>
                    <div class="mb-3">
                        <label class="mb-1">Category Name</label>
                        <input type="text" class="form-control" placeholder="e.g. LPG Tanks">
                    </div>
                    <div class="mb-3">
                        <label class="mb-1">Description</label>
                        <textarea class="form-control" rows="3" placeholder="Brief description..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="mb-1">Image (optional)</label>
                        <input type="file" class="form-control" accept="image/*">
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="catActive" checked>
                        <label class="form-check-label" for="catActive">Active</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="border-top:none;padding:0 24px 24px;">
                <button class="btn" data-bs-dismiss="modal" style="border-radius:10px;">Cancel</button>
                <button class="btn" style="background:var(--gasgo-orange);color:#fff;border-radius:10px;font-weight:600;padding:10px 28px;">Save Category</button>
            </div>
        </div>
    </div>
</div>
@endsection
