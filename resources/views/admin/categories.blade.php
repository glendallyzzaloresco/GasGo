@extends('layouts.admin')

@section('title', 'Category Management')
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
    .modal-form .form-control:focus { border-color:var(--gasgo-blue); box-shadow:none; }

    @media (max-width: 768px) {
        .cat-card {
            padding: 14px 12px;
            gap: 12px;
            border-radius: 14px;
        }
        .cat-icon {
            width: 44px;
            height: 44px;
            font-size: 1.2rem;
            border-radius: 10px;
        }
    }
</style>
@endsection

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm" style="border-radius:12px;">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <p class="text-muted mb-0" style="font-size:.9rem;">Organize your products into custom categories for any business niche.</p>
    <button class="btn" style="background:var(--gasgo-orange);color:#fff;border-radius:12px;font-weight:600;padding:10px 22px;" data-bs-toggle="modal" data-bs-target="#categoryModal" onclick="openAddCategoryModal()">
        <i class="fas fa-plus me-2"></i>Add Category
    </button>
</div>

<div class="row g-4">
    @forelse($categories as $category)
        <div class="col-lg-6">
            <div class="cat-card">
                <div class="cat-icon" style="background: {{ $category->color_code ?? '#1a6db0' }};">
                    <i class="{{ $category->icon_class ?? 'fas fa-tag' }}"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="fw-bold mb-1" style="color:var(--gasgo-blue);">{{ $category->name }}</h6>
                    <p class="text-muted mb-0" style="font-size:.82rem;">{{ $category->description ?? 'No description provided.' }}</p>
                    <small class="text-muted"><strong>{{ $category->products_count ?? 0 }}</strong> products assigned</small>
                </div>
                <div class="d-flex gap-2">
                    <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" data-confirm="Are you sure you want to delete this category?">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm" style="background:#f8d7da;color:#dc3545;border-radius:8px;" title="Delete Category">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center text-muted py-5">
            <i class="fas fa-folder-open fa-3x mb-3 text-secondary"></i>
            <p>No categories found. Click <strong>Add Category</strong> above to create your first category.</p>
        </div>
    @endforelse
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header" style="border-bottom:none;padding:24px 24px 0;">
                <h5 class="modal-title fw-bold" style="color:var(--gasgo-blue);">Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.categories.store') }}" method="POST">
                @csrf
                <div class="modal-body modal-form" style="padding:24px;">
                    <div class="mb-3">
                        <label class="mb-1">Category Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. 5-Gallon Water, Accessories..." required>
                    </div>
                    <div class="mb-3">
                        <label class="mb-1">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Brief description of products in this category..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="mb-1">Icon Class (FontAwesome)</label>
                        <input type="text" name="icon_class" class="form-control" value="fas fa-box" placeholder="e.g. fas fa-tint, fas fa-fire, fas fa-shopping-basket">
                    </div>
                    <div class="mb-3">
                        <label class="mb-1">Badge Color</label>
                        <input type="color" name="color_code" class="form-control form-control-color w-100" value="#1a6db0" style="height:45px;">
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="catActive" value="1" checked>
                        <label class="form-check-label" for="catActive">Active Category</label>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:none;padding:0 24px 24px;">
                    <button type="button" class="btn" data-bs-dismiss="modal" style="border-radius:10px;">Cancel</button>
                    <button type="submit" class="btn" style="background:var(--gasgo-orange);color:#fff;border-radius:10px;font-weight:600;padding:10px 28px;">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
